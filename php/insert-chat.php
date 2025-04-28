<?php
session_start();
include_once "connect.php";

$outgoing_id = $_SESSION['id'];
$incoming_id = mysqli_real_escape_string($dbconn, $_POST['incoming_id']);
$message = mysqli_real_escape_string($dbconn, $_POST['message']);

if (!empty($_FILES['image']['name'])) {
    $image_name = $_FILES['image']['name'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_ext = pathinfo($image_name, PATHINFO_EXTENSION); // Kiterjesztés lekérdezése
    $unique_name = uniqid("IMG-", true) . '.' . $image_ext; // Egyedi fájlnév generálása
    $image_folder = "img/" . $unique_name;

    if (move_uploaded_file($image_tmp_name, $image_folder)) {
        // Kép sikeresen feltöltve
        $sql = mysqli_query($dbconn, "INSERT INTO messages (sender_id, receiver_id, content, dm_image_url) VALUES ('{$outgoing_id}', '{$incoming_id}', '{$message}', '{$unique_name}')") or die(mysqli_error($dbconn));
        echo "Üzenet elküldve";
    } else {
        echo "Hiba történt a kép feltöltésekor.";
        exit();
    }
} else {
    // Szöveges üzenet
    if (!empty($message)) {
        $sql = mysqli_query($dbconn, "INSERT INTO messages (sender_id, receiver_id, content) VALUES ('{$outgoing_id}', '{$incoming_id}', '{$message}')") or die(mysqli_error($dbconn));
        echo "Üzenet elküldve";
    } else {
        echo "Az üzenet nem lehet üres.";
    }
}
?>