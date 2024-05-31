<?php

/**
 * Retrieve a list of logs for a given instance based on some criteria.
 *
 * @param array{
 *     instance: string,
 *     filter: array{
 *         date_from?: string,
 *         date_to?: string,
 *         single_date: bool,
 *         level: string,
 *         layer: string,
 *         keyword?: string
 *     }
 * } $data
 * @return array{status_code: int, message: string[]}
 */
function instance_logs(array $data): array
{
    $status_code = 201;
    $message = '';

    if (!isset($data['instance']) || !isset($data['filter'])) {
        throw new InvalidArgumentException('Bad Request');
    }

    return [
        'status_code' => $status_code,
        'message' => $message
    ];
}