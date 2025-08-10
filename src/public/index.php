<?php

$requestUri = $_SERVER['REQUEST_URI'];

$navigation = [
    "Home" => "/",
    "Categories" => "/categories",
    "Expenses" => "/expenses",
];

function doRouting($reqUri): array
{
    global $navigation;

    $viewsDir = "/views";
    $filepath = "";
    $status = 200;

    if ($reqUri === "/" || $reqUri === "")
    {
        $filepath = __DIR__ . $viewsDir . "/home.php";
    }
    else
    {
        $uris = array_values($navigation);
        $found = array_find($uris, static fn($u) => $u === $reqUri);

        if ($found)
        {
            $filepath = __DIR__ . $viewsDir . $found . ".php";
        }
        else
        {
            $filepath = __DIR__ . $viewsDir . "/404.php";
            $status = 404;
        }
    }

    return [
        "status" => $status,
        "filepath" => $filepath
    ];
}

$result = doRouting($requestUri);
http_response_code($result['status']);

include "templates/head.html";
include "templates/nav.php";

require $result['filepath'];

include "templates/footer.html";
