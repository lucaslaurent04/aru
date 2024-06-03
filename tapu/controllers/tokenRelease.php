<?php

/**
 * Release the token of a given instance.
 * ! Not sure
 *
 * @param array{JWT: string, instance: string} $data
 * @return array{code: int, message: string}
 * @throws Exception
 */

function tokenRelease(array $data): array
{
    $status_code = 201;
    $message = '';

    if (!isset($data['JWT']) || !isset($data['instance'])) {
        throw new Exception('Bad Request', 400);
    }

    // Get the backup host JWT
    $backup_host_jwt = file_get_contents('/home/status/jwt.txt');
    if ($backup_host_jwt === false) {
        throw new Exception('Server Error while reading backup host JWT', 500);
    }

    // Create a backup token with the backup host JWT and the B2Host JWT + instance name
    $b2_instance_backup_token = openssl_encrypt($data['JWT'] . $data['instance'], 'AES-256-CBC', $backup_host_jwt);
    if ($b2_instance_backup_token === false) {
        throw new Exception('Server Error while creating backup token', 500);
    }

    // read the backup_tokens.json file and if lines are more than 10, return status code 429
    $backup_tokens_filename = '/home/aru/tapu/backup_tokens.json';
    $max_retries = 10;
    $retry_count = 0;
    $locked = false;
    $fp = fopen($backup_tokens_filename, 'r+');

    while ($retry_count < $max_retries) {
        if (flock($fp, LOCK_EX)) {
            $locked = true;
            break;
        } else {
            $retry_count++;
            fclose($fp);
            sleep(1); // Wait for 1 second before retrying
        }
    }

    if (!$locked) {
        throw new Exception("Couldn't get the lock after $max_retries attempts!", 500);
    }

    // Read the file content
    $content = fread($fp, filesize($backup_tokens_filename));

    // Convert the content to JSON
    /** @var array{tokens: string[]} $backup_tokens_file_content */
    $backup_tokens_file_content = json_decode($content, true);

    // Adapt the value
    unset($backup_tokens_file_content['tokens'][$b2_instance_backup_token]);

    // Convert back to JSON
    $newContent = json_encode($backup_tokens_file_content);

    // Truncate the file and write the new content
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, $newContent);

    // Release the lock
    flock($fp, LOCK_UN);

    // Close the file
    fclose($fp);

    // delete ftp account based on the instance name
    $username = escapeshellarg($data['instance']);
    exec('userdel -f ' . $username);

    $message = 'Token released';

    return [
        'code' => $status_code,
        'message' => $message
    ];
}