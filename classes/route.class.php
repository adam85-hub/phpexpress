<?php
namespace PHPExpress;

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
    private $handle;
    private string $method;
    private $middleware;

    /**
     * @param string $path path to route.
     * @param callable $handle function wich get executed when route is requested.
     */
    function __construct(string $path, callable $handle, string $method)
    {
        $this->path = $path;
        $this->handle = $handle;
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
                if(\strpos($this->path_array[$i], ":") == 0) {
                    if($requestPathArray[$i] == "") $matched = false;
                }
                else if (\strpos($this->path_array[$i], "+:") == 0){
                    if(\is_numeric($requestPathArray[$i]) == false) $matched = false;
                }
                else {
                    if($this->path_array[$i] != $requestPathArray[$i]) $matched = false;
                }
            }
        }

        if($matched && $this->method == $_SERVER['REQUEST_METHOD']) {
            return true;
        }

        return false;
    }
    /**
     * Adds middleware to this single Route
     * @param mixed $middleware If string it will try to find and apply one of built in middlewares.
     * If it's callable it will call this user defined function.
     * 
     * @return void
     */
    public function use($middleware) {
        if(is_string($middleware) == false && is_callable($middleware)) {
            throw new TypeError('$middleware can be only string or callable', 500);
        }
        else {
            $this->middleware = $middleware;
        }
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
                $functionPath = 'PHPExpress\\' . $this->middleware;
                if(function_exists($functionPath)) {
                    $request = \call_user_func($functionPath, $request);
                }
                else throw new Exception("That middleware does not exist", 500);
            }
        }

        $response = new Response();
        \call_user_func($this->handle, $request, $response);
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