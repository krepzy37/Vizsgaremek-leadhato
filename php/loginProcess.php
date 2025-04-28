<?php 
//munkamenet indítása a bejelentkezett felhasználó részére
session_start();

//csatlakozás az adatbázishoz
include_once "connect.php";
//frontend oldalról érkező adatok
$writtenemail = mysqli_real_escape_string($dbconn, $_POST['email']);
//print_r($writtenemail); ellenőrzés 
$writtenpass = mysqli_real_escape_string($dbconn, $_POST['password']);
//print_r($writtenpass);

//e-mail cím ellenőrzése
$sql = "SELECT * FROM users WHERE email LIKE '{$writtenemail}'";
$result = mysqli_query($dbconn, $sql) or die(mysqli_error($dbconn));

if (mysqli_num_rows($result) != 1) {
    echo "Hibás e-mail címet adott meg!";
    return;
}

$row = mysqli_fetch_assoc($result);
$hash = $row['password_hash'];

if (!empty($writtenemail) && !empty($writtenpass)) {
    if (!password_verify($writtenpass, $hash)) {
        echo "Hibás bejelentkezési adatok.";
        return;
    }

    // Verifikáció ellenőrzése
    if ($row['verification_code'] !== NULL) {
        echo "A fiókod még nincs ellenőrizve. Kérjük, ellenőrizd az e-mail címedet! (Lehet a spam mappában maradt a levelünk.)";
        return;
    }

    $sql2 = mysqli_query($dbconn, "SELECT * FROM users WHERE email LIKE '{$writtenemail}' AND password_hash = '{$hash}'");
    if (mysqli_num_rows($sql2) > 0) {
        $row = mysqli_fetch_assoc($sql2);
        $status = 1;

      
        $sql2 = mysqli_query($dbconn, "UPDATE users SET status = '{$status}'
                WHERE id = {$row['id']}");
                if($sql2){
                    $_SESSION['id'] = $row['id'];
                    $_SESSION['user_role'] = $row['role'];
                    $_SESSION['last_activity'] = time(); // Utolsó aktivitás ideje

                    if (isset($_POST['remember_me'])) {
                        $token = bin2hex(random_bytes(16)); 
                        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days')); 
                
                        $sql = "INSERT INTO remember_me_tokens (user_id, token, expires_at) VALUES ({$row['id']}, '{$token}', '{$expiresAt}')";
                        mysqli_query($dbconn, $sql);
                
                        setcookie("remember_me", $token, time() + (86400 * 30), "/"); // 30 nap
                    }
                    echo "success";
                }      
    }else{
        echo "Helytelen jelszót adott meg!";
    }
}else{
    echo "Minden mezőt ki kell töltenie!";
}

?>