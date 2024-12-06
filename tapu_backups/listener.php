<?php

// Include the http-response.php file to use the send_http_response function
include_once '../helpers/http-response.php';

function load_env(string $file) {
    if(!file_exists($file)) {
        throw new Exception("listener_dot_env_file_does_not_exist", 500);
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        $line = trim($line);
        list($key, $value) = explode('=', $line, 2);

        putenv(trim($key) . '=' . trim($value));
    }
}

$allowed_routes = [
    '/status',              /* @link status() */
    '/instance/backups',    /* @link instance_backups() */
    '/token/create',        /* @link token_create() */
    '/token/release'        /* @link token_release() */
];

try {
    // By convention, we accept only POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("method_not_allowed", 405);
    }

    // Check if the requested route is allowed
    if (!in_array($_SERVER['REQUEST_URI'], $allowed_routes)) {
        throw new Exception("unknown_route", 404);
    }

    if ($_SERVER['CONTENT_TYPE'] != 'application/json') {
        throw new Exception("invalid_body", 400);
    }

    // Get the request body
    $json = file_get_contents("php://input");

    // Decode JSON data
    $data = json_decode($json, true);

    // Check if data decoded successfully
    if ($data === null || gettype($data) !== 'array') {
        throw new Exception("invalid_json", 400);
    }

    $handler = trim($_SERVER['REQUEST_URI'], '/');

    $controller_file = __DIR__ . '/controllers/' . $handler . '.php';

    // Check if the controller or script file exists
    if (!file_exists($controller_file)) {
        throw new Exception("missing_script_file", 503);
    }

    // Include the controller file
    include_once $controller_file;

    $handler_method_name = preg_replace('/[-\/]/', '_', $handler);

    // Call the controller function with the request data
    if(!is_callable($handler_method_name)) {
        throw new Exception("missing_method", 501);
    }

    define('BASE_DIR', __DIR__);
    define('TOKENS_DIR', __DIR__.'/tokens');

    load_env(BASE_DIR . '/.env');

    // Respond with the returned body and code
    ['body' => $body, 'code' => $code] = $handler_method_name($data);
} catch (Exception $e) {
    file_put_contents(BASE_DIR . '/tmp.log', $e->getMessage());

    // Respond with the exception message and status code
    [$body, $code] = [$e->getMessage(), $e->getCode()];
}

// Send response with body and code
send_http_response($body, $code);
