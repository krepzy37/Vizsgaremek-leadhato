<?php
session_start();
require "../php/connect.php";

if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}
if ($_SESSION['user_role'] == 1) {
    header("Location: access_denied.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autó frissítése</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="../php/img/logoCT.png" type="image/x-icon">
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
        }
        .container {
            margin-top: 80px;
        }
        .card {
            background-color: #1e1e1e;
            border: 1px solid #333;
        }
        .card a {
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card text-white p-4">
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int)$_POST['id'];
    $name = $_POST['name'];
    $brand_id = (int)$_POST['brand_id'];

    $image_url = NULL;
    if (!empty($_FILES["bg_image"]["name"])) {
        $target_dir = "../php/img/";
        $image_name = time() . "_" . basename($_FILES["bg_image"]["name"]);
        $target_file = $target_dir . $image_name;

        if (move_uploaded_file($_FILES["bg_image"]["tmp_name"], $target_file)) {
            $image_url = $image_name;
        }
    }

    if ($image_url) {
        $stmt = $dbconn->prepare("UPDATE cars SET name = ?, brand_id = ?, bg_image_url = ? WHERE id = ?");
        $stmt->bind_param("sisi", $name, $brand_id, $image_url, $id);
    } else {
        $stmt = $dbconn->prepare("UPDATE cars SET name = ?, brand_id = ? WHERE id = ?");
        $stmt->bind_param("sii", $name, $brand_id, $id);
    }

    if ($stmt->execute()) {
        echo "<div class='alert alert-success' role='alert'>Autó sikeresen frissítve!</div>";
        echo "<a href='moderator.php' class='btn btn-primary mt-3'>Vissza a moderátor oldalra</a>";
    } else {
        echo "<div class='alert alert-danger' role='alert'>Hiba történt: " . htmlspecialchars($dbconn->error) . "</div>";
    }
}
?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
