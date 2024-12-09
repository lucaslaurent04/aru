<?php

include_once '../helpers/request-handler.php';

$allowed_routes = [
    '/status',              /* @link status() */
    '/instance/backups',    /* @link instance_backups() */
    '/token/create',        /* @link token_create() */
    '/token/release'        /* @link token_release() */
];

handle_request(
    [
        'method'        => $_SERVER['REQUEST_METHOD'],
        'uri'           => $_SERVER['REQUEST_URI'],
        'content_type'  => $_SERVER['CONTENT_TYPE'],
        'data'          => file_get_contents("php://input"),
    ],
    $allowed_routes
);
