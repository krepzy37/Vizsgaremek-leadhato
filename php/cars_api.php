<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once 'connect.php';  //Adatbázis kapcsolat

// CORS beállítások
function send_response($status_code, $message) {
    http_response_code($status_code);
    echo json_encode(array('message' => $message));
}

// GET: 
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['id'])) {
        // 1 autó lekérdezése
        $car_id = $_GET['id'];
        $query = "SELECT * FROM cars WHERE id = ?";
        $stmt = $dbconn->prepare($query);
        $stmt->bind_param('i', $car_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $car = $result->fetch_assoc();
            echo json_encode($car);
        } else {
            send_response(404, "Autó nem található");
        }
    } else {
        // Összes autó lekérdezése
        $query = "SELECT * FROM cars";
        $result = mysqli_query($dbconn, $query);
        
        if (mysqli_num_rows($result) > 0) {
            $cars = mysqli_fetch_all($result, MYSQLI_ASSOC);
            echo json_encode($cars);
        } else {
            send_response(404, "Az autók nem találhatóak");
        }
    }
}

// POST: 
elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // JSON dekódolása a bemeneti streamből
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    // Ellenőrizzük, hogy a dekódolás sikeres volt és a kötelező mezők léteznek
    if ($data !== null && isset($data['name']) && isset($data['brand_id'])) {
        $name = $data['name'];
        $brand_id = $data['brand_id'];
        $bg_image_url = isset($data['bg_image_url']) ? $data['bg_image_url'] : 'bg-def.png';

        $query = "INSERT INTO cars (name, brand_id, bg_image_url) VALUES (?, ?, ?)";
        $stmt = $dbconn->prepare($query);
        $stmt->bind_param('sis', $name, $brand_id, $bg_image_url);

        if ($stmt->execute()) {
            send_response(201, "Autó sikeresen hozzáadva!");
        } else {
            send_response(500, "Hiba autó hozzáadása közben: " . $stmt->error);
        }
    } else {
        send_response(400, "Hiányzó vagy érvénytelen kötelező mezők");
    }
}

// PUT: 
elseif ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    // JSON dekódolása a bemeneti streamből
    $json_data = file_get_contents('php://input');
    $put_data = json_decode($json_data, true);

    // Ellenőrizzük, hogy a dekódolás sikeres volt és a kötelező mezők léteznek
    if ($put_data !== null && isset($put_data['id']) && isset($put_data['name']) && isset($put_data['brand_id'])) {
        $id = $put_data['id'];
        $name = $put_data['name'];
        $brand_id = $put_data['brand_id'];
        $bg_image_url = isset($put_data['bg_image_url']) ? $put_data['bg_image_url'] : 'bg-def.png';

        $query = "UPDATE cars SET name = ?, brand_id = ?, bg_image_url = ? WHERE id = ?";
        $stmt = $dbconn->prepare($query);
        $stmt->bind_param('sisi', $name, $brand_id, $bg_image_url, $id);

        if ($stmt->execute()) {
            send_response(200, "Autó sikeresen frissítve!");
        } else {
            send_response(500, "Hiba autó frissítése közben: " . $stmt->error);
        }
    } else {
        send_response(400, "Hiányzó vagy érvénytelen kötelező mezők");
    }
}
// DELETE:
elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    // JSON dekódolása a bemeneti streamből
    $json_data = file_get_contents('php://input');
    $delete_data = json_decode($json_data, true);

    if ($delete_data !== null && isset($delete_data['id'])) {
        $id = $delete_data['id'];
        $query = "DELETE FROM cars WHERE id = ?";
        $stmt = $dbconn->prepare($query);
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            send_response(200, "Autó sikeresen törölve!");
        } else {
            send_response(500, "Hiba az autó törlése közben: " . $stmt->error);
        }
    } else {
        send_response(400, "Hiányzó vagy érvénytelen autó azonosító");
    }
}
?>
