<?php

/**
 * Returns host status statistics.
 *
 * @return array{
 *     code: int,
 *     body: array{
 *         stats: array{
 *             net: array{
 *                 rx: string,
 *                 tx: string,
 *                 total: string,
 *                 avg_rate: string,
 *             }|false,
 *             cpu: string,
 *             uptime: string
 *         },
 *         instant: array{
 *             total_proc: string,
 *             ram_use: string,
 *             cpu_use: string,
 *             disk_use: string,
 *             usr_active: string,
 *             usr_total: string,
 *         },
 *         config: array{
 *             host: string,
 *             uptime: string,
 *             mem: string,
 *             cpu_qty: string,
 *             cpu_freq: string,
 *             disk: string,
 *             ip_private: string
 *         }
 *     }
 * }
 * @throws Exception
 */
function status(): array {
    $do_cmd = function ($command) {
        $result = null;
        if(exec($command, $output) !== false) {
            $result = reset($output);
        }
        return $result;
    };

    $adapt_units = function ($str) {
        return str_replace(['GiB', 'Gi', 'MiB', 'Mi', 'KiB', 'Ki', 'kbit/s'], ['G', 'G', 'M', 'M', 'K', 'K', 'kbs'], str_replace(' ', '', $str));
    };

    // Retrieve interface (usually either eth0 or ens3)
    $interface = $do_cmd('ip link show | head -3 | tail -1 | awk \'{print $2}\'');
    if(!$interface) {
        throw new Exception("unable_to_retrieve_main_interface", 500);
    }

    $interface = trim($interface, ':');

    $commands = [
        'stats' => [
            'net' => [
                'description' => "monthly network volume",
                'command'     => 'vnstat -i ' . $interface . ' -m | tail -3 | head -1',
                'adapt'       => function ($res) use ($adapt_units) {
                    if(strpos($res, '|') === false) {
                        return [
                            'rx'        => 'No data yet',
                            'tx'        => 'No data yet',
                            'total'     => 'No data yet',
                            'avg_rate'  => 'No data yet',
                        ];
                    }

                    $parts = preg_split('/\s{2,10}/', $res, 3);
                    $b = array_map($adapt_units, array_map('trim', explode('|', $parts[2])));
                    return array_combine(['rx', 'tx', 'total', 'avg_rate'], $b);
                }
            ],
            'cpu' => [
                'description' => "average CPU load (%) since last reboot",
                'command'     => 'vmstat | tail -1| awk \'{print $15}\'',
                'adapt'       => function ($res) {
                    return (100 - intval($res)) . '%';
                }
            ],
            'uptime' => [
                'description' => "average CPU load (%) since last reboot",
                'command'     => 'cat /proc/uptime | awk \'{print $1}\'',
                'adapt'       => function ($res) {
                    return (intval($res / 86400) + 1) . 'days';
                }
            ],
            'backups_disk' => [
                'description' => "percentage of usage backups disk",
                'command'     => 'df -h ' . getenv('BACKUPS_DISK_MOUNT') . ' | awk \'NR==2 {print $5}\'',
                'adapt'       => function ($res) {
                    return $res;
                }
            ]
        ],
        'instant' => [
            'total_proc' => [
                'description' => "total number of running processes",
                'command'     => 'ps aux | head -n -1 | wc -l',
                'adapt'       => function ($res) {
                    return $res;
                }
            ],
            'ram_use' => [
                'description' => "used RAM (Bytes)",
                'command'     => 'free -mh |awk \'/Mem/{print $3}\'',
                'adapt'       => function ($res) use ($adapt_units) {
                    return $adapt_units($res);
                }
            ],
            'cpu_use' => [
                'description' => "used CPU (%)",
                'command'     => 'top -bn2 -d 0.1 | grep "Cpu" | tail -1 | awk \'{print $2}\'',
                'adapt'       => function ($res) {
                    return $res . '%';
                }
            ],
            'disk_use' => [
                'description' => "consumed disk space",
                'command'     => 'df . -h | tail -1 | awk \'{print $3}\'',
                'adapt'       => function ($res) use ($adapt_units) {
                    return $adapt_units($res);
                }
            ],
            'usr_active' => [
                'description' => "total number of logged in users",
                'command'     => 'w -h  | wc -l',
                'adapt'       => function ($res) {
                    return $res;
                }
            ],
            'usr_total' => [
                'description' => "total number of logged in users",
                'command'     => 'awk -F\':\' \'$3 >= 1000 && $3 < 60000 {print $1}\' /etc/passwd | wc -l',
                'adapt'       => function ($res) {
                    return $res;
                }
            ]
        ],
        'config' => [
            'host' => [
                'description' => "host name",
                'command'     => 'hostname',
                'adapt'       => function ($res) use ($adapt_units) {
                    return $adapt_units($res);
                }
            ],
            'uptime' => [
                'description' => "time since last reboot",
                'command'     => 'uptime -s',
                'adapt'       => function ($res) {
                    return date('c', strtotime($res));
                }
            ],
            'mem' => [
                'description' => "total RAM",
                'command'     => 'free -mh | awk \'/Mem/{print $2}\'',
                'adapt'       => function ($res) use ($adapt_units) {
                    return $adapt_units($res);
                }
            ],
            'cpu_qty' => [
                'description' => "number of CPU (#)",
                'command'     => 'cat /proc/cpuinfo | grep processor | wc -l',
                'adapt'       => function ($res) {
                    return intval($res);
                }
            ],
            'cpu_freq' => [
                'description' => "CPU frequency (MHz)",
                'command'     => 'cat /proc/cpuinfo | grep -m1 MHz | awk \'{ print $4 }\'',
                'adapt'       => function ($res) {
                    $res = floatval($res);
                    if($res > 1000) {
                        $res /= 1000;
                    }
                    return round($res, 1) . 'GHz';
                }
            ],
            'disk' => [
                'description' => "total disk space",
                'command'     => 'df . -h | tail -1 | awk \'{print $2}\'',
                'adapt'       => function ($res) use ($adapt_units) {
                    return $adapt_units($res);
                }
            ],
            'ip_private' => [
                'description' => "main IP address",
                'command'     => 'ip -4 addr show ' . $interface . ' | grep \'inet \' | awk \'{print $2}\'',
                'adapt'       => function ($res) {
                    return $res;
                }
            ]
        ]
    ];

    $result = [];
    foreach($commands as $cat => $cat_commands) {
        foreach($cat_commands as $cmd => $command) {
            $res = $do_cmd($command['command']);
            $result[$cat][$cmd] = $command['adapt']($res);
        }
    }

    $result['config']['env'] = getenv();

    return [
        'code' => 200,
        'body' => $result
    ];
}
