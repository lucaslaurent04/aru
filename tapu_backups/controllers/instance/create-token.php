<?php

/**
 * Creates and returns a backup token, if the MAX_TOKEN limit hasn't been reach yet.
 *
 * @param array{instance: string} $data
 * @return array{
 *     code: int,
 *     body: array{
 *         token: string,
 *         credentials: array{
 *             username: string,
 *             password: string
 *         }
 *     }
 * }
 * @throws Exception
 */
function instance_create_token(array $data): array {
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

    $no_delay = $data['no_delay'] ?? false;
    if(!is_bool($no_delay)) {
        throw new InvalidArgumentException("invalid_no_delay", 400);
    }

    $backups_path = getenv('BACKUPS_PATH') ?: false;
    if(!$backups_path) {
        throw new Exception("BACKUPS_PATH_not_configured", 500);
    }

    if(!is_dir($backups_path)) {
        throw new Exception("BACKUPS_PATH_invalid", 500);
    }

    $instance = $data['instance'];

    // Retrieve the tokens
    $tokens = glob(TOKENS_DIR.'/*.json');

    $max_token = getenv('MAX_TOKEN') ?: '3';
    if(!is_numeric($max_token)) {
        throw new Exception("max_token_not_numeric", 500);
    }

    $max_retries = 10;
    $retry_count = 0;
    while(count($tokens) >= $max_token) {
        if($retry_count >= $max_retries || $no_delay) {
            throw new InvalidArgumentException("max_token_reached_retry_limit", 400);
        }

        sleep(120); // Pause before retrying
        $retry_count++;

        // Refresh token list
        $tokens = glob(TOKENS_DIR . '/*.json');
    }

    // Create token
    $token = bin2hex(random_bytes(16));
    $created_at = date('Y-m-d H:i:s');

    // Add the creation datetime and instance
    $token_data = compact('token', 'created_at', 'instance');

    // Create file to persist the token
    file_put_contents(TOKENS_DIR."/$instance.json", json_encode($token_data));

    // Create a new system user with no shell access (for FTP use)
    $username = $data['instance'];
    $password = bin2hex(random_bytes(16));

    exec("useradd -m -s /usr/sbin/nologin $username");

    // Allow FTP access to nologin newly created user
    // exec("usermod -s /bin/bash $username");

    // Set the password for the user
    exec("echo '$username:$password' | sudo chpasswd");

    // Set the user's home directory
    $home_directory = $backups_path.'/'.$username;
    exec("mkdir $home_directory");
    exec("usermod -d $home_directory $username");

    // Set proper permissions
    exec("chown $username:$username $home_directory");

    return [
        'code' => 201,
        'body' => [
            'token'         => $token,
            'credentials'   => [
                'username'      => $data['instance'],
                'password'      => $password
            ]
        ]
    ];
}
