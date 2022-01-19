<?php
namespace PHPExpress;

use Exception;

require_once __DIR__ . "/classes/route.class.php";

/**
 * Handles requests to server, requires proper ".htaccess" configuration.
 */
class Handler {
    private $routes;
    private $config;

    /**
     * @param array $config configuration for Handler
     */
    function __construct(array $config)
    {
        $this->routes = array();
        if(array_key_exists("PATH_TO_HANDLE", $config) == false) throw new Exception("PATH_TO_HANDLE in handler config is required!");
        $this->config = $config;
    }
    /**
     * Adds handle of specified route get request to Handler. Order of invoking this method matters.
     * @param string $route Route to this get method.
     * @param callable $callback Method that gets executed when specified route is requested with get method.
     * 
     * @return void
     */
    public function get(string $route, callable $callback) {
        array_push($this->routes, new Route($route, $callback, "get"));
    }    
    /**
     * Adds handle of specified route post request to Handler. Order of invoking this method matters.
     * @param string $route Route to this post method.
     * @param callable $callback Method that gets executed when specified route is requested with post method.
     * 
     * @return void
     */
    public function post(string $route, callable $callback) {
        array_push($this->routes, new Route($route, $callback, "post"));
    }  
    /**
     * Adds handle of specified route delete request to Handler. Order of invoking this method matters.
     * @param string $route Route to this delete method.
     * @param callable $callback Method that gets executed when specified route is requested with delete method.
     * 
     * @return void
     */
    public function delete(string $route, callable $callback) {
        array_push($this->routes, new Route($route, $callback, "delete"));
    }
    /**
     * Adds handle of specified route put request to Handler. Order of invoking this method matters.
     * @param string $route Route to this put method.
     * @param callable $callback Method that gets executed when specified route is requested with put method.
     * 
     * @return void
     */
    public function put(string $route, callable $callback) {
        array_push($this->routes, new Route($route, $callback, "put"));
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