<?php

/**
 * Releases the backup token of a given instance.
 *
 * @param array{instance: string, token: string} $data
 * @return array{code: int, body: string}
 * @throws Exception
 */
function token_release(array $data): array {
    if(!isset($data['instance'])) {
        throw new InvalidArgumentException("missing_instance", 400);
    }

    if(
        !is_string($data['instance']) || empty($data['instance']) || strlen($data['instance']) > 32
        || preg_match('/^(?!\-)(?:[a-zA-Z0-9\-]{1,63}\.)+[a-zA-Z]{2,}$/', $data['instance']) === 0
        || $data['instance'] !== basename($data['instance'])
    ) {
        throw new InvalidArgumentException("invalid_instance", 400);
    }

    if(!isset($data['token'])) {
        throw new InvalidArgumentException("missing_token", 400);
    }

    if(
        !is_string($data['token']) || strlen($data['token']) !== 32
        || !file_exists(BASE_DIR.'/tokens/'.$data['instance'].'.json')
    ) {
        throw new InvalidArgumentException("invalid_token", 400);
    }

    $token_data_json = file_get_contents(BASE_DIR.'/tokens/'.$data['instance'].'.json');
    $token_data = json_decode($token_data_json, true);

    if($token_data['token'] !== $data['token']) {
        throw new InvalidArgumentException("invalid_token", 400);
    }

    // Remove system user with no shell access (for FTP use) (keep home directory)
    $instance_escaped = escapeshellarg($data['instance']);
    $username = $instance_escaped;
    exec("userdel $username");

    // Remove file
    exec('rm '.BASE_DIR.'/tokens/'.$instance_escaped);
    throw new Exception('rm '.BASE_DIR.'/tokens/'.$instance_escaped);

    return [
        'code' => 200,
        'body' => "token_released"
    ];
}