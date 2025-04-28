<?php
session_start();
include '../php/connect.php';
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
// Összes szavazat száma
$post_votes_count = $dbconn->query("SELECT COUNT(*) FROM post_votes")->fetch_array()[0];
$comment_votes_count = $dbconn->query("SELECT COUNT(*) FROM comment_votes")->fetch_array()[0];

// Legnépszerűbb posztok
$popular_posts = $dbconn->query("SELECT post_id, SUM(CASE WHEN vote_type = 'upvote' THEN 1 ELSE -1 END) as score FROM post_votes GROUP BY post_id ORDER BY score DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);

$one_year_ago = strtotime('-1 year');
$today = strtotime('today');

// Szavazatok adatai
$vote_weeks = [];
$current_week = $one_year_ago;

while ($current_week <= $today) {
    $vote_weeks[date('Y-W', $current_week)] = 0;
    $current_week = strtotime('+1 week', $current_week);
}

$one_year_ago_date = date('Y-m-d', $one_year_ago);

$vote_data_query = "SELECT DATE_FORMAT(created_at, '%Y-%u') as week, COUNT(*) as vote_count FROM post_votes WHERE created_at >= ? GROUP BY week";
$vote_data_stmt = $dbconn->prepare($vote_data_query);
$vote_data_stmt->bind_param("s", $one_year_ago_date);
$vote_data_stmt->execute();
$vote_data_result = $vote_data_stmt->get_result();

while ($row = $vote_data_result->fetch_assoc()) {
    $vote_weeks[date('Y-W', strtotime(date('Y-m-d', strtotime($row['week'] . '1'))))] = $row['vote_count'];
}

// Posztok adatai
$post_weeks = [];
$current_week = $one_year_ago;

while ($current_week <= $today) {
    $post_weeks[date('Y-W', $current_week)] = 0;
    $current_week = strtotime('+1 week', $current_week);
}

$post_data_query = "SELECT DATE_FORMAT(created_at, '%Y-%u') as week, COUNT(*) as post_count FROM posts WHERE created_at >= ? GROUP BY week";
$post_data_stmt = $dbconn->prepare($post_data_query);
$post_data_stmt->bind_param("s", $one_year_ago_date);
$post_data_stmt->execute();
$post_data_result = $post_data_stmt->get_result();

while ($row = $post_data_result->fetch_assoc()) {
    $post_weeks[date('Y-W', strtotime(date('Y-m-d', strtotime($row['week'] . '1'))))] = $row['post_count'];
}

// Kommentek adatai
$comment_weeks = [];
$current_week = $one_year_ago;

while ($current_week <= $today) {
    $comment_weeks[date('Y-W', $current_week)] = 0;
    $current_week = strtotime('+1 week', $current_week);
}

$comment_data_query = "SELECT DATE_FORMAT(created_at, '%Y-%u') as week, COUNT(*) as comment_count FROM comments WHERE created_at >= ? GROUP BY week";
$comment_data_stmt = $dbconn->prepare($comment_data_query);
$comment_data_stmt->bind_param("s", $one_year_ago_date);
$comment_data_stmt->execute();
$comment_data_result = $comment_data_stmt->get_result();

while ($row = $comment_data_result->fetch_assoc()) {
    $comment_weeks[date('Y-W', strtotime(date('Y-m-d', strtotime($row['week'] . '1'))))] = $row['comment_count'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Analitika</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
        }

        canvas {
    max-width: 100%;
    height: 100% !important;
}
    </style>
</head>
<body>
    <div class="container py-4">
        <h1 class="text-center mb-4">📊 Analitika</h1>
        <div class="text-center mb-4">
        <a href="moderator.php" class="btn btn-primary">Vissza a dashboard-ra</a>
    </div>
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card bg-dark text-white">
                    <div class="card-body">
                        <h5 class="card-title">Összes poszt szavazat</h5>
                        <p class="card-text fs-4"><?php echo $post_votes_count; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mt-3 mt-md-0">
                <div class="card bg-dark text-white">
                    <div class="card-body">
                        <h5 class="card-title">Összes komment szavazat</h5>
                        <p class="card-text fs-4"><?php echo $comment_votes_count; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-dark text-white">
            <div class="card-body">
            
                <h5 class="card-title">Heti aktivitás (elmúlt 1 év)</h5>
                <div style="height: 500px;">
                <canvas id="combinedChart" class="mt-3"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        var labels = <?php echo json_encode(array_keys($vote_weeks)); ?>;
        var voteData = <?php echo json_encode(array_values($vote_weeks)); ?>;
        var postData = <?php echo json_encode(array_values($post_weeks)); ?>;
        var commentData = <?php echo json_encode(array_values($comment_weeks)); ?>;

        new Chart(document.getElementById('combinedChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Heti szavazatok száma',
                        data: voteData,
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1
                    },
                    {
                        label: 'Heti posztok száma',
                        data: postData,
                        borderColor: 'rgb(54, 162, 235)',
                        tension: 0.1
                    },
                    {
                        label: 'Heti kommentek száma',
                        data: commentData,
                        borderColor: 'rgb(255, 99, 132)',
                        tension: 0.1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: 'white'
                        },
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Heti aktivitás (szavazatok, posztok, kommentek) - elmúlt 1 év',
                        color: 'white'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: 'white'
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        }
                    },
                    x: {
                        ticks: {
                            color: 'white'
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>