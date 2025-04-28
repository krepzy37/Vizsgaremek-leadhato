<?php 
session_start();

if (!isset($_SESSION['id'])) {
    header("location:index.php");
    exit;
}

include_once "php/connect.php";

// Bejelentkezett felhasználó azonosítója
$user_id = $_SESSION['id'];

// Felhasználói adatok lekérése
$sql = "SELECT id, username, email, profile_picture_url FROM users WHERE id = ?";
$stmt = mysqli_prepare($dbconn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$row = mysqli_fetch_assoc($result)) {
    echo "Felhasználó nem található!";
    exit;
}

$username = $row['username'];
$email = $row['email'];
$profile_picture = $row['profile_picture_url'];

?>
<?php include_once "kisegitok/head.html"; ?>


<style>
input[type="text"], input[type="email"], input[type="password"] {
    background-color: #1e1e1e;
    color: #fff;
    border: none;
    padding: 12px 15px;
    border-radius: 10px;
    width: 100%;
    font-size: 16px;
    box-shadow: 0 0 5px rgba(0,0,0,0.5);
    transition: all 0.3s ease;
    margin-top: 5px;
}
input[type="text"]:hover, input[type="email"]:hover, input[type="password"]:hover{
    
    box-shadow: 0 0 0 .15vw rgba(255, 47, 0, 0.265);
} 

input[type="text"]:focus,
input[type="email"]:focus,
input[type="password"]:focus {
    outline: none;
    box-shadow: 0 0 0 .15vw rgba(255, 47, 0, 0.7);
}

/* Label stílus */
label {
    color: #ddd;
    font-weight: 500;
    margin-bottom: 5px;
    display: block;
}

/* Gomb stílus */
.field.button input[type="submit"] {
    background-color: #000;
    color: #fff;
    padding: 12px 20px;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    cursor: pointer;
    font-weight: bold;
    transition: background 0.3s;
}

.field.button input[type="submit"]:hover {
    background-color: #222;
}


.password-requirements {
    background-color: #2b2b2b;
    border-radius: 10px;
    padding: 15px;
    margin-top: 10px;
    color: #ccc;
    font-size: 14px;
    line-height: 1.6;
    box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.4);
}

.password-requirements p {
    margin: 0 0 10px 0;
    font-weight: 500;
    color: #fff;
}

.password-requirements ul {
    padding-left: 20px;
    list-style: none;
}

.password-requirements li {
    position: relative;
    padding-left: 24px;
    margin-bottom: 8px;
}

.password-requirements li::before {
    content: "✔";
    position: absolute;
    left: 0;
    color: #4caf50;
    font-weight: bold;
}


.profile-picture-row {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-top: 10px;
    flex-wrap: wrap;
}

.profile-picture-label {
    color: #ddd;
    font-size: 16px;
    font-weight: 500;
}



.back-to-profile {
    text-align: center;
    margin-top: 20px;
}

.back-to-profile a {
    display: inline-block;
    padding: 10px 20px;
    background-color: #111;
    color: #fff;
    border-radius: 10px;
    text-decoration: none;
    font-weight: bold;
    box-shadow: 0 0 5px rgba(0,0,0,0.5);
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.back-to-profile a:hover {
    background-color: #222;
    transform: scale(1.05);
}

</style>
<body>

<div class="content">
    <div class="wrapper">
        <section class="form signup">
            <header><h1>Adatok módosítása</h1></header>
            <form action="process_update.php" method="POST" enctype="multipart/form-data" autocomplete="off" data-user-id="<?php echo $user_id; ?>">
                <div class="error-txt"></div>

                <!-- Felhasználói azonosító (rejtett mező) -->
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

                <div class="field input input-container"> 
                    <label style="padding-top: 5px" >Felhasználónév:</label>
                    <input type="text" name="username" class="input loginBox" value="<?php print($username); ?>" required>
                </div>

                <div class="field input">
                    <label style="padding-top: 5px">E-mail:</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>

                <div class="field input">
                    <label style="padding-top: 5px">Jelszó (ha nem szeretné megváltoztatni, hagyja üresen):</label>
                    <input type="password" name="password" placeholder="Új jelszó">
                    <div class="password-requirements">
                        <h4>Jelszókövetelmények:</h4>
                        <ul>
                            <li>Legalább 8 karakter hosszú legyen</li>
                            <li>Tartalmazzon legalább egy kisbetűt</li>
                            <li>Tartalmazzon legalább egy nagybetűt</li>
                            <li>Tartalmazzon legalább egy számot</li>
                            <li>Tartalmazzon legalább egy speciális karaktert (@$!%*?&)</li>
                        </ul>
                    </div>
                </div>


                
                <div class="field image">
                    <label><h3 style="padding-top: 7px" >Profil kép:</h3></label>
                    <div class="profile-picture-row">
                        <span class="profile-picture-label">A profilkép megváltoztatáshoz kattintson a mappára:</span>
                        <div class="custom-file-wrapper position-relative">
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
                                        <?php if (!empty($profile_picture)): ?>
                                            <h5>Jelenlegi kép: <img style="border-radius: 50%; width: 100px; height: 100px; object-fit: cover; object-position: center; margin-top: 10px" src="php/img/<?php echo $profile_picture; ?>" width="100"></h5>
                                        <?php endif; ?>

                <div class="field button">
                    <input type="submit" value="Módosítások mentése">
                </div>
            </form>
            
            <div class="back-to-profile">
                <p>Meggondolta magát?</p>
                <a href="profile.php?user_id=<?php echo $user_id; ?>">Vissza a profilra</a>
            </div>
        </section>
    </div>
</div>

<script src="script/pass-show-hide.js"></script>
<script src="script/update.js"></script>
</body>
</html>
