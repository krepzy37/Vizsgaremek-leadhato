<?php
session_start();
//include_once "php/cookies.php";
require 'php/connect.php'; // Adatbázis kapcsolat
if (isset($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];

    $sql = "SELECT user_id FROM remember_me_tokens WHERE token = '{$token}' AND expires_at > NOW()";
    $result = mysqli_query($dbconn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['id'] = $row['user_id']; 
    }
}
// Inicializáljuk a view_user_id-t null-ra
$view_user_id = null;

// Ellenőrizzük, hogy van-e user_id a GET paraméterek között
if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $view_user_id = $_GET['user_id'];
} elseif (isset($_SESSION['id'])) {
    // Ha be vagyunk jelentkezve, akkor a saját profilunkat mutatjuk
    $view_user_id = $_SESSION['id'];
}

// Ha nincs megadva user_id, akkor hibaüzenet
if ($view_user_id === null) {
    header("Location: index.php");
    exit();
}

// Lekérdezzük a megjelenítendő felhasználó adatait
$query = "SELECT id, username, profile_picture_url, status, role FROM users WHERE id = ?";
$stmt = $dbconn->prepare($query);
$stmt->bind_param("i", $view_user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "<p>Felhasználó nem található.</p>";
    exit();
}

/*  // Ellenőrzés - debug
var_dump($user);
var_dump($_SESSION);*/
// Biztosítsuk, hogy a status mindig létezik
$status = isset($user['status']) ? htmlspecialchars($user['status']) : "Nincs státusz megadva";

// Lekérdezzük a felhasználó posztjait
$query = "SELECT posts.id, posts.title, posts.user_id, posts.body, posts.post_image_url, posts.created_at, cars.name AS community_name, brands.logo_url, brands.name AS brand_name 
          FROM posts 
          JOIN cars ON posts.car_id = cars.id
          JOIN brands ON cars.brand_id = brands.id
          WHERE posts.user_id = ? AND posts.status = 1
          ORDER BY posts.created_at DESC";
$stmt = $dbconn->prepare($query);
$stmt->bind_param("i", $view_user_id);
$stmt->execute();
$result = $stmt->get_result();
$posts = $result->fetch_all(MYSQLI_ASSOC);

$user_score_query = "SELECT SUM(score) AS user_score FROM (
    SELECT SUM(CASE WHEN pv.vote_type = 'upvote' THEN 1 ELSE -1 END) AS score
    FROM post_votes pv
    JOIN posts p ON pv.post_id = p.id
    WHERE p.user_id = ? AND p.status = 1

    UNION ALL

    SELECT SUM(CASE WHEN cv.vote_type = 'upvote' THEN 1 ELSE -1 END) AS score
    FROM comment_votes cv
    JOIN comments c ON cv.comment_id = c.id
    WHERE c.user_id = ? AND c.status = 1
) AS combined_scores";

$user_score_stmt = $dbconn->prepare($user_score_query);
$user_score_stmt->bind_param("ii", $view_user_id, $view_user_id); // Kétszer kell megadni a user_id-t
$user_score_stmt->execute();
$user_score_result = $user_score_stmt->get_result()->fetch_assoc();
$user_score = $user_score_result['user_score'] !== null ? $user_score_result['user_score'] : 0;


$is_followed_by_user = false;
$is_following_user = false;

if (isset($_SESSION['id']) && $_SESSION['id'] !== $view_user_id) {
    // Ellenőrizzük, hogy a felhasználó követ-e minket
    $followed_by_query = "SELECT * FROM follows WHERE following_user_id = ? AND followed_user_id = ?";
    $followed_by_stmt = $dbconn->prepare($followed_by_query);
    $followed_by_stmt->bind_param("ii", $view_user_id, $_SESSION['id']);
    $followed_by_stmt->execute();
    $is_followed_by_user = $followed_by_stmt->get_result()->num_rows > 0;

    // Ellenőrizzük, hogy mi követjük-e a felhasználót
    $following_query = "SELECT * FROM follows WHERE following_user_id = ? AND followed_user_id = ?";
    $following_stmt = $dbconn->prepare($following_query);
    $following_stmt->bind_param("ii", $_SESSION['id'], $view_user_id);
    $following_stmt->execute();
    $is_following_user = $following_stmt->get_result()->num_rows > 0;
}

$followers_count_query = "SELECT COUNT(*) FROM follows WHERE followed_user_id = ?";
$followers_count_stmt = $dbconn->prepare($followers_count_query);
$followers_count_stmt->bind_param("i", $view_user_id);
$followers_count_stmt->execute();
$followers_count_result = $followers_count_stmt->get_result()->fetch_assoc();
$followers_count = $followers_count_result['COUNT(*)'];

$following_count_query = "SELECT COUNT(*) FROM follows WHERE following_user_id = ?";
$following_count_stmt = $dbconn->prepare($following_count_query);
$following_count_stmt->bind_param("i", $view_user_id);
$following_count_stmt->execute();
$following_count_result = $following_count_stmt->get_result()->fetch_assoc();
$following_count = $following_count_result['COUNT(*)'];

$post_count_query = "SELECT COUNT(*) AS post_count FROM posts WHERE user_id = ? && status = 1";
$post_count_stmt = $dbconn->prepare($post_count_query);
$post_count_stmt->bind_param("i", $view_user_id); // Feltételezve, hogy $view_user_id már definiálva van
$post_count_stmt->execute();
$post_count_result = $post_count_stmt->get_result();
$post_count_row = $post_count_result->fetch_assoc();
$post_count = $post_count_row['post_count'];
?>


<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['username']); ?> profilja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="shortcut icon" href="php/img/logoCT.png" type="image/x-icon">
</head>
<div class="main-container mt-5">
    <div class="main-content">
        <?php include 'kisegitok/nav.php'; ?>

        <style>
            body {
                background-color: #121212;

            }

            h1,
            h2 {
                color: white;
            }

            #myVideo {
                position: fixed;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                object-fit: cover;
                z-index: -1;
            }

            .user-info {

                background-color: rgba(5, 5, 5, 0.5);
                padding: 20px;
                border-radius: 8px;
            }

            .user-info img {
                width: 100px;
                height: 100px;
                border-radius: 50%;
                object-fit: cover;
                object-position: center;
            }

            .post-list {
                margin-top: 20px;
            }

            .card {
                background-color: #1e1e1e;
                border: none;
            }

            #posts {
                text-align: center;
                color: white !important;

            }

            @media (max-width: 768px) {
                .container {
                    padding: 10px;
                }
            }

            button[type="submit"] {
                background-color: #4CAF50;
                color: white;
            }

            button[type="submit"]:hover {
                background-color: #357a38;
                color: white;
            }

            .profile-picture {
                position: relative;
                display: inline-block;
                /* A pötty pozícionálásához kell */
            }

            .profile-status-dot {
                position: absolute;
                width: 20px;
                height: 20px;
                border-radius: 50%;
                bottom: 0px;
                left: 0px;
            }

            .profile-status-dot.online {
                background: radial-gradient(circle, limegreen, rgba(2, 48, 32, 0.9));
                border: 2px solid darkgreen;
            }

            .profile-status-dot.offline {
                background: radial-gradient(circle, red, rgba(139, 0, 0, 0.9));
                border: 2px solid darkred;
            }

            .btn-sm {
                padding: 0.2rem 0.5rem;
                font-size: 0.8rem;
            }

            #carLink {
                color: #4CAF50;
                text-decoration: none;
            }

            #carLink:hover {
                color: #357a38;
                text-decoration: white underline 1px;
            }
        </style>

        </head>

        <body>
            <video autoplay muted loop id="myVideo">
                <source src="php/img/vecteezy_darkroom-background-with-a-platform-to-showcase-product_30964617.mp4" type="video/mp4">
            </video>

            <div class="container mt-5">
                <div class="user-info">
                    <?php $roleTag = ($user['role'] == 2) ? " <span class='text-warning'><i class='fa-solid fa-wrench'></i></span>" : ""; ?>
                    <div class='profile-picture d-flex align-items-center gap-3 mb-2'>
                        <img src="php/img/<?php echo htmlspecialchars($user['profile_picture_url']); ?>" alt="Profilkép">
                        <div class="profile-status-dot <?php echo ($status != 0) ? 'online' : 'offline'; ?>"></div>
                        <h1><?php echo htmlspecialchars($user['username']) . $roleTag; ?></h1>
                    </div>


                    <?php if (isset($_SESSION['id']) && $_SESSION['id'] == $view_user_id): ?>
                        <a href="update.php" class="btn btn-outline-primary">Profil szerkesztése</a>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <h2 class="mb-2" style="font-size: 1.2rem;">Pontok: <?php echo $user_score; ?></h2>
                            <h2 class="mb-2" style="font-size: 1.2rem;">Posztok: <?php echo $post_count; ?></h2>
                        </div>
                        <div>
                            <div class="mb-2">
                                <button class="followers-link btn btn-outline-info" style="cursor: pointer; width:150px" data-user-id="<?php echo $view_user_id; ?>">Követők: <?php echo $followers_count; ?></button>
                                <br>
                                <hr>
                                <button class="following-link btn btn-outline-info" style="cursor: pointer; width:150px" data-user-id="<?php echo $view_user_id; ?>">Követettek: <?php echo $following_count; ?></button>

                            </div>
                            <?php if (isset($_SESSION['id']) && $_SESSION['id'] !== $view_user_id): ?>
                                <?php if ($is_followed_by_user && !$is_following_user): ?>
                                    <button class="btn btn-outline-success btn-sm follow-button" style="width: 150px;" data-user-id="<?php echo $view_user_id; ?>" data-following="false">
                                        Követés viszonzása
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-outline-success btn-sm follow-button" style="width: 150px;" data-user-id="<?php echo $view_user_id; ?>" data-following="<?php echo $is_following_user ? 'true' : 'false'; ?>">
                                        <?php echo $is_following_user ? 'Követem' : 'Követés'; ?>
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div id="followers-modal" class="modal" style="display: none;">
                <div class="modal-content">

                    <h3>Követők</h3>
                    <ul id="followers-list"></ul>
                </div>
            </div>

            <div id="following-modal" class="modal" style="display: none;">
                <div class="modal-content">

                    <h3>Követettek</h3>
                    <ul id="following-list"></ul>
                </div>
            </div>
            <script src="script/follow.js"></script>
            <script src="script/followers.js"></script>
            <div class="post-list col-lg-8 m-auto">

                <?php if (count($posts) > 0): ?>
                    <h2 id="posts">Posztok</h2>
                    <?php foreach ($posts as $post): ?>
                        <?php
                        $postImage = !empty($post['post_image_url']) ? 'php/img/' . htmlspecialchars($post['post_image_url']) : '';
                        ?>
                        <div class='card mb-4 col-lg-8  text-light' style='background-color: rgba(5, 5, 5, 0.7); border-radius: 10px; margin:auto;'>
                            <div class='card-body'>

                                <div class="mb-2 pb-2 pt-2" style="display: flex; align-items: center; border-radius: 8px; ">
                                    <img style="max-width: 40px; margin-right: 10px;" src="php/img/carlogos/<?php echo htmlspecialchars($post['logo_url']) ?>" alt="a">
                                    <a id="carLink" href="car.php?brand=<?php echo htmlspecialchars($post['brand_name']); ?>&model=<?php echo htmlspecialchars($post['community_name']); ?>">
                                        <h5><?php echo htmlspecialchars($post['brand_name']) . " " . htmlspecialchars($post['community_name']); ?></h5>
                                    </a>
                                </div>

                                <h3 class='card-subtitle mb-2'><?php echo htmlspecialchars($post['title']); ?></h3>
                                <p class='card-text'><?php echo nl2br(htmlspecialchars($post['body'])); ?></p>
                                <?php if ($postImage): ?>
                                    <img src='<?php echo $postImage; ?>' alt='Poszt Kép' class='img-fluid rounded d-block fullscreen-image mx-auto'>
                                <?php endif; ?>
                                <?php
                                $vote_query = "SELECT SUM(CASE WHEN vote_type = 'upvote' THEN 1 ELSE -1 END) as score FROM post_votes WHERE post_id = ?";
                                $stmt = $dbconn->prepare($vote_query);
                                $stmt->bind_param("i", $post['id']);
                                $stmt->execute();
                                $vote_result = $stmt->get_result()->fetch_assoc();
                                
                                // 0 ha nincs szavazat
                                $score = $vote_result['score'] !== null ? $vote_result['score'] : 0;
                                
                                $user_vote_query = "SELECT vote_type FROM post_votes WHERE user_id = ? AND post_id = ?";
                                $user_vote_stmt = $dbconn->prepare($user_vote_query);
                                $user_vote_stmt->bind_param("ii", $_SESSION['id'], $post['id']);
                                $user_vote_stmt->execute();
                                $user_vote_result = $user_vote_stmt->get_result();
                                $user_vote = $user_vote_result->fetch_assoc();
                                
                                $upvote_class = ($user_vote && $user_vote['vote_type'] === 'upvote') ? 'voted' : '';
                                $downvote_class = ($user_vote && $user_vote['vote_type'] === 'downvote') ? 'voted' : '';
                                // szavazat száma
                                echo "<div class='vote-buttons mt-2 d-flex align-items-center gap-2'>
                                    <button class='upvote btn btn-outline-success $upvote_class' data-id='" . $post['id'] . "' data-type='post'>⬆</button>
                                    <span id='post-score-" . $post['id'] . "'>" . $score . "</span>
                                    <button class='downvote btn btn-outline-danger $downvote_class' data-id='" . $post['id'] . "' data-type='post'>⬇</button>";
                                $post_id = $post['id'];
                                $comment_query = "SELECT comments.id, comments.body, comments.comment_image_url, users.profile_picture_url, users.username, users.role, comments.user_id, comments.created_at  
  FROM comments 
  JOIN users ON comments.user_id = users.id 
  WHERE comments.post_id = $post_id AND comments.status = 1";
                                $comment_result = mysqli_query($dbconn, $comment_query);
                                $comment_count = mysqli_num_rows($comment_result);
                                // Hozzászólások szekció
                                $buttonHtml = sprintf(
                                    '<button style="width:100px" type="button" class="toggle-comments btn btn-outline-secondary" data-post-id="%d"><span class="comment-count">%d</span> <i class="fa-solid fa-comment"></i></button>',
                                    $post_id,
                                    $comment_count
                                );
                                echo $buttonHtml;
                                echo "</div>";

                                $createdAt = date("Y m. d. H:i", strtotime($post['created_at']));
                    echo "<p class='mt-2 text-end'><em style='font-size: 0.8rem;'>$createdAt</em></p>";

                                if (isset($_SESSION['id'])) {
                                    // Ha a bejelentkezett felhasználó a poszt írója, megjelenik a szerkesztés gomb
                                    if ($_SESSION['id'] == $post['user_id']) {
                                        echo "<button class='btn btn-outline-warning edit-post-btn' data-id='" . $post['id'] . "' 
                                  data-title='" . htmlspecialchars($post['title']) . "' 
                                  data-body='" . htmlspecialchars($post['body']) . "' 
                                  data-image='" . $postImage . "'>Szerkesztés</button>";
                                    }

                                    // Ha a felhasználó a poszt írója VAGY moderátor, megjelenik a törlés gomb
                                    if ($_SESSION['id'] == $post['user_id'] || $_SESSION['user_role'] == 2) {
                                        $referer = urlencode($_SERVER['REQUEST_URI']); // Az aktuális oldal URL-je
                                        echo "<a href='php/delete-post.php?post_id=" . $post['id'] . "&redirect=" . $referer . "' 
   class='btn btn-outline-danger ms-2' 
   id='deletePostBtn'>Poszt törlése</a>

<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
<script>
document.getElementById('deletePostBtn').addEventListener('click', function(event) {
    event.preventDefault(); 
    const deleteUrl = this.href; 

    Swal.fire({
        title: 'Biztosan törlöd a posztot?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#4CAF50',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Igen, törlöm!',
        cancelButtonText: 'Mégse',
        background: '#1e1e1e',
        color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = deleteUrl; 
        }
    });
});
</script>";
                                    }
                                }


                                // Hozzászólások szekció
                                echo "<div>";


                                echo "<div class='comments' id='comments-$post_id' style='display:none;'>";
                                echo '<div class="card shadow-sm p-4 text-light mt-4 " style="background-color: rgba(5, 5, 5, 0.7);"">
          <h5 class="card-title mb-3">Hozzászólás hozzáadása</h5>
          <form class="comment-form" data-post-id=' . $post_id . ' enctype="multipart/form-data">
              <div class="mb-3">
                  <label for="comment_text" class="form-label">Hozzászólás:</label>
                  <textarea name="comment_text" class="form-control bg-secondary text-light border-0" rows="3" required></textarea>
              </div>
              
              <div class="mb-3">
                  <label for="comment_image" class="form-label">Kép csatolása:</label>
                  <input type="file" name="comment_image" class="form-control bg-secondary text-light border-0">
              </div>
              
              <button type="submit" class="btn w-100" >Hozzászólás hozzáadása</button>
          </form>
      </div>';
                                while ($comment = mysqli_fetch_assoc($comment_result)) {
                                    $roleTag = ($comment['role'] == 2) ? " <span class='text-warning'><i class='fa-solid fa-wrench'></i></span>" : "";
                                    $commentPfp = htmlspecialchars($comment['profile_picture_url']);

                                    echo "<div class='card mb-3 mt-3 text-light' style='background-color: rgba(5, 5, 5, 0.7);'>
            <div class='card-body'>
                <div class='d-flex align-items-center mb-2'>
                    <img src='php/img/$commentPfp' alt='Profilkép' class='rounded-circle' style='width: 35px; height: 35px;'>
                    
                    <strong class='ms-2'><a id='profileLink' href='profile.php?user_id=" . htmlspecialchars($comment['user_id']) . "'><h5 class='card-title'> " . " " . htmlspecialchars($comment['username']) . $roleTag . "</h5></a></strong>
                </div>
                <p class='card-text'>" . htmlspecialchars($comment['body']) . "</p>";

                                    // Ha van kommenthez tartozó kép
                                    if (!empty($comment['comment_image_url'])) {
                                        $commentImage = 'php/img/' . htmlspecialchars($comment['comment_image_url']);
                                        echo "<div class='mb-2'>
                              <img src='$commentImage' alt='Komment Kép'  class='img-fluid fullscreen-image' style='max-width: 50%; height: auto; '>
                          </div>";
                                    }

                                    $commentCreatedAt = date("Y. m. d. H:i", strtotime($comment['created_at']));
                                    echo "<p class='d-flex justify-content-end card-text text-muted'><em style='margin-top: -45px;'> $commentCreatedAt</em></p>
            ";

                                    // Szavazógombok a kommenthez

                                    $comment_vote_query = "SELECT SUM(CASE WHEN vote_type = 'upvote' THEN 1 ELSE -1 END) as score 
FROM comment_votes 
WHERE comment_id = ?";
$comment_stmt = $dbconn->prepare($comment_vote_query);
$comment_stmt->bind_param("i", $comment['id']); 
$comment_stmt->execute();
$comment_vote_result = $comment_stmt->get_result()->fetch_assoc();

$comment_score = $comment_vote_result['score'] !== null ? $comment_vote_result['score'] : 0;

// Felhasználó szavazatának lekérdezése
$user_comment_vote_query = "SELECT vote_type FROM comment_votes WHERE user_id = ? AND comment_id = ?";
$user_comment_vote_stmt = $dbconn->prepare($user_comment_vote_query);
$user_comment_vote_stmt->bind_param("ii", $_SESSION['id'], $comment['id']);
$user_comment_vote_stmt->execute();
$user_comment_vote_result = $user_comment_vote_stmt->get_result();
$user_comment_vote = $user_comment_vote_result->fetch_assoc();

$upvote_class = ($user_comment_vote && $user_comment_vote['vote_type'] === 'upvote') ? 'voted' : '';
$downvote_class = ($user_comment_vote && $user_comment_vote['vote_type'] === 'downvote') ? 'voted' : '';

echo "<div class='vote-buttons mt-2 mb-2 d-flex align-items-center gap-2'>
    <button class='upvote btn btn-outline-success $upvote_class' data-id='" . $comment['id'] . "' data-type='comment'>⬆</button>
    <span id='comment-score-" . $comment['id'] . "'>" . $comment_score . "</span>
    <button class='downvote btn btn-outline-danger $downvote_class' data-id='" . $comment['id'] . "' data-type='comment'>⬇</button>
</div>";
                                    if (isset($_SESSION['id'])) {
                                        // Ha a belépett user a komment írója, megjelenik a szerkesztés gomb
                                        if ($_SESSION['id'] == $comment['user_id']) {
                                            echo "<button class='btn btn-outline-warning edit-comment-btn' data-id='" . $comment['id'] . "' 
data-text='" . htmlspecialchars($comment['body']) . "' 
data-image='" . $comment['comment_image_url'] . "'>Szerkesztés</button>";
                                        }

                                        // Ha a belépett user a komment írója VAGY moderátor, megjelenik a törlés gomb
                                        if ($_SESSION['id'] == $comment['user_id'] || $_SESSION['user_role'] == 2) {
                                            $referer = urlencode($_SERVER['REQUEST_URI']); // Az aktuális oldal URL-je
                                            echo "<a class='btn btn-outline-danger ms-2' href='php/delete-comment.php?comment_id=" . $comment['id'] . "&redirect=" . $referer . "' 
   id='deleteCommentBtn'>Komment törlése</a>

<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
<script>
document.getElementById('deleteCommentBtn').addEventListener('click', function(event) {
    event.preventDefault(); 
    const deleteUrl = this.href; 

    Swal.fire({
        title: 'Biztosan törlöd a kommentet?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#4CAF50',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Igen, törlöm!',
        cancelButtonText: 'Mégse',
        background: '#1e1e1e',
        color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = deleteUrl; 
        }
    });
});
</script>";
                                        }
                                    }
                                    echo "</div> </div>";
                                }
                                echo "</div></div>";

                                ?>

                            </div>
                            <div id="editModal" class="modal">
                                <div class="modal-content">

                                    <h2>Poszt szerkesztése</h2>
                                    <form id="editPostForm" enctype="multipart/form-data">
                                        <input type="hidden" id="edit_post_id" name="post_id">

                                        <label for="edit_title">Cím:</label>
                                        <input type="text" id="edit_title" name="title" required>

                                        <label for="edit_body">Tartalom:</label>
                                        <textarea id="edit_body" name="body" required></textarea>

                                        <div class="mb-3 d-flex align-items-center">
                                            <label for="image" class="form-label me-2">Kép </label>

                                            <div class="custom-file-wrapper position-relative ">
                                                <label for="image" class="custom-file-label">
                                                    <section class="group">

                                                        <div class="file">
                                                            <div class="work work-5"></div>
                                                            <div class="work work-4"></div>
                                                            <div class="work work-3"></div>
                                                            <div class="work work-2"></div>
                                                            <div class="work work-1"></div>

                                                        </div>
                                                    </section>
                                                </label>
                                                <input type="file" name="image" id="image" class="custom-file-input">
                                            </div>
                                        </div>

                                        <img id="current_post_image" src="" style="display: none;">
                                        <label for="remove_image">Kép törlése:</label>
                                        <input type="checkbox" id="remove_image" name="remove_image">

                                        <button type="submit">Mentés</button>
                                    </form>
                                </div>
                            </div>

                            <script src="script/edit-post.js"></script>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="font-size:20px; margin: 80px auto; background-color: rgba(5, 5, 5, 0.7); padding:30px; border-radius:8px" id="posts">Nincs poszt.</p>
                <?php endif; ?>
            </div>
            <div id="editCommentModal" class="modal">
                <div class="modal-content">

                    <h2>Komment szerkesztése</h2>
                    <form id="editCommentForm" enctype="multipart/form-data">
                        <input type="hidden" id="edit_comment_id" name="comment_id">

                        <label for="edit_comment_text">Szöveg:</label>
                        <textarea id="edit_comment_text" name="comment_text" required></textarea>

                        <label for="edit_comment_image">Kép:</label>
                        <input type="file" name="comment_image" id="edit_comment_image">

                        <img id="current_comment_image" src="" style="max-width: 200px; display: none;">
                        <div id="delete_image_container" style="display: none;">
                            <input type="checkbox" id="delete_comment_image" name="delete_comment_image">
                            <label for="delete_comment_image">Jelenlegi kép törlése</label>
                        </div>

                        <button type="submit">Mentés</button>
                    </form>
                </div>
            </div>
    </div>
</div>
</main>
<script src="script/edit-comment-vote.js"></script>
<?php include 'kisegitok/footer.php' ?>
<!--<div class="custom-cursor"></div>
    <div class="cursor-follower"></div>
    <script src="./script/cursor.js"></script>-->
</div>
</div>
<script src="script/image-zoom.js"></script>
<script src="script/comments.js"></script>

</body>

</html>