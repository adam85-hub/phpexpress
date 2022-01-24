<?php
require_once dirname(__DIR__, 1) . "/handler.php";

use PHPExpress\Http\Request;
use PHPExpress\Http\Response;

$handler = new PHPExpress\Handler([
    "PATH_TO_HANDLE" => "/phpexpress/test",
    "RESPONSE_FLAGS" => JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
]);

$handler->get("/wpis/+:id", function(Request $req, Response $res) {
    $res->send(["id" => $req->params["id"]]);
});

$handler->get("/cus/*", function($none, Response $res) {
    $res->send("It works! <i>tada!</i>");
});

$handler->get("/*", function(Request $req, Response $res) {
    $fullpath = $req->fullRequestPath;
    $res->setError(404, "Requested resource not found!")->send("404 page not found: \"$fullpath\"");
});

$handler->listen();