<?php
require_once "../handler.php";

use PHPExpress\Request;
use PHPExpress\Response;

$handler = new PHPExpress\Handler([
    "PATH_TO_HANDLE" => "/phpexpress/test"
]);

$handler->get("/wpis/+:id", function(Request $req, Response $res) {
    $res->send(["id" => $req->params["id"]]);
});

$handler->listen();