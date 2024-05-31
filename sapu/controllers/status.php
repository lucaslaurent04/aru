<?php

/**
 * Retrieve the status of the instance.
 * ! Not sure
 *
 * @param array $data
 * @return array{status_code: int, message: string}
 */

function status(array $data): array
{
    $status_code = 201;
    $message = '';

    $do_cmd = function ($command) {
        $result = null;
        if (exec($command, $output) !== false) {
            $result = reset($output);
        }
        return $result;
    };


    $adapt_units = function ($str) {
        return str_replace(['GiB', 'Gi', 'MiB', 'Mi', 'KiB', 'Ki', 'kbit/s'], ['G', 'G', 'M', 'M', 'K', 'K', 'kbs'], str_replace(' ', '', $str));
    };

    $commands = [
        'config' => [
            'disk' => [
                'description' => "total disk space",
                'command' => 'df . -h | tail -1 | awk \'{print $2}\'',
                'adapt' => function ($res) use ($adapt_units) {
                    return $adapt_units($res);
                }
            ],

            'remaining_disk_space' => [
                'description' => "remaining disk space",
                'command' => 'df . -h | tail -1 | awk \'{print $4}\'',
                'adapt' => function ($res) use ($adapt_units) {
                    return $adapt_units($res);
                }
            ],
        ]
    ];

    $result = [];

    foreach ($commands as $cat => $cat_commands) {
        foreach ($cat_commands as $cmd => $command) {
            $res = $do_cmd($command['command']);
            $result[$cat][$cmd] = $command['adapt']($res);
        }
    }

    $response = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

    return [
        'status_code' => $status_code,
        'message' => $response
    ];
}