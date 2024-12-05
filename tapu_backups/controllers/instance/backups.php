<?php

/**
 * Returns a list of backups for a specific instance.
 *
 * @param array{instance: string} $data
 * @return array{code: int, body: string[]}
 */
function instance_backups(array $data): array {
    // Retrieve the list files contained in a folder
    $backups = scandir('/home/backups/'.$data['instance']);

    // Remove the '.' and '..' and 'ubuntu' and 'docker' entries
    $backups = array_diff($backups, ['.', '..', 'ubuntu', 'docker']);

    return [
        'code' => 200,
        'body' => $backups
    ];
}