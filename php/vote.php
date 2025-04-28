<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Nincs bejelentkezve']);
    exit;
}

$user_id = $_SESSION['id']; 
$type = $_POST['type'] ?? null; 
$vote_type = $_POST['vote_type'] ?? null; 
$id = isset($_POST['id']) ? intval($_POST['id']) : null;

if (!$type || !$vote_type || !$id) {
    echo json_encode(['success' => false, 'message' => 'Hiányzó paraméterek']);
    exit;
}

if ($type === "post") {
    $table = "post_votes";
    $id_column = "post_id";
} else if ($type === "comment") {
    $table = "comment_votes";
    $id_column = "comment_id";
} else {
    echo json_encode(['success' => false, 'message' => 'Érvénytelen típus']);
    exit;
}

$query = "SELECT * FROM $table WHERE user_id = ? AND $id_column = ?";
$stmt = $dbconn->prepare($query);
$stmt->bind_param("ii", $user_id, $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $existing_vote = $result->fetch_assoc();

    if ($existing_vote['vote_type'] === $vote_type) {
        $delete_query = "DELETE FROM $table WHERE user_id = ? AND $id_column = ?";
        $delete_stmt = $dbconn->prepare($delete_query);
        $delete_stmt->bind_param("ii", $user_id, $id);

        if ($delete_stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Szavazat törölve!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Hiba történt a szavazat törlésében']);
        }
    } else {
        $update_query = "UPDATE $table SET vote_type = ? WHERE user_id = ? AND $id_column = ?";
        $update_stmt = $dbconn->prepare($update_query);
        $update_stmt->bind_param("sii", $vote_type, $user_id, $id);

        if ($update_stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Szavazat frissítve!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Hiba történt a szavazat frissítésében']);
        }
    }
} else {
    $query = "INSERT INTO $table (user_id, $id_column, vote_type, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $dbconn->prepare($query);
    $stmt->bind_param("iis", $user_id, $id, $vote_type);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Szavazat mentve!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Hiba történt a szavazat mentésekor']);
    }
}
?>