<?php
session_start();
require 'connect.php';

if (!isset($_GET['user_id']) || !isset($_GET['type'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit();
}

$user_id = $_GET['user_id'];
$type = $_GET['type'];

if ($type === 'followers') {
    $query = "SELECT u.id, u.username, u.profile_picture_url FROM users u JOIN follows f ON u.id = f.following_user_id WHERE f.followed_user_id = ?";
} elseif ($type === 'following') {
    $query = "SELECT u.id, u.username, u.profile_picture_url FROM users u JOIN follows f ON u.id = f.followed_user_id WHERE f.following_user_id = ?";
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid type']);
    exit();
}

$stmt = $dbconn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode($result);
$dbconn->close();
?>