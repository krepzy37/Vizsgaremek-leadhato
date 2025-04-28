<?php
session_start();
require 'connect.php';

if (!isset($_SESSION['id']) || !isset($_POST['user_id']) || !isset($_POST['action'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit();
}

$following_user_id = $_SESSION['id'];
$followed_user_id = $_POST['user_id'];
$action = $_POST['action'];

if ($action === 'follow') {
    $query = "INSERT INTO follows (following_user_id, followed_user_id) VALUES (?, ?)";
    $stmt = $dbconn->prepare($query);
    $stmt->bind_param("ii", $following_user_id, $followed_user_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'action' => 'unfollow']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
    }
} elseif ($action === 'unfollow') {
    $query = "DELETE FROM follows WHERE following_user_id = ? AND followed_user_id = ?";
    $stmt = $dbconn->prepare($query);
    $stmt->bind_param("ii", $following_user_id, $followed_user_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'action' => 'follow']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
    }
}

$dbconn->close();
?>