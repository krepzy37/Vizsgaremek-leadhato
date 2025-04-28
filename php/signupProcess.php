<?php
include_once "connect.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

$username = mysqli_real_escape_string($dbconn, $_POST['username']);
$email = mysqli_real_escape_string($dbconn, $_POST['email']);
$passwordrow = mysqli_real_escape_string($dbconn, $_POST['password']);
$password = password_hash($passwordrow, PASSWORD_DEFAULT);

if (!empty($username) && !empty($email) && !empty($password)) {
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $sql = mysqli_query($dbconn, "SELECT email FROM users WHERE email = '{$email}'");
        if (mysqli_num_rows($sql) > 0) {
            echo "$email - már létező e-mail cím!";
        } else {
            $role = 1;
            $created_at = date('Y-m-d H:i:s');
            $sql2 = mysqli_query($dbconn, "INSERT INTO users (username, email, password_hash, role, created_at) VALUES ('{$username}', '{$email}', '{$password}', '{$role}', '{$created_at}')");

            if ($sql2) {
                $verification_code = bin2hex(random_bytes(16));
                mysqli_query($dbconn, "UPDATE users SET verification_code = '{$verification_code}' WHERE email = '{$email}'");

                $mail = new PHPMailer(true);
                try {
                    $mail->SMTPDebug = SMTP::DEBUG_OFF;
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    /*Email helye */
                    /*App jelszó helye */
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port       = 465;
                    

                    $mail->setFrom(/*Email helye */, 'Car Talk HU');
                    $mail->addAddress($email, $username);
                    $mail->isHTML(true);
                    $mail->CharSet = 'UTF-8';
                    $mail->Subject = 'Indítsd be a fiókodat a Car Talk HU-n!';
                    $mail->Body = '
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f9f9f9;
      color: #333;
      padding: 20px;
    }
    .container {
      background-color: #ffffff;
      border-radius: 10px;
      padding: 30px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      max-width: 600px;
      margin: auto;
    }
    .logo {
      text-align: center;
      margin-bottom: 20px;
    }
    .button {
  display: inline-block;
  padding: 12px 24px;
  background-color:rgb(4, 146, 35);
  color: white !important;
  text-decoration: none;
  border-radius: 5px;
  font-weight: bold;
  font-size: 16px;
  transition: background-color 0.3s ease;
}

.button:hover {
  background-color:rgb(0, 70, 0);
}
    .footer {
      font-size: 0.9em;
      color: #777;
      margin-top: 30px;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="logo">
      <img src="https://imgur.com/bdk2h3K.png" alt="Car Talk Logo" width="120">
    </div>
    <h2>Kedves ' . htmlspecialchars($username) . '!</h2>
    <p>Köszönjük, hogy regisztráltál a <strong>Car Talk HU</strong> közösségi oldalunkra! Mielőtt használni tudnád fiókodat, kérjük, erősítsd meg az e-mail címedet az alábbi gombra kattintva:</p>
    <p style="text-align: center;">
      <a class="button" href="http://localhost/Vizsgaremek/php/verify.php?code=' . $verification_code . '">E-mail megerősítése</a>
    </p>
    <p>Ha nem te regisztráltál, kérlek, hagyd figyelmen kívül ezt az üzenetet.</p>
    <div class="footer">
      &copy; ' . date("Y") . ' Car Talk HU – Minden jog fenntartva.
    </div>
  </div>
</body>
</html>
';


                    $mail->send();
                } catch (Exception $e) {
                    echo "Üzenet elküldése sikertelen: {$mail->ErrorInfo}";
                }
                echo json_encode(['status' => 'success', 'message' => 'Sikeres regisztráció! Kérjük, ellenőrizd az e-mail címedet a fiókod aktiválásához.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Valami hiba történt!']);
            }
        }
    } else {
        echo "Érvénytelen e-mail cím!";
    }
} else {
    echo "Minden mezőt ki kell töltenie!";
}
?>