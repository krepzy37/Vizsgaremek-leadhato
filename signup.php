<?php
// Munkamenet kezelése
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

$registration_error = "";

// Űrlap elküldésének kezelése
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ellenőrzi, hogy mindkét checkbox be van-e jelölve
    if (isset($_POST['aszf_elfogadva']) && isset($_POST['adatkezeles_elfogadva']) && $_POST['aszf_elfogadva'] == 'on' && $_POST['adatkezeles_elfogadva'] == 'on') {
        // További regisztrációs logika ide jöhet (pl. adatok validálása, adatbázisba mentés)
        // Ebben a példában csak egy sikeres üzenetet jelenítünk meg
        echo "<script>
Swal.fire({
    icon: 'success',
    title: 'Sikeres regisztráció!',
    text: 'Most már bejelentkezhetsz.',
    confirmButtonColor: '#4CAF50',
    background: '#1e1e1e',
    color: '#fff'
}).then(() => {
    window.location.href = 'login.php'; 
});
</script>";
        exit();
    } else {
        $registration_error = "Kérjük, fogadja el az Általános Szerződési Feltételeket és az Adatkezelési Tájékoztatót a regisztrációhoz.";
    }
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
</style>
<?php include_once "kisegitok/nav.php"; ?>
<video autoplay muted loop id="myVideo">
        <source src="php/img/vecteezy_darkroom-background-with-a-platform-to-showcase-product_30964617.mp4" type="video/mp4">
    </video>



    <div id="signup" class="content">
        <div class="wrapper">
            <section class="signup form">
                <h1>Regisztráció</h1>
                <form action="#" enctype="multipart/form-data" autocomplete="off">
                    <div class="error-box">
                        <div style="max-width: 300px; word-wrap:break-word" class="error-txt"></div>
                    </div>
                    <label>
                            <span class="icon">
                                <svg
                                    class="w-6 h-6 text-gray-800 dark:text-white formIMG"
                                    aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="30"
                                    height="30"
                                    fill="none"
                                    viewBox="0 0 24 24">
                                    <path
                                        stroke="currentColor"
                                        stroke-width="1.25"
                                        d="M7 17v1a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1a3 3 0 0 0-3-3h-4a3 3 0 0 0-3 3Zm8-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>
                                </svg>
                            </span>
                        </label>
                        
                    <div class="field input">
                        
                        <input id="felhasznaloNev"  type="text" placeholder="Felhasználónév" name="username" title="Amit itt ad meg, azon a néven fognak a későbbiekben hivatkozni magára!">
                    </div>
                    <img class="formIMG" src="php/img/email.webp" alt="" style="width: 20px; height: 20px;">
                    <div class="field input">
                        
                        <input id="felhasznaloNev" type="email" name="email" placeholder="E-mail cím" title="Valós emailcímet adjon meg! (Pl: valaki@pelda.com)">
                    </div>
                    <img class="formIMG" src="php/img/lock.webp" alt="" style="width: 20px; height: 20px;">
                    <div class="pass field input">
                        
                        <div class="input-container">
                            <input id="pass" type="password" name="password" placeholder="Jelszó"
                                title="A jelszónak legalább 8 karakterből kell állnia, tartalmaznia kell kis és nagy betűt,&#10; valamint egy speciális karaktert!">
                                <i class="fas fa-eye" id="togglePassword"></i>
                        </div>
                    </div>
                    <div class="mt-3 terms-and-conditions">
                            <label>
                                <input type="checkbox" name="aszf_elfogadva">
                                Elfogadom az <a style="color:#4CAF50" href="CTHU_ASZF.pdf" target="_blank">Általános Szerződési Feltételeket</a>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="adatkezeles_elfogadva">
                                Elfogadom az <a style="color:#4CAF50" href="CTHU_Adatkezeles.pdf" target="_blank">Adatkezelési Tájékoztatót</a>
                            </label>
                        </div>
                    <div class="field button" id="login-button">
                        <a class="gomb"><input class="gomb" type="submit" value="Regisztráció"></a>
                    </div>
                </form>
                <div class="link">
                    <h4>Van már fiókod?</h4>
                    <a class="gomb register" style="text-decoration: none; font-weight: bold;" href="login.php">Belépés</a>
                </div>
            </section>
        </div>
    </div>

<script src="script/pass-show-hide.js"></script>

<script src="script/signup.js"></script>

 <!-- <script src="script/signup_autofill.js"></script>-->
<?php include "kisegitok/end.html" ?>