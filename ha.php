<?php
use Workerman\Worker;
require_once './Workerman/Autoloader.php';
require_once 'quote.php';

$discard_worker = new Worker("tcp://0.0.0.0:9");
$daytime_worker = new Worker("tcp://0.0.0.0:13");
$udp_daytime_worker = new Worker("udp://0.0.0.0:13");
$qotd_worker = new Worker("tcp://0.0.0.0:17");
$udp_qotd_worker = new Worker("udp://0.0.0.0:17");
$chargen_worker = new Worker("tcp://0.0.0.0:19");
$udp_chargen_worker = new Worker("udp://0.0.0.0:19");

$qotd_worker->onConnect = function ($connection)
{
    sendQuote($connection);
    $connection->close();
};

$udp_qotd_worker->onMessage = function ($connection, $data)
{
    sendQuote($connection);
};

$chargen_worker->onConnect = function ($connection)
{
    while (sendQuote($connection, "¡¡") !== false);
    $connection->close();
};

$udp_chargen_worker->onMessage = function ($connection, $data)
{
    $msg = "";
    while (strlen($msg) <= 512) {
        $msg .= fetchQuote();
    }
    $msg = substr($msg, 0, 512);
    $connection->send($msg);
};

$discard_worker->onMessage = function ($connection, $data)
{
    $connection->close();
};

$daytime_worker->onConnect = function ($connection)
{
    sendTimestamp($connection);
    $connection->close();
};

$daytime_worker->onMessage = function ($connection, $data)
{
    sendTimestamp($connection);
};

Worker::runAll();

function sendQuote($conn, $d = "\n") {
    return $conn->send(fetchQuote().$d);
}

function fetchQuote() {
    global $quote_count, $quote;
    $piece = mt_rand(0, $quote_count - 1);
    return $quote[$piece];
}

function sendTimestamp($conn) {
    $time = time() + 1368864000;
    $conn->send("Naive Timestamp: ".$time."\n");
}