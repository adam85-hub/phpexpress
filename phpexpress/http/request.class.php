<?php
namespace PHPExpress\Http;

/**
 * Http request.
 */
class Request {
    /**
     * @var array Request headers.
     */
    public $headers;
    /**
     * @var string|array|mixed   Body of request.
     */
    public $body;
    public $params;
    public $queryParams;
    public $formData;
    public $fullRequestPath;

    /**
     * @param array $path_array Array that contains path splitted by "/".
     */
    function __construct(array $template_path_array, array $request_path_array)
    {
        $this->headers = apache_request_headers();
        $this->body = file_get_contents("php://input");
        
        //Get query params and form data
        $this->queryParams = null;
        $this->formData = null;
        switch($_SERVER["REQUEST_METHOD"]) {
            case "GET":
                $this->queryParams = &$_GET;
                break;
            case "POST":
                $this->formData = &$_POST;
                break;                
        }   
        
        for($i = 0; $i < count($template_path_array); $i++) {
            if(\strpos($template_path_array[$i], ":") === 0) {
                $this->params[substr($template_path_array[$i], 1)] = $request_path_array[$i];
            }
            else if(\strpos($template_path_array[$i], "+:") === 0) {
                $this->params[substr($template_path_array[$i], 2)] = intval($request_path_array[$i]);
            }
        }

        $this->fullRequestPath = $_SERVER["REQUEST_URI"];
    }    
}