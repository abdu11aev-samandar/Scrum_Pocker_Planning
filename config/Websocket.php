<?php

class Websocket
{

    /*$host = 'localhost';
    $port = 8000;
    createWebSocketServer($host, $port);*/

    function createWebSocketServer($host, $port)
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die('Socket creation failed');
        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($socket, $host, $port) or die('Socket binding failed');
        socket_listen($socket, 10) or die('Socket listening failed');

        echo "WebSocket server ishga tushdi: {$host}:{$port}\n";

        while (true) {
            $newSocket = socket_accept($socket);
            $header = socket_read($newSocket, 1024);
            performHandshaking($header, $newSocket, $host, $port);

            socket_getpeername($newSocket, $client_ip);
            $client_ip = str_replace("::ffff:", "", $client_ip);

            $header = socket_read($newSocket, 1024);
            sendSocketMessage($header, $newSocket);

            socket_close($newSocket);
        }

        socket_close($socket);
    }

    function sendSocketMessage($message, $socket)
    {
        $message = unmask($message);
        $messageObj = json_decode($message);

        $user_id = $messageObj->user_id;
        $task_id = $messageObj->task_id;
        $card = $messageObj->card;

        $cards = [];
        $cards[] = intval($card);
        $avg_card = array_sum($cards) / count($cards);

        $response = [
            'status' => 'success',
            'message' => 'Muvaffaqiyatli boldi',
            'avg_card' => $avg_card
        ];

        $response = json_encode($response);
        $response = mask($response);
        socket_write($socket, $response, strlen($response));
    }

    function performHandshaking($header, $socket, $host, $port)
    {
        $headers = [];
        $lines = preg_split("/\r\n/", $header);
        foreach ($lines as $line) {
            $line = rtrim($line);
            if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
                $headers[$matches[1]] = $matches[2];
            }
        }

        $secKey = $headers['Sec-WebSocket-Key'];
        $secAccept = base64_encode(sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));

        $upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            "WebSocket-Origin: $host\r\n" .
            "WebSocket-Location: ws://$host:$port\r\n" .
            "Sec-WebSocket-Accept:$secAccept\r\n\r\n";

        socket_write($socket, $upgrade, strlen($upgrade));
    }

    function unmask($text)
    {
        $length = ord($text[1]) & 127;

        if ($length == 126) {
            $masks = substr($text, 4, 4);
            $data = substr($text, 8);
        } elseif ($length == 127) {
            $masks = substr($text, 10, 4);
            $data = substr($text, 14);
        } else {
            $masks = substr($text, 2, 4);
            $data = substr($text, 6);
        }

        $text = "";
        for ($i = 0; $i < strlen($data); ++$i) {
            $text .= $data[$i] ^ $masks[$i % 4];
        }

        return $text;
    }

    function mask($text)
    {
        $b1 = 0x80 | (0x1 & 0x0f);
        $length = strlen($text);

        if ($length <= 125) {
            $header = pack('CC', $b1, $length);
        } elseif ($length > 125 && $length < 65536) {
            $header = pack('CCn', $b1, 126, $length);
        } elseif ($length >= 65536) {
            $header = pack('CCNN', $b1, 127, $length);
        }

        return $header . $text;
    }

}