<?php
require_once 'connect.php'; // A kapcsolat létrehozásához

// Ellenőrizzük, hogy az id paraméter be van-e állítva
if (isset($_GET['id'])) {
    $carId = $_GET['id'];

    // SQL lekérdezés egy adott autó lekérdezésére
    $query = "SELECT b.id AS brand_id, b.name AS brand_name, c.id, c.name AS model_name, b.logo_url AS logo_url
              FROM brands b
              LEFT JOIN cars c ON b.id = c.brand_id
              WHERE c.id = ?";

    $stmt = mysqli_prepare($dbconn, $query);
    mysqli_stmt_bind_param($stmt, "i", $carId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (!$result) {
        die("Hiba az adatbázis lekérdezésében: " . mysqli_error($dbconn));
    }

    $car = mysqli_fetch_assoc($result);

    if ($car) {
        // Ha az autó létezik, visszaadjuk JSON formátumban
        echo json_encode($car);
        exit;
    } else {
        // Ha az autó nem létezik, üres JSON tömböt adunk vissza
        echo json_encode([]);
        exit;
    }
}

// Ha az id paraméter nincs beállítva, lekérdezzük az összes márkát és modellt
$query = "SELECT b.id AS brand_id, b.name AS brand_name, c.id, c.name AS model_name, b.logo_url AS logo_url
          FROM brands b
          LEFT JOIN cars c ON b.id = c.brand_id";

$result = mysqli_query($dbconn, $query);

// Hiba kezelése
if (!$result) {
    die("Hiba az adatbázis lekérdezésében: " . mysqli_error($dbconn));
}

$brands = [];
while ($row = mysqli_fetch_assoc($result)) {
    $brandName = $row['brand_name'];
    $brandLogo_url = $row['logo_url'];
    // URL-kompatibilis azonosító létrehozása
    $brandId = strtolower(str_replace(['á', 'é', 'í', 'ó', 'ö', 'ő', 'ú', 'ü', 'ű', ' '], ['a', 'e', 'i', 'o', 'o', 'o', 'u', 'u', 'u', '-'], $brandName));

    if (!isset($brands[$brandName])) {
        $brands[$brandName] = ['name' => $brandName, 'brand_id' => $brandId, 'models' => [], 'logo_url' => $brandLogo_url];
    }
    if ($row['model_name']) {
        $brands[$brandName]['models'][] = ['name' => $row['model_name'], 'id' => $row['id']]; //hozzáadtam az id-t is
    }
}

$collator = collator_create('hu_HU'); // Magyar nyelvi beállítások
uksort($brands, function ($a, $b) use ($collator) {
    return collator_compare($collator, $a, $b);
});

// A modelleket betűrendbe rendezzük
foreach ($brands as &$brand) {
    usort($brand['models'], function ($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
}

// Az adatokat JSON formátumban visszaadjuk
echo json_encode(array_values($brands));
?>  