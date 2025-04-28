<?php
session_start();
include 'connect.php';

$type = $_POST['type'] ?? null; 
$id = isset($_POST['id']) ? intval($_POST['id']) : null;

if (!$type || !$id) {
    echo json_encode(['success' => false, 'message' => 'Hiányzó paraméterek']);
    exit;
}

if ($type === 'post') {
    $table = "post_votes";
    $id_column = "post_id";
} else if ($type === 'comment') {
    $table = "comment_votes";
    $id_column = "comment_id";
} else {
    echo json_encode(['success' => false, 'message' => 'Érvénytelen típus']);
    exit;
}

$query = "SELECT SUM(CASE WHEN vote_type = 'upvote' THEN 1 ELSE -1 END) as score FROM $table WHERE $id_column = ?";

$stmt = $dbconn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$vote_result = $stmt->get_result()->fetch_assoc();

$score = $vote_result['score'] !== null ? $vote_result['score'] : 0;

echo json_encode(['success' => true, 'score' => $score]);
?>