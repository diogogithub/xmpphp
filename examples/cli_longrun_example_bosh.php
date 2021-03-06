<?php
/**
 * @file: XMPPHP Cli example BOSH
 *
 * @info: If this script doesn't work, are you running 64-bit PHP with < 5.2.6?
 */

/**
 * Activate full error reporting
 * error_reporting(E_ALL & E_STRICT);
 *
 * XMPPHP Log levels:
 *
 * LEVEL_ERROR   = 0;
 * LEVEL_WARNING = 1;
 * LEVEL_INFO    = 2;
 * LEVEL_DEBUG   = 3;
 * LEVEL_VERBOSE = 4;
 */

$conf = [
    'host' => 'server.tld',
    'port' => 5280,
    'username' => 'username',
    'password' => 'password',
    'resource' => 'xmpphp',
    'server' => 'http://server.tld:5280/xmpp-httpbind',
    'print_log' => true,
    'log_level' => XMPPHP\Log::LEVEL_VERBOSE,
];

// Easy and simple for access to variables with their names
extract($conf);

$conn = new XMPPHP\BOSH($host, $port, $username, $password, $proto, $server, $print_log, $log_level);
$conn->autoSubscribe();

try {
    $conn->connect();

    while (!$conn->isDisconnected()) {
        $events = ['message', 'presence', 'end_stream', 'session_start'];
        $payloads = $conn->processUntil($events);

        foreach ($payloads as $result) {
            list($event, $data) = $result;

            if (isset($data)) {
                extract($data);
            }

            switch ($event) {

                case 'message':

                    if (!$body) {
                        break;
                    }

                    echo str_repeat('-', 80);
                    echo "Message from: $from";

                    if (isset($subject)) {
                        echo "Subject: $subject";
                    }

                    echo $body;
                    echo str_repeat('-', 80);

                    $cmd = explode(' ', $body);
                    $body = "Mi no entender! '$body'";
                    $conn->message($from, $body, $type);

                    if (isset($cmd[0])) {
                        if ($cmd[0] == 'quit') {
                            $conn->disconnect();
                        }

                        if ($cmd[0] == 'break') {
                            $conn->send('</end>');
                        }
                    }
                    break;

                case 'presence':

                    echo "Presence: $from [$show] $status\n";
                    break;

                case 'session_start':

                    echo "Session start\n";
                    $conn->getRoster();
                    $conn->presence('Quasar!');
                    break;
            }
        }
    }
} catch (XMPPHP\Exception $e) {
    die($e->getMessage());
}
