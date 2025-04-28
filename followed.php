<?php

if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];

    $follow_query = "SELECT followed_user_id FROM follows WHERE following_user_id = ?";
    $follow_stmt = $dbconn->prepare($follow_query);
    $follow_stmt->bind_param("i", $user_id);
    $follow_stmt->execute();
    $follow_result = $follow_stmt->get_result();
    $followed_user_ids = array();
    while ($follow_row = $follow_result->fetch_assoc()) {
        $followed_user_ids[] = $follow_row['followed_user_id'];
    }
    $follow_stmt->close();

    if (!empty($followed_user_ids)) {
        $post_query = "
            SELECT posts.*, 
                users.username, 
                users.profile_picture_url, 
                users.role,
                posts.created_at,
                COALESCE(vote_counts.vote_count, 0) AS vote_count,
                cars.name AS car_name,
                brands.name AS brand_name
            FROM posts 
            JOIN users ON posts.user_id = users.id 
            LEFT JOIN (
                SELECT post_id, 
                    SUM(CASE WHEN vote_type = 'upvote' THEN 1 ELSE -1 END) AS vote_count
                FROM post_votes
                GROUP BY post_id
            ) AS vote_counts ON posts.id = vote_counts.post_id
            LEFT JOIN cars ON posts.car_id = cars.id
            LEFT JOIN brands ON cars.brand_id = brands.id
            WHERE posts.user_id IN (" . implode(',', $followed_user_ids) . ")
            ORDER BY posts.created_at DESC
        ";
    } else {
        $post_query = "SELECT * FROM posts WHERE 1 = 0";  
    }
    $post_stmt = $dbconn->prepare($post_query);
    $post_stmt->execute();
    $post_result = $post_stmt->get_result();
    $post_stmt->close();

    ECHO ' <div class="mb-5 text-center text-white">
            <h2>
                Követett felhasználók posztjai
            </h2>
        </div>';

    if ($post_result->num_rows > 0) {
        while ($post = $post_result->fetch_assoc()) {
            $profilePic = !empty($post['profile_picture_url']) ? 'php/img/' . htmlspecialchars($post['profile_picture_url']) : 'php/img/default.png';
            $postImage = !empty($post['post_image_url']) ? 'php/img/' . htmlspecialchars($post['post_image_url']) : '';
            echo '<div class="col-12 col-md-10 col-lg-5 mx-auto mb-4 ">';
            echo "<div class='card text-light p-3' style='background-color: rgba(34, 34, 34, 0.95); border-radius: 10px;'>";
            echo "<div class='card-body'>";
            echo "<div class='d-flex align-items-center gap-2'>";
            echo "<img src='$profilePic' alt='Profilkép' class='rounded-circle mb-2' style='width: 40px; height: 40px; object-fit: cover;'>";
            $roleTag = ($post['role'] == 2) ? " <span class='text-warning'><i class='fa-solid fa-wrench'></i></span>" : "";

            $communityInfo = "";
            if (!empty($post['car_name']) && !empty($post['brand_name'])) {
                $communityInfo = " <span class='text-muted'>(" . htmlspecialchars($post['brand_name']) . " " . htmlspecialchars($post['car_name']) . ")</span>";
            }

            echo "<a id='profileLink' href='profile.php?user_id=" . htmlspecialchars($post['user_id']) . "'><h5 class='card-title'>" . htmlspecialchars($post['username']) . $roleTag . $communityInfo . "</h5></a>";
            echo "</div>";

            echo "<h4 class='card-subtitle mb-2'>" . htmlspecialchars($post['title']) . "</h4>";
            echo "<p class='card-text'>" . htmlspecialchars($post['body']) . "</p>";

            // Ha van kép, akkor megjelenítjük
            if ($postImage) {
                echo "<img src='$postImage' alt='Poszt Kép' class='img-fluid rounded d-block fullscreen-image mx-auto' >";
            }

            // Szavazatok
            $vote_query = "SELECT SUM(CASE WHEN vote_type = 'upvote' THEN 1 ELSE -1 END) as score FROM post_votes WHERE post_id = ?";
            $stmt = $dbconn->prepare($vote_query);
            $stmt->bind_param("i", $post['id']);
            $stmt->execute();
            $vote_result = $stmt->get_result()->fetch_assoc();
            $score = $vote_result['score'] !== null ? $vote_result['score'] : 0;

            echo "<div class='vote-buttons mt-2 d-flex align-items-center gap-2'>
                <button class='upvote btn btn-outline-success' data-id='" . $post['id'] . "' data-type='post'>⬆</button>
                <span id='post-score-" . $post['id'] . "'>" . $score . "</span>
                <button class='downvote btn btn-outline-danger' data-id='" . $post['id'] . "' data-type='post'>⬇</button>";

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

            $createdAt = date("Y. m. d. H:i", strtotime($post['created_at']));
            echo "<p class='mt-2 text-end card-text text-muted'><em style='font-size: 0.8rem;'> $createdAt</em></p>";

            
            echo "</div></div>";

            // Hozzászólások szekció
            echo "<div class='comments' id='comments-$post_id' style='display:none;'>";
            echo '<div style="background-color: rgba(34, 34, 34, 0.50)" class="card shadow-sm p-4 text-light mt-4 ">
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
                    
                    <button type="submit" class="btn btn-primary w-100">Hozzászólás hozzáadása</button>
                </form>
            </div>';
            while ($comment = mysqli_fetch_assoc($comment_result)) {
                $roleTag = ($comment['role'] == 2) ? " <span class='text-warning'><i class='fa-solid fa-wrench'></i></span>" : "";
                $commentPfp = htmlspecialchars($comment['profile_picture_url']);

                echo "<div style='background-color: rgba(34, 34, 34, 0.50)' class='card mb-3 mt-3 text-light'>
                    <div class='card-body'>
                        <div class='d-flex align-items-center mb-2'>
                            <img src='php/img/$commentPfp' alt='Profilkép' class='rounded-circle' style='width: 35px; height: 35px; object-fit: cover;'>
                            
                            <strong class='ms-2'>" . htmlspecialchars($comment['username']) . $roleTag . "</strong>
                        </div>
                        <p class='card-text'>" . htmlspecialchars($comment['body']) . "</p>";

                // Ha van kommenthez tartozó kép
                if (!empty($comment['comment_image_url'])) {
                    $commentImage = 'php/img/' . htmlspecialchars($comment['comment_image_url']);
                    echo "<div class='mb-2'>
                        <img src='$commentImage' alt='Komment Kép' class='img-fluid fullscreen-image' style='max-width: 50%; height: auto; '>
                    </div>";
                }

                $commentCreatedAt = date("Y. m. d. H:i", strtotime($comment['created_at']));
                echo "<p class='d-flex justify-content-end card-text text-muted'><em style='margin-top: -45px;'> $commentCreatedAt</em></p>";
                
                $comment_vote_query = "SELECT SUM(CASE WHEN vote_type = 'upvote' THEN 1 ELSE -1 END) as score FROM comment_votes WHERE comment_id = ?";
                $comment_stmt = $dbconn->prepare($comment_vote_query);
                $comment_stmt->bind_param("i", $comment['id']);
                $comment_stmt->execute();
                $comment_vote_result = $comment_stmt->get_result()->fetch_assoc();
                $comment_score = $comment_vote_result['score'] !== null ? $comment_vote_result['score'] : 0;

                echo "<div class='vote-buttons mt-2 mb-2 d-flex align-items-center gap-2'>
                    <button class='upvote btn btn-outline-success' data-id='" . $comment['id'] . "' data-type='comment'>⬆</button>
                    <span id='comment-score-" . $comment['id'] . "'>" . $comment_score . "</span>
                    <button class='downvote btn btn-outline-danger' data-id='" . $comment['id'] . "' data-type='comment'>⬇</button>
                </div> </div>
            </div>";

                if (isset($_SESSION['id'])) {
                    // Ha a belépett user a komment írója, megjelenik a szerkesztés gomb
                    if ($_SESSION['id'] == $comment['user_id']) {
                        echo "<button class='btn btn-outline-warning edit-comment-btn' data-id='" . $comment['id'] . "' data-text='" . htmlspecialchars($comment['body']) . "' data-image='" . $comment['comment_image_url'] . "'>Szerkesztés</button>";
                    }
                    if (isset($_SESSION['user_role'])) {
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
                }
            }
            echo "</div>";
            echo "</div>";
        }
    } else {
        echo "<div style='margin:200px'>";
        echo "<p class='col-12 col-md-10 col-lg-8 m-auto mb-5 alert alert-info';'>Nincsenek követett felhasználók.</p>";
        echo "</div>";
    }
}
?>
<script src="script/comments.js"></script>
<script src="script/edit-comment-vote.js"></script>