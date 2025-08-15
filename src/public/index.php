<?php
declare(strict_types=1);

session_start();
if (empty($_SESSION['csrf_token']))
{
    $_SESSION['csrf_token'] = bin2hex(random_bytes(35));
}
ob_start();


set_exception_handler('exception_handler');

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$navigation = [
    "Home" => "/",
    "Categories" => "/categories",
    "Expenses" => "/expenses",
];

function doRouting($reqUri): array
{
    $viewsDir = "/views";
    $filepath = "";
    $status = 200;

    if ($reqUri === "/" || $reqUri === "") {
        $filepath = __DIR__ . $viewsDir . "/home.php";
    } else {
        $uris = array_values($GLOBALS['navigation']);
        $found = array_find($uris, static fn($u) => $u === $reqUri);

        if ($found) {
            $filepath = __DIR__ . $viewsDir . $found . ".php";
        } else {
            $filepath = __DIR__ . $viewsDir . "/404.php";
            $status = 404;
        }
    }

    return [
        "status" => $status,
        "filepath" => $filepath
    ];
}

function exception_handler(Throwable $exc)
{
    echo "Exception: \n";
    echo $exc->getMessage();
}

['status' => $status, 'filepath' => $filepath] = doRouting($requestUri);
http_response_code($status);


include "templates/head.html";
include "templates/nav.php";

require $filepath;

include "templates/footer.html";
