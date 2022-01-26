<?php
require_once dirname(__DIR__, 1) . "/phpexpress/phpexpress.php"; // Path to phpexpress.php

use PHPExpress\Http\Request;
use PHPExpress\Http\Response;

$handler = new PHPExpress\Http\Handler([
    "PATH_TO_HANDLE" => "/phpexpress/setup", //Access path to folder containing this file
    "RESPONSE_FLAGS" => JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES //Flags describing how responses will be encoded to json
]);

$handler->get("/", function($none, Response $response) { //Default page
    $response->send("Main page");
});

$handler->get("/post/+:id", function(Request $req, Response $res) { //Route with args
    $res->send(["id" => $req->params["id"]]);
});

$handler->get("/*", function(Request $req, Response $res) { //Matches all paths
    $fullpath = $req->fullRequestPath;
    $res->setError(404, "Requested resource not found!")->send("404 page not found: \"$fullpath\"");
});

$handler->post("/test", function(Request $req, Response $res) { // Built in middleware example
    $res->send($req->body);
})->use("json");

$handler->listen(); //If you add Route after calling this function it won't work

$handler->post("/test", function() {echo "test";}); // Does not work (try above listen())