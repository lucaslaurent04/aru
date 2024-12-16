<?php

/**
 * Returns a list of backups for a specific instance.
 *
 * @param array{instance: string} $data
 * @return array{code: int, body: string[]}
 * @throws Exception
 */
function instance_backups(array $data): array {
    if(!isset($data['instance'])) {
        throw new InvalidArgumentException("missing_instance", 400);
    }

    $domain_name_pattern = '/^(?!\-)(?:[a-zA-Z0-9\-]{1,63}\.)+[a-zA-Z]{2,}$/';
    if(
        !is_string($data['instance']) || empty($data['instance']) || strlen($data['instance']) > 32
        || preg_match($domain_name_pattern, $data['instance']) === 0
        || $data['instance'] !== basename($data['instance'])
    ) {
        throw new InvalidArgumentException("invalid_instance", 400);
    }

    $backups_path = getenv('BACKUPS_PATH') ?: false;
    if(!$backups_path) {
        throw new Exception("BACKUPS_PATH_not_configured", 500);
    }

    // Remove ending / if present
    if($backups_path[strlen($backups_path) - 1] === '/') {
        $backups_path = substr($backups_path, 0, -1);
    }

    if(!is_dir($backups_path)) {
        throw new Exception("BACKUPS_PATH_invalid", 500);
    }

    $instance_backups_path = $backups_path.'/'.$data['instance'];

    // Retrieve the list files contained in a folder
    $instance_backups = scandir($instance_backups_path);
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
