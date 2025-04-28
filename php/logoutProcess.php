<?php 
session_start();

if (isset($_SESSION['id'])) {
    include_once "connect.php";

    $id = $_SESSION['id'];
    $status = 0;

    $sql = mysqli_query($dbconn, "UPDATE users SET status = '$status' WHERE id = $id");

    if ($sql) {
        if (isset($_COOKIE['remember_me'])) {
            $token = $_COOKIE['remember_me'];
            $deleteTokenSql = "DELETE FROM remember_me_tokens WHERE token = '{$token}'";
            mysqli_query($dbconn, $deleteTokenSql);
          
            setcookie("remember_me", "", time() - 3600, "/"); 
        }

        session_unset();
        session_destroy();
        header("location: ../index.php"); 
        exit();
    }
} else {
    header("location: ../login.php"); 
    exit();
}
?>