<?php

/**
 * Retrieve a list of backups for a given instance.
 *
 * @param array{instance: string} $data
 * @return array{status_code: int, message: string[]}
 */

function instance_backups(array $data): array
{
    $status_code = 201;
    $message = '';

    // Retrieve the list files contained in a folder
    $backups = scandir('/home/backups/' . $data['instance']);

    // remove the '.' and '..' entries
    $backups = array_diff($backups, ['.', '..']);

    return [
        'status_code' => $status_code,
        'message' => $backups
    ];
}