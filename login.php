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
if (isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}
?>

<?php include_once "kisegitok/head.html"; ?>


<style>
        body {
            background-color: #121212;
            
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
            text-align: center;
            background-color: rgba(0, 0, 0, 0.5);
            padding: 20px;
            border-radius: 8px;
        }
        .user-info img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
        }
        .post-list {
            margin-top: 20px;
        }
        .card {
            background-color: #1e1e1e;
            border: none;
        }
        #posts{
            text-align: center;
        }
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
        }

        #status{
            font-weight: 600;
        }
        
    </style>
    


<body>
    <video autoplay muted loop id="myVideo">
        <source src="php/img/vecteezy_darkroom-background-with-a-platform-to-showcase-product_30964617.mp4" type="video/mp4">
    </video>
<?php include_once "kisegitok/nav.php"; ?>
    <div id="login" class="content">

        <div class="wrapper">
     
            <section class="form login">

                <h1>Bejelentkezés</h1>               
                <form action="#">
                    <div style="max-width: 300px; word-wrap:break-word" class="error-txt"></div>

                    <div class="field input">
                    <img class="formIMG" src="php/img/email.webp" alt="" style="width: 20px; height: 20px;">
                        <input id="felhasznaloNev" type="email" name="email" class="input loginBox" placeholder="E-mail cím">
                    </div>
                    <div class=" field input">
                    <img class="formIMG" src="php/img/lock.webp" alt="" style="width: 20px; height: 20px;">
                        <div class="input-container">
                            
                            <input id="pass" type="password" name="password" class="loginBox" placeholder="Jelszó">
                            <i class="fas fa-eye" id="togglePassword"></i>
                        </div>
                        
                    </div>
                    <label>
        <input class="mt-3" type="checkbox" name="remember_me"> Emlékezz rám
    </label>
                    <div class="field button" id="login-button">
                    <a class="gomb"><input class="gomb" type="submit" value="Bejelentkezés"></a>
                    </div>
                    
                </form>
                
                    <h4>Új vagy nálunk?</h4>
                    <a class="gomb" href="signup.php">Regisztráció</a>
                
            </section>
        </div>
    </div>
    
    <script src="script/pass-show-hide.js"></script>
    <script src="script/login.js"></script>
<?php include "kisegitok/end.html"?>
