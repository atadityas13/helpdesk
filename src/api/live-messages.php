<?php
/**
 * API: Live Messages (Server-Sent Events)
 * Helpdesk MTsN 11 Majalengka
 */

header("Content-Type: text/event-stream; charset=utf-8");
header("Cache-Control: no-cache");
header("Connection: keep-alive");
header("X-Accel-Buffering: no");

require_once "../config/database.php";
require_once "../helpers/functions.php";
require_once "../helpers/ticket.php";

// Validate ticket number
$ticketNumber = isset($_GET["ticket"]) ? sanitizeInput($_GET["ticket"]) : "";
$lastId = isset($_GET["last"]) ? intval($_GET["last"]) : 0;

if (empty($ticketNumber)) {
    echo "event: error\ndata: Nomor ticket harus diisi\n\n";
    flush();
    exit;
}

// Verify ticket exists
$ticket = getTicketByNumber($conn, $ticketNumber);
if (!$ticket) {
    echo "event: error\ndata: Ticket tidak ditemukan\n\n";
    flush();
    exit;
}

$maxIterations = 60; // 30 minutes with 30 second interval
$iteration = 0;

while ($iteration < $maxIterations) {
    // Get new messages
    $query = "SELECT m.*, 
                    CASE WHEN m.sender_type = 'admin' THEN COALESCE(a.username, 'Admin') ELSE 'Customer' END as sender_name
              FROM messages m
              LEFT JOIN admins a ON m.sender_type = 'admin' AND m.sender_id = a.id
              WHERE m.ticket_id = ? AND m.id > ?
              ORDER BY m.id ASC";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Live messages prepare failed: " . $conn->error);
        echo "event: error\ndata: Server error\n\n";
        flush();
        exit;
    }
    
    $stmt->bind_param("ii", $ticket['id'], $lastId);
    $stmt->execute();
    $result = $stmt->get_result();
    $messages = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    if (!empty($messages)) {
        // Ada pesan baru
        echo "event: message\n";
        echo "data: " . json_encode($messages) . "\n\n";
        ob_flush();
        flush();
        exit;
    }
    
    // Heartbeat setiap 30 detik
    echo "event: ping\ndata: 1\n\n";
    ob_flush();
    flush();

    sleep(1);
}