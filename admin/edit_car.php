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
if (!isset($_GET['id'])) {
    die("Nincs megadva autó ID!");
}

$id = (int)$_GET['id'];

$stmt = $dbconn->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$car = $result->fetch_assoc();

if (!$car) {
    die("Nincs ilyen autó!");
}

$brands_query = "SELECT id, name FROM brands ORDER BY name ASC";
$brands_result = $dbconn->query($brands_query);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autó szerkesztése</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="../php/img/logoCT.png" type="image/x-icon">
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
        }
        .form-label, .form-control, .form-select {
            color: #fff;
        }
        .form-control, .form-select {
            background-color: #1e1e1e;
            border-color: #333;
        }
        .form-control:focus, .form-select:focus {
            border-color: #666;
            box-shadow: none;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <h2 class="mb-4 text-center">Autó szerkesztése</h2>
        <form action="update_car.php" method="post" enctype="multipart/form-data" class="row g-3 needs-validation" novalidate>
            <input type="hidden" name="id" value="<?= $car['id'] ?>">

            <div class="col-12">
                <label for="name" class="form-label">Autó neve:</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($car['name']) ?>" required>
            </div>

            <div class="col-12">
                <label for="brand_id" class="form-label">Márka:</label>
                <select class="form-select" id="brand_id" name="brand_id" required>
                    <?php while ($brand = $brands_result->fetch_assoc()): ?>
                        <option value="<?= $brand['id'] ?>" <?= ($brand['id'] == $car['brand_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($brand['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-12">
                <label for="bg_image" class="form-label">Háttérkép módosítása:</label>
                <input type="file" class="form-control" id="bg_image" name="bg_image" accept="image/*">
            </div>

            <div class="col-12 text-center">
                <button type="submit" class="btn btn-primary px-5">Mentés</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
