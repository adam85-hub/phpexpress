<?php
namespace PHPExpress\Http;

use Exception;
use TypeError;

require_once __DIR__ . "/request.class.php";
require_once __DIR__ . "/response.class.php";

/**
 * Single route
 */
class Route {
    private string $path;
    private array $path_array;
    private callable $handle;
    private string $method;
    private $middleware;
    private $flags;
    private callable $prefilght_handle;

    /**
     * @param string $path path to route.
     * @param callable $handle function wich get executed when route is requested.
     * @param string $method request method.
     * @param callable $default_handle defautl handle wich get executed when route is requested in preflight mode.
     * @param null $flags Json flags used to send response.
     */
    function __construct(string $path, callable $handle, string $method, callable $default_handle, $flags = null)
    {
        $this->flags = $flags;
        $this->path = $path;
        $this->handle = $handle;
        $this->prefilght_handle = $default_handle;
        $method = \strtoupper($method);
        if($method != 'GET' && $method != 'POST' && $method != 'PUT' && $method != 'DELETE') {
            throw new Exception("Invalid request method type", 400);
        }        
        $this->method = $method;
        //Convert path to array
        $this->path_array = \preg_split("/\//", $this->path);
        \array_shift($this->path_array);
    }        
    /**
     * Checks if requested path matches this route path and if it was requested with proper method.
     * @param string $requestPath requested path.
     * 
     * @return boolean
     */
    public function isRoutePath(string $requestPath) {
        $matched = false;
        // if($requestPath == $this->path) $matched = true;
        
        $requestPathArray = \preg_split("/\//",$requestPath);
        \array_shift($requestPathArray);
        if(count($requestPathArray) == count($this->path_array)) {
            $matched = true;
            for($i = 0; $i < count($requestPathArray); $i++) {
                if(\strpos($this->path_array[$i], ":") != false) {
                    if($requestPathArray[$i] == "") { $matched = false; break; }
                }
                else if (\strpos($this->path_array[$i], "+:") != false){
                    if(\is_numeric($requestPathArray[$i]) == false) {$matched = false; break;}                 
                }
                else if($this->path_array[$i] == "*") {
                    break;
                }
                else if($this->path_array[$i] != $requestPathArray[$i]) {
                   $matched = false;
                   break;
                }
            }
        }

        if($this->path_array[0] == "*") $matched = true; 

        if($matched == true && $this->method == $_SERVER['REQUEST_METHOD']) {
            return true;
        }

        return false;
    }
    /**
     * Adds middleware to this single Route
     * @param mixed $middleware If string it will try to find and apply one of built in middlewares.
     * If it's callable it will call this user defined function.
     * 
     * @return Route
     */
    public function &use($middleware): Route {
        if(is_string($middleware) == false && is_callable($middleware)) {
            throw new TypeError('$middleware can be only string or callable', 500);
        }
        else {
            $this->middleware = $middleware;
        }

        return $this;
    }
    /**
     * Overrides global response flags for this route.
     * @param mixed $flags Json flags used to send the response.
     * 
     * @return Route
     */
    public function &setResponseFlags($flags): Route {
        $this->flags = $flags;
        return $this;
    }
    /**
     * Adds custom handle to handle preflight requests.
     * @param callable $handle Function that handles preflight requests.
     * 
     * @return Route
     */
    public function &setPreflightHandle(callable $handle) {
        $this->prefilght_handle = $handle;
        return $this;
    }
    /**
     * Executes the callback function with request and response arguments.
     * @return void
     */
    public function invoke(string $requestPath) {
        //Get route array
        $requestPathArray = \preg_split("/\//",$requestPath);
        \array_shift($requestPathArray);
        $request = new Request($this->path_array, $requestPathArray);

        //Call middleware function
        if($this->middleware != null) {
            if(is_string($this->middleware)) {                
                $functionPath = 'PHPExpress\\Http\\' . $this->middleware;
                if(function_exists($functionPath)) {
                    $request = \call_user_func($functionPath, $request);
                }
                else throw new Exception("That middleware does not exist", 500);
            }
        }
        $response = new Response($this->flags);

        //Calls method associated with this Route
        \call_user_func($this->handle, $request, $response);
    }  
    /**
     * Executes preflight function (default preflight function if not set)
     * @return void
     */
    public function invoke_preflight() {
        \call_user_func($this->prefilght_handle);
    }  
}

//* Middlewares:

/**
 * @param Request $request
 * 
 * @return Request
 */
function json(Request $request) {
    try {
        $request->body = json_decode($request->body, true);
    }
    catch (Exception $e) {
        header("Error: Wrong or corrupted json data!", true, 401);
        exit();
    }
    return $request;
};