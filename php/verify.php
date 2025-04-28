<?php
include_once "connect.php";

if (isset($_GET['code'])) {
    $verification_code = mysqli_real_escape_string($dbconn, $_GET['code']);
    $result = mysqli_query($dbconn, "SELECT * FROM users WHERE verification_code = '$verification_code'");

    if (mysqli_num_rows($result) == 1) {
        mysqli_query($dbconn, "UPDATE users SET verification_code = NULL WHERE verification_code = '$verification_code'");
        echo "
<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
<script>
Swal.fire({
    icon: 'success',
    title: 'Sikeres ellenőrzés!',
    text: 'Az e-mail címed sikeresen ellenőrizve lett! Most már bejelentkezhetsz.',
    confirmButtonColor: '#4CAF50',
    background: '#1e1e1e',
    color: '#fff'
});
</script>";
        header("Location: ../login.php");
    } else {
        echo "Invalid verification code.";
    }
} else {
    echo "Verification code not provided.";
}
?>