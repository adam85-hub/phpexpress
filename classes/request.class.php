<?php
namespace PHPExpress;

/**
 * Http request.
 */
class Request {
    /**
     * @var array Request headers.
     */
    public $headers;
    /**
     * @var string|array Body of request.
     */
    public $body;
    public $params;
    public $queryParams;

    /**
     * @param array $path_array Array that contains path splitted by "/".
     */
    function __construct(array $template_path_array, array $request_path_array)
    {
        $this->headers = apache_request_headers();
        if(file_exists('php://input')) $this->body = file_get_contents("php://input");
        else $this->body = null;
        switch($_SERVER["REQUEST_METHOD"]) {
            case "GET":
                $this->queryParams = &$_GET;
                break;
            case "POST":
                $this->queryParams = &$_POST;
                break;
            default: 
                $this->queryParams = null;
        }   
        
        for($i = 0; $i < count($request_path_array); $i++) {
            if(\strpos($template_path_array[$i], ":") === 0) {
                $this->params[substr($template_path_array[$i], 1)] = $request_path_array[$i];
            }
            else if(\strpos($template_path_array[$i], "+:") === 0) {
                $this->params[substr($template_path_array[$i], 2)] = intval($request_path_array[$i]);
            }
        }
    }
}