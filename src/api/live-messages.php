<?php
header("Content-Type: text/event-stream");
header("Cache-Control: no-cache");
header("Connection: keep-alive");

require_once "../config.php";

$ticket = isset($_GET["ticket"]) ? $_GET["ticket"] : "";
$lastId  = isset($_GET["last"]) ? intval($_GET["last"]) : 0;

while (true) {
    // Ambil pesan baru
    $stmt = $db->prepare("
        SELECT * FROM ticket_messages 
        WHERE ticket_number = ? AND id > ? 
        ORDER BY id ASC
    ");
    $stmt->execute([$ticket, $lastId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($messages) {
        echo "event: message\n";
        echo "data: " . json_encode($messages) . "\n\n";
        ob_flush();
        flush();
        exit;
    }

    // Heartbeat tiap 15 detik
    echo "event: ping\ndata: 1\n\n";
    ob_flush();
    flush();

    sleep(1);
}