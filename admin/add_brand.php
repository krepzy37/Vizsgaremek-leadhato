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

    // Ellenőrizzük, hogy létezik-e már ilyen márka
    $check_stmt = $dbconn->prepare("SELECT id FROM brands WHERE name = ?");
    $check_stmt->bind_param("s", $name);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        echo "Ez a márka már létezik!";
        exit;
    }

    $logo_url = NULL; // Ha nincs feltöltve kép, NULL marad az adatbázisban

    // Ha feltöltöttek logót
    if (!empty($_FILES["logo"]["name"])) {
        $target_dir = "../php/img/carlogos/"; // Képek mentése a logos mappába
        $image_name = time() . "_" . basename($_FILES["logo"]["name"]);
        $target_file = $target_dir . $image_name;

        if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
            $logo_url = $image_name; // Mentjük az adatbázisba
        }
    }

    // Adatok beszúrása
    $stmt = $dbconn->prepare("INSERT INTO brands (name, logo_url) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $logo_url);
    if ($stmt->execute()) {
        echo "Márka sikeresen hozzáadva!";
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
    <title>brand added</title>
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
