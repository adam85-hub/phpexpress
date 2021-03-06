<?php
namespace PHPExpress\Http;

use Exception;
use TypeError;

require_once __DIR__ . "/route.class.php";

/**
 * Handles requests to server, requires proper ".htaccess" configuration.
 */
class Handler {
    private $routes;
    private $config;
    private $middleware;
    private $globalResponseFlags;
    private $prefilght_handle;

    /**
     * Required in config: 
     * "PATH_TO_HANDLE" - route where handler listens to requests.
     * Optional: 
     * "RESPONSE_FLAGS" - flags defining how json response should be encoded
     * @param array $config configuration for Handler
     */
    function __construct(array $config)
    {
        $this->routes = array();
        if(array_key_exists("PATH_TO_HANDLE", $config) == false) throw new Exception("PATH_TO_HANDLE in handler config is required!", 500);
        $this->config = $config;
        $this->middleware = null;
        $this->prefilght_handle = function() { };
        if(array_key_exists("RESPONSE_FLAGS", $config)) $this->globalResponseFlags = $config["RESPONSE_FLAGS"];
        else $this->globalResponseFlags = null;
    }
    /**
     * Adds handle of specified route get request to Handler. Order of invoking this method matters.
     * @param string $route Route to this get method.
     * @param callable $callback Method that gets executed when specified route is requested with get method.
     * 
     * @return Route
     */
    public function &get(string $route, callable $callback): Route {
        $this->addRoute($route, $callback, "get");
        return $this->routes[count($this->routes) -1];
    }    
    /**
     * Adds handle of specified route post request to Handler. Order of invoking this method matters.
     * @param string $route Route to this post method.
     * @param callable $callback Method that gets executed when specified route is requested with post method.
     * 
     * @return Route
     */
    public function &post(string $route, callable $callback): Route {
        $this->addRoute($route, $callback, "post");
        return $this->routes[count($this->routes) -1];
    }  
    /**
     * Adds handle of specified route delete request to Handler. Order of invoking this method matters.
     * @param string $route Route to this delete method.
     * @param callable $callback Method that gets executed when specified route is requested with delete method.
     * 
     * @return Route
     */
    public function &delete(string $route, callable $callback): Route {
        $this->addRoute($route, $callback, "delete");
        return $this->routes[count($this->routes) -1];
    }
    /**
     * Adds handle of specified route put request to Handler. Order of invoking this method matters.
     * @param string $route Route to this put method.
     * @param callable $callback Method that gets executed when specified route is requested with put method.
     * 
     * @return Route
     */
    public function &put(string $route, callable $callback): Route {
        $this->addRoute($route, $callback, "put");
        return $this->routes[count($this->routes) -1];
    }
    /**
     * Adds new route
     * @param string $method
     * 
     * @return void
     */
    private function addRoute(string $route, callable $callback, string $method): void {
        $newRoute = new Route($route, $callback, $method, $this->prefilght_handle, $this->globalResponseFlags);
        if($this->middleware != null) $newRoute->use($this->middleware);
        array_push($this->routes, $newRoute);
    }
    /**
     * Sets given middleware globally(for all routes). 
     * It affects only routes defined below this function call(if middleware is already set this function will override it for all declarations below).
     * You can also override it by calling use function on route directly.
     * @param mixed $middleware If string it will try to find and apply one of built in middlewares.
     * If it's callable it will call this user defined function.
     * 
     * @return void
     */
    public function use($middleware) {
        if(is_callable($middleware) == false && is_string($middleware) == false) throw new TypeError('$middleware must be string or callable', 500);
        else $this->middleware = $middleware;
    }
    /**
     * Sets given preflight handle globally(for all routes).
     * Works simmilar to Handler::use() function. Only diffrence is that it sets handling of preflight requests instead of applying middleware.
     * @param callable $preflight
     * @return void
     */
    public function preflight(callable $preflight) {
        if(is_callable($preflight) == false) throw new TypeError('$preflight must be callable');
        else $this->prefilght_handle = $preflight;
    }
    /**
     * Handles requested route.
     * @param string $requestedRoute - requested route.
     * 
     * @return void
     */
    public function handle(string $requestedRoute) {        
        foreach($this->routes as $route) {
            if($route->isRoutePath($requestedRoute)) {
                if($_SERVER['REQUEST_METHOD'] != 'OPTIONS') { // Checks if it is preflight request
                    $route->invoke($requestedRoute);
                    return;
                }
                else {
                    $route->invoke_preflight();
                    return;
                }
            }
        }
    }
    /**
     * Starts listening for requests.
     * @return void
     */
    public function listen() {
        $fullpath = $_SERVER["REQUEST_URI"];
        $fullpath = str_replace($this->config["PATH_TO_HANDLE"], "", $fullpath);
        $path = preg_split("/\?/",  $fullpath)[0];
        $this->handle($path);
    }
}