<?php
session_start();
include "php/connect.php";
if (isset($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];

    $sql = "SELECT user_id FROM remember_me_tokens WHERE token = '{$token}' AND expires_at > NOW()";
    $result = mysqli_query($dbconn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['id'] = $row['user_id'];
    }
}

?>
<!DOCTYPE html>
<html lang="hu" ng-app="carApp">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Főoldal</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="shortcut icon" href="php/img/logoCT.png" type="image/x-icon">
</head>
<style>
     #myVideo {
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
        }
</style>
<body>
    <?php
    include "./kisegitok/nav.php";
    ?>
    <video autoplay muted loop id="myVideo">
        <source src="php/img/vecteezy_darkroom-background-with-a-platform-to-showcase-product_30964617.mp4" type="video/mp4">
    </video>

    <div class="post-list">
       
        <?php
        
        if (isset($_SESSION['id'])) {
           
            include "followed.php";
            echo '</div>';
        } else {
        ?>
            <div class="jumbotron mt-4 bg-dark text-white p-5 rounded" style="margin: auto auto 60px auto; max-width: 800px;">
                <h1 class="display-4">Üdvözöljük a Cartalk weboldalán!</h1>
                <p class="lead">A weboldal célja, hogy segítséget nyújtson az autó tulajdonosoknak.</p>
                <hr class="my-4">
                <p>Lehetősége van bejegyzéseket létrehozni, ahol a közösség tagjaitól segítséget kérhet az autójával kapcsolatos kérdéseire vagy éppen segítséget nyújthat a közösség másik tagjainak a problémájuk megoldásában. Igyekeztünk a weboldalt a lehető legfelhasználóbarátabbá tenni, csoportosítottuk a márkák alapján a modelleket a könnyebb megtalálás érdekében. Biztosítunk lehetőséget privát társalgásra egymás között, így nem szükséges publikusan beszélgetni.</p>
                <p>Amennyiben felmerülne kérdése, kérjük lépjen velünk kapcsolatba a megadott elérhetőségeinken keresztül.</p>
            </div>
        <?php
        }
        ?>
    </div>
    </div>
    <?php
    include "./kisegitok/footer.php";
    include "./kisegitok/end.html";
    ?>
</body>

</html>