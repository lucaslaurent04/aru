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
function token_create(array $data): array {
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

    $instance = $data['instance'];

    // Retrieve the list files in tokens directory
    $tokens = scandir(BASE_DIR.'/tokens');

    // Remove the '.' and '..'
    $tokens = array_values(array_diff($tokens, ['.', '..', '.gitignore']));

    if(count($tokens) >= intval(getenv('MAX_TOKEN'))) {
        throw new InvalidArgumentException("max_token_reached_try_later", 400);
    }

    // Create token
    $token = bin2hex(random_bytes(16));
    $created_at = date('Y-m-d H:i:s');

    // Add the creation datetime and instance
    $token_data = compact('token', 'created_at', 'instance');

    // Create file to persist the token
    file_put_contents(BASE_DIR."/tokens/$instance.json", json_encode($token_data));

    // Create a new system user with no shell access (for FTP use)
    $instance_escaped = escapeshellarg($data['instance']);
    $username = $instance_escaped;
    $password = bin2hex(random_bytes(16));

    exec("useradd -m -s /sbin/nologin $username");

    // Set the password for the user
    exec("echo '$username:$password' | sudo chpasswd");

    // Set the user's home directory
    $home_directory = getenv('BACKUPS_DISK_MOUNT').'/'.$instance_escaped;
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