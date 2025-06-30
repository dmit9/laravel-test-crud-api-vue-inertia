<?php

namespace App\Service;

use Illuminate\Support\Facades\Log;

class DoorController
{
    public function open(): void
    {
        $ip = '192.168.1.80';
        $port = 60000;
        $hex = '7ec1c99d10010100000000000000000000000000000000000000000000000039020d';
        $data = hex2bin($hex);

        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_sendto($socket, $data, strlen($data), 0, $ip, $port);
        socket_close($socket);
    }
}
