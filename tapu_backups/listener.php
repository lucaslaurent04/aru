<?php

include_once '../helpers/env.php';
include_once '../helpers/http-response.php';
include_once '../helpers/request-handler.php';

const BASE_DIR = __DIR__;
const CONTROLLERS_DIR = __DIR__ . '/controllers';
const TOKENS_DIR = __DIR__ . '/tokens';

$request = [
    'method'        => $_SERVER['REQUEST_METHOD'],
    'uri'           => $_SERVER['REQUEST_URI'],
    'content_type'  => $_SERVER['CONTENT_TYPE'],
    'data'          => file_get_contents("php://input"),
];

$allowed_routes = [
    '/status',              /* @link status() */
    '/instance/backups',    /* @link instance_backups() */
    '/token/create',        /* @link token_create() */
    '/token/release'        /* @link token_release() */
];

['body' => $body, 'code' => $code] = handle_request($request, $allowed_routes);

send_http_response($body, $code);
