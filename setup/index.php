<?php
require_once dirname(__DIR__, 1) . "/phpexpress.php"; // Path to phpexpress.php

use PHPExpress\Http\Request;
use PHPExpress\Http\Response;

$handler = new PHPExpress\Http\Handler([
    "PATH_TO_HANDLE" => "/phpexpress/setup", //Access path to folder containing this file
    "RESPONSE_FLAGS" => JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES //Flags describing how responses will be encoded to json
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