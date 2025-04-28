<?php
session_start();
require "../php/connect.php";
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
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $brand_id = $_POST['brand_id'];
    $bg_image_url = "bg-def.png"; // Alapértelmezett kép

    // Ha feltöltöttek képet
    if (!empty($_FILES["bg_image"]["name"])) {
        $target_dir = "../php/img/";
        $image_name = time() . "_" . basename($_FILES["bg_image"]["name"]);
        $target_file = $target_dir . $image_name;
        
        if (move_uploaded_file($_FILES["bg_image"]["tmp_name"], $target_file)) {
            $bg_image_url = $image_name; // Mentjük az adatbázisba
        }
    }

    // Adatok beszúrása
    $stmt = $dbconn->prepare("INSERT INTO cars (name, brand_id, bg_image_url) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $name, $brand_id, $bg_image_url);
    if ($stmt->execute()) {
        echo "Autó sikeresen hozzáadva!";
        echo "<a href='moderator.php'>vissza</a> ";
    } else {
        echo "Hiba történt: " . $dbconn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>car added</title>
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
        }
    </style>
    <link rel="shortcut icon" href="../php/img/logoCT.png" type="image/x-icon">
</head>
<body>
    
</body>
</html>
