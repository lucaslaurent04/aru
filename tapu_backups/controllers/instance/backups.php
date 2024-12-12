<?php

/**
 * Returns a list of backups for a specific instance.
 *
 * @param array{instance: string} $data
 * @return array{code: int, body: string[]}
 * @throws Exception
 */
function instance_backups(array $data): array {
    // Retrieve the list files contained in a folder
    $instance_backups = scandir(getenv('BACKUPS_PATH').'/'.$data['instance']);
    if($instance_backups === false) {
        throw new Exception("backups_not_found", 404);
    }

    // Remove the '.' and '..'
    $instance_backups = array_values(array_diff($instance_backups, ['.', '..']));

    return [
        'code' => 200,
        'body' => $instance_backups
    ];
}
