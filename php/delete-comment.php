<?php
session_start();
include 'connect.php';

if (isset($_GET['comment_id']) && isset($_SESSION['id'])) {
    $comment_id = intval($_GET['comment_id']);
    $user_id = $_SESSION['id']; // Bejelentkezett felhasználó azonosítója

    // Lekérdezzük a felhasználó szerepét
    $roleQuery = "SELECT role FROM users WHERE id = ?";
    $stmt = $dbconn->prepare($roleQuery);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $role = $user['role'];

        if ($role === 2) {
            // Ha moderátor, akkor archiválás
    $updateQuery = "UPDATE comments SET status = 0 WHERE id = ?";
    $stmt = $dbconn->prepare($updateQuery);
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    
    // Logolás a comment_admin_logs táblába
    $logQuery = "INSERT INTO comment_admin_logs (admin_user_id, comment_id, action, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $dbconn->prepare($logQuery);
    $action = 1; // Archiválás
    $stmt->bind_param("iii", $user_id, $comment_id, $action);
    $stmt->execute();
        } else {
            // Ha nem moderátor, akkor teljes törlés
            $deleteQuery = "DELETE FROM comments WHERE id = ?";
            $stmt = $dbconn->prepare($deleteQuery);
            $stmt->bind_param("i", $comment_id);
            $stmt->execute();
        }
    }

    // Honnan jött a felhasználó?
    $redirect = isset($_GET['redirect']) ? urldecode($_GET['redirect']) : '../index.php';

    // Visszairányítás az eredeti oldalra
    header("Location: $redirect");
    exit();
}

// Ha nincs post_id vagy a felhasználó nincs bejelentkezve, visszairányítás a főoldalra
header("Location: ../index.php");
exit();
