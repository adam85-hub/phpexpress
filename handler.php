<?php
namespace PHPExpress;

use Exception;
use TypeError;

require_once __DIR__ . "/http/route.class.php";

/**
 * Handles requests to server, requires proper ".htaccess" configuration.
 */
class Handler {
    private $routes;
    private $config;
    private $middleware;
    private $globalResponseFlags;

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
        $newRoute = new Route($route, $callback, "get", $this->globalResponseFlags);
        if($this->middleware != null) $newRoute->use($this->middleware);
        array_push($this->routes, $newRoute);
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
        $newRoute = new Route($route, $callback, "post", $this->globalResponseFlags);
        if($this->middleware != null) $newRoute->use($this->middleware);
        array_push($this->routes, $newRoute);
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
        $newRoute = new Route($route, $callback, "delete", $this->globalResponseFlags);
        if($this->middleware != null) $newRoute->use($this->middleware);
        array_push($this->routes, $newRoute);
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
        $newRoute = new Route($route, $callback, "put", $this->globalResponseFlags);
        if($this->middleware != null) $newRoute->use($this->middleware);
        array_push($this->routes, $newRoute);
        return $this->routes[count($this->routes) -1];
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
     * Handles requested route.
     * @param string $requestedRoute - requested route.
     * 
     * @return void
     */
    public function handle(string $requestedRoute) {
        if($_SERVER['REQUEST_METHOD'] != 'OPTIONS') {
            foreach($this->routes as $route) {
                if($route->isRoutePath($requestedRoute)) {
                    $route->invoke($requestedRoute);
                    return;
                }
            }
        }
        else {
            //TODO: handle preflight request
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