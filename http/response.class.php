<?php
namespace PHPExpress\Http;

/**
 * Http response.
 */
class Response {
    private $headers;
    private $body;
    private $flags;

    function __construct($flags = null)
    {
        $this->headers = apache_response_headers();
        $this->flags = $flags;
    }

    public function setHeader(string $header): Response {
        header($header);
        $this->headers = apache_response_headers();
        return $this;
    }

    public function setError(int $errorCode, string $errorMsg): Response {
        header("Error: $errorMsg", true, $errorCode);
        $this->headers = apache_response_headers();
        return $this;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function send($body) {
        if(is_string($body)) {
            echo $body;
        }
        else if(is_array($body)) {
            if($this->flags == null) echo json_encode($body);
            else echo json_encode($body, $this->flags);
        }
    }
}