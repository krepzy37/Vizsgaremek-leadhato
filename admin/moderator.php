<?php
session_start();
require "../php/connect.php";
define('ACTION_ARCHIVED', 1);
define('ACTION_RESTORED', 2);

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

// Felhasználók lekérdezése
$user_query = "SELECT * FROM users";
$user_result = $dbconn->query($user_query);

// Archivált posztok lekérdezése
$archived_posts_query = "
SELECT 
    p.id, 
    p.title, 
    p.body, 
    p.post_image_url, 
    p.created_at, 
    u.username AS author, 
    m.username AS moderator, 
    al.created_at AS log_time,
    al.action
FROM 
    posts p
JOIN 
    post_admin_logs al ON p.id = al.post_id
JOIN 
    users u ON p.user_id = u.id
JOIN 
    users m ON al.admin_user_id = m.id
WHERE 
    p.status = 0 AND al.action = 1;
";
$archived_posts_result = $dbconn->query($archived_posts_query);

// Archivált kommentek lekérdezése
$archived_comments_query = "
SELECT 
    c.id, 
    c.body, 
    c.comment_image_url, 
    c.created_at, 
    u.username AS author, 
    m.username AS moderator, 
    al.created_at AS log_time,
    al.action
FROM 
    comments c
JOIN 
    comment_admin_logs al ON c.id = al.comment_id
JOIN 
    users u ON c.user_id = u.id
JOIN 
    users m ON al.admin_user_id = m.id
WHERE 
    c.status = 0 AND al.action = 1; -- 1 for archived
";
$archived_comments_result = $dbconn->query($archived_comments_query);

$restored_posts_query = "
SELECT 
    p.id, 
    p.title, 
    p.body, 
    p.post_image_url, 
    p.created_at, 
    u.username AS author, 
    m.username AS moderator, 
    al.created_at AS log_time,
    al.action
FROM 
    posts p
JOIN 
    post_admin_logs al ON p.id = al.post_id
JOIN 
    users u ON p.user_id = u.id
JOIN 
    users m ON al.admin_user_id = m.id
WHERE 
    p.status = 1 AND al.action = 2; -- 2 for restored
";
$restored_posts_result = $dbconn->query($restored_posts_query);

$query = "SELECT cars.id, cars.name AS car_name, brands.name AS brand_name, cars.bg_image_url 
          FROM cars 
          JOIN brands ON cars.brand_id = brands.id
          ORDER BY brands.name ASC";
          

$car_result = $dbconn->query($query);
?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderator Dashboard</title>
    <link rel="shortcut icon" href="../php/img/logoCT.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
        }
        td, th, table td:nth-child(2n){
            color: #ffffff !important;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1 class="mb-4">Moderator Dashboard</h1>
        <a href="../index.php">Vissza a főoldalra</a><br>
        <a href="analytics.php">Analitika</a> <br>

        <label for="tableSelector">Válaszd ki a megjelenítendő táblázatot:</label>
        <select id="tableSelector" class="form-select w-25 mb-4">
            <option value="users">Felhasználók</option>
            <option value="posts">Archivált Posztok</option>
            <option value="comments">Archivált Kommentek</option>
            <option value="carEdit">Autók szerkesztése</option>
            <option value="carAdd">Autó hozzáadása</option>
            <option value="brandAdd">Márka hozzáadása</option>
            <option value="restoredPosts">Visszaállított Posztok</option>
        </select>
        
        <div id="users" class="table-container">
        <!-- Felhasználók kezelése -->
        <h2>Felhasználók</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Felhasználónév</th>
                    <th>Email</th>
                    <th>Szerep</th>
                    <th>Létrehozva</th>
                    
                    <th>Profilkép</th>
                    <th>Műveletek</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $user_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td><?php echo date('Y-m-d H:i:s', strtotime($user['created_at'])); ?></td>
                        
                        <td><img style="max-width:50px" src="../php/img/<?php echo htmlspecialchars($user['profile_picture_url']) ?>" alt="<?php echo htmlspecialchars($user['username']); ?> profilképe"></td>
                        <td>
                            
                            <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Biztosan törölni akarod ezt a felhasználót?')">Törlés</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        </div>
        <div id="posts" class="table-container" style="display: none;">
        <!-- Archivált posztok -->
        <h2 class="mt-5">Archivált Posztok</h2>
        <table class="table table-striped">
        <thead>
    <tr>
        <th>ID</th>
        <th>Cím</th>
        <th>Tartalom</th>
        <th>Média</th>
        <th>Készítette</th>
        <th>Létrehozás dátuma</th>
        <th>Archiválta</th>
        <th>Archiválás dátuma</th>
        <th>Műveletek</th>
    </tr>
</thead>
<tbody>
    <?php while ($post = $archived_posts_result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $post['id']; ?></td>
            <td><?php echo htmlspecialchars($post['title']); ?></td>
            <td><?php echo htmlspecialchars($post['body']); ?></td>
            <td>
                <?php if (!empty($post['post_image_url'])): ?>
                    <img style="max-width:150px" src="../php/img/<?php echo htmlspecialchars($post['post_image_url']); ?>" alt="Média">
                <?php else: ?>
                    <span>Nincs kép</span>
                <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($post['author']); ?></td>
            <td><?php echo date('Y-m-d H:i:s', strtotime($post['created_at'])); ?></td>
            <td><?php echo htmlspecialchars($post['moderator']); ?></td>
            <td><?php echo date('Y-m-d H:i:s', strtotime($post['log_time'])); ?></td>
            <td>
                <a href="restore_post.php?id=<?php echo $post['id']; ?>" class="btn btn-success btn-sm"
                    onclick="return confirm('Biztosan visszaállítod ezt a posztot?')">
                    Visszaállítás
                </a>
            </td>
        </tr>
    <?php endwhile; ?>
</tbody>

        </table>
        </div>
        <div id="comments" class="table-container" style="display: none;">
        <!-- Archivált kommentek -->
        <h2 class="mt-5">Archivált Kommentek</h2>
        <table class="table table-striped">
        <thead>
    <tr>
        <th>ID</th>
        <th>Tartalom</th>
        <th>Média</th>
        <th>Készítette</th>
        <th>Létrehozás dátuma</th>
        <th>Archiválta</th>
        <th>Archiválás dátuma</th>
        <th>Műveletek</th>
    </tr>
</thead>
<tbody>
    <?php while ($comment = $archived_comments_result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $comment['id']; ?></td>
            <td><?php echo htmlspecialchars($comment['body']); ?></td>
            <td>
                <?php if (!empty($comment['comment_image_url'])): ?>
                    <img style="max-width:150px" src="../php/img/<?php echo htmlspecialchars($comment['comment_image_url']); ?>" alt="Média">
                <?php else: ?>
                    <span>Nincs kép</span>
                <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($comment['author']); ?></td>
            <td><?php echo date('Y-m-d H:i:s', strtotime($comment['created_at'])); ?></td>
            <td><?php echo htmlspecialchars($comment['moderator']); ?></td>
            <td><?php echo date('Y-m-d H:i:s', strtotime($comment['log_time'])); ?></td>
            <td>
                <a href="restore_comment.php?id=<?php echo $comment['id']; ?>" class="btn btn-success btn-sm"
                    onclick="return confirm('Biztosan visszaállítod ezt a kommentet?')">
                    Visszaállítás
                </a>
            </td>
        </tr>
    <?php endwhile; ?>
</tbody>

        </table>
        </div>

        <div id="carEdit" class="table-container" style="display: none">
    <h2>Autók szerkesztése</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Autó Neve</th>
                <th>Márka</th>
                <th>Háttérkép</th>
                <th>Műveletek</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $car_result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['car_name']) ?></td>
                <td><?= htmlspecialchars($row['brand_name']) ?></td>
                <td>
                    <img src="../php/img/<?= htmlspecialchars($row['bg_image_url']) ?>" width="100" alt="Car Image">
                </td>
                <td>
                    <a href="edit_car.php?id=<?= $row['id'] ?>">Módosítás</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    
</div>
<div id="restoredPosts" class="table-container" style="display: none;">
    <h2 class="mt-5">Visszaállított Posztok</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cím</th>
                <th>Tartalom</th>
                <th>Média</th>
                <th>Készítette</th>
                <th>Létrehozás dátuma</th>
                <th>Visszaállította</th>
                <th>Visszaállítás dátuma</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($post = $restored_posts_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $post['id']; ?></td>
                    <td><?php echo htmlspecialchars($post['title']); ?></td>
                    <td><?php echo htmlspecialchars($post['body']); ?></td>
                    <td>
                        <?php if (!empty($post['post_image_url'])): ?>
                            <img style="max-width:150px" src="../php/img/<?php echo htmlspecialchars($post['post_image_url']); ?>" alt="Média">
                        <?php else: ?>
                            <span>Nincs kép</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($post['author']); ?></td>
                    <td><?php echo date('Y-m-d H:i:s', strtotime($post['created_at'])); ?></td>
                    <td><?php echo htmlspecialchars($post['moderator']); ?></td>
                    <td><?php echo date('Y-m-d H:i:s', strtotime($post['log_time'])); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div id="carAdd" class="container table-container my-4" style="display: none;">
    <div class="card bg-dark text-white p-4">
        <h1 class="mb-4">Autó Hozzáadása</h1>
        <form action="add_car.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="name" class="form-label">Autó neve:</label>
                <input type="text" name="name" class="form-control bg-secondary text-white" required>
            </div>
            <div class="mb-3">
                <label for="brand_id" class="form-label">Márka:</label>
                <select name="brand_id" class="form-select bg-secondary text-white" required>
                    <?php
                    $brand_query = "SELECT id, name FROM brands";
                    $brand_result = $dbconn->query($brand_query);
                    while ($brand = $brand_result->fetch_assoc()) {
                        echo "<option value='{$brand['id']}'>{$brand['name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="bg_image" class="form-label">Háttérkép:</label>
                <input type="file" name="bg_image" class="form-control bg-secondary text-white" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary">Hozzáadás</button>
        </form>
    </div>
</div>

<div id="brandAdd" class="container table-container my-4" style="display: none;">
    <div class="card bg-dark text-white p-4">
        <h1 class="mb-4">Márka hozzáadása</h1>
        <form action="add_brand.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="name" class="form-label">Márka neve:</label>
                <input type="text" name="name" class="form-control bg-secondary text-white" required>
            </div>
            <div class="mb-3">
                <label for="logo" class="form-label">Logó:</label>
                <input type="file" name="logo" class="form-control bg-secondary text-white" accept="image/*">
            </div>
            <button type="submit" class="btn btn-success">Márka hozzáadása</button>
        </form>
    </div>
</div>
    </div>
    
    <script>
        document.getElementById("tableSelector").addEventListener("change", function() {
    let selectedTable = this.value;
    document.querySelectorAll(".table-container").forEach(table => {
        table.style.display = "none";
    });
    document.getElementById(selectedTable).style.display = "block";
});

 
    </script>
</body>

</html>