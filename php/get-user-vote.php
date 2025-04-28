<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Nincs bejelentkezve']);
    exit;
}

$user_id = $_SESSION['id'];

// Lekérdezzük a posztokra leadott szavazatokat
$post_query = "SELECT post_id, vote_type FROM post_votes WHERE user_id = ?";
$post_stmt = $dbconn->prepare($post_query);
$post_stmt->bind_param("i", $user_id);
$post_stmt->execute();
$post_result = $post_stmt->get_result();

$post_votes = [];
while ($row = $post_result->fetch_assoc()) {
    $post_votes[] = $row;
}

// Lekérdezzük a kommentekre leadott szavazatokat
$comment_query = "SELECT comment_id, vote_type FROM comment_votes WHERE user_id = ?";
$comment_stmt = $dbconn->prepare($comment_query);
$comment_stmt->bind_param("i", $user_id);
$comment_stmt->execute();
$comment_result = $comment_stmt->get_result();

$comment_votes = [];
while ($row = $comment_result->fetch_assoc()) {
    $comment_votes[] = $row;
}

echo json_encode(['success' => true, 'post_votes' => $post_votes, 'comment_votes' => $comment_votes]);
?>