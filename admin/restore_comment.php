<?php
session_start();
require '../php/connect.php';
// Csak moderátorok érhetik el az oldalt
if (!isset($_SESSION['id'])) {
    // A felhasználó nincs bejelentkezve
    header("Location: ../login.php");
    exit();
}
if ($_SESSION['user_role'] == 1) {
    header("Location: access_denied.php");
    exit();
}
define('ACTION_RESTORED', 2); 

if (isset($_GET['id'])) {
    $comment_id = intval($_GET['id']);
    
    $sql = "UPDATE comments SET status = 1 WHERE id = ?";
    $stmt = $dbconn->prepare($sql);
    $stmt->bind_param("i", $comment_id);
    
    if ($stmt->execute()) {
        $admin_user_id = $_SESSION['user_id']; 
        $log_sql = "INSERT INTO comment_admin_logs (admin_user_id, comment_id, action, created_at) VALUES (?, ?, ?, NOW())";
        $log_stmt = $dbconn->prepare($log_sql);
        $log_stmt->bind_param("iii", $admin_user_id, $comment_id, ACTION_RESTORED);
        $log_stmt->execute();
        
        header("Location: moderator.php?message=Comment restored successfully");
        exit();
    } else {
        echo "Error during restoration: " . $dbconn->error;
    }
} else {
    echo "Invalid request.";
}