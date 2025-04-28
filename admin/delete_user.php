<?php
require_once '../php/connect.php'; // adatbázis kapcsolat

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Érvénytelen felhasználó azonosító.");
}

$user_id = intval($_GET['id']);

// Tranzakció indítása
mysqli_begin_transaction($dbconn);

try {
    // Komment szavazatok törlése
    $stmt = mysqli_prepare($dbconn, "DELETE FROM comment_votes WHERE comment_id IN (SELECT id FROM comments WHERE user_id = ?) OR user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Post admin logok törlése (előbb, mint a post törlés!)
$stmt = mysqli_prepare($dbconn, "DELETE FROM comment_admin_logs WHERE comment_id IN (SELECT id FROM comments WHERE user_id = ?)");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

    // Komment admin logok törlése
    $stmt = mysqli_prepare($dbconn, "DELETE FROM comment_admin_logs WHERE comment_id IN (SELECT id FROM comments WHERE user_id = ?)");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Kommentek törlése
    $stmt = mysqli_prepare($dbconn, "DELETE FROM comments WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Post szavazatok törlése
    $stmt = mysqli_prepare($dbconn, "DELETE FROM post_votes WHERE post_id IN (SELECT id FROM posts WHERE user_id = ?) OR user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Post admin logok törlése (előbb, mint a post törlés!)
$stmt = mysqli_prepare($dbconn, "DELETE FROM post_admin_logs WHERE post_id IN (SELECT id FROM posts WHERE user_id = ?)");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

    // Postok törlése
    $stmt = mysqli_prepare($dbconn, "DELETE FROM posts WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Üzenetek törlése
    $stmt = mysqli_prepare($dbconn, "DELETE FROM messages WHERE sender_id = ? OR receiver_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Követések törlése
    $stmt = mysqli_prepare($dbconn, "DELETE FROM follows WHERE following_user_id = ? OR followed_user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Felhasználó törlése
    $stmt = mysqli_prepare($dbconn, "DELETE FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Tranzakció lezárása
    mysqli_commit($dbconn);

    header("Location: moderator.php?success=1");
    exit();

} catch (Exception $e) {
    mysqli_rollback($dbconn);
    die("Hiba történt a felhasználó törlése közben: " . $e->getMessage());
}
