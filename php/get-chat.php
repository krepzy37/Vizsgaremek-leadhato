<?php
session_start();
include_once "connect.php";

$outgoing_id = $_SESSION['id'];
$incoming_id = mysqli_real_escape_string($dbconn, $_POST['incoming_id']);

$output = "";

$sql = "SELECT messages.*, users.username, users.profile_picture_url FROM messages 
        LEFT JOIN users ON users.id = messages.sender_id
        WHERE (sender_id = {$outgoing_id} AND receiver_id = {$incoming_id})
        OR (sender_id = {$incoming_id} AND receiver_id = {$outgoing_id}) 
        ORDER BY messages.created_at";

$chat_query = mysqli_query($dbconn, $sql);

if (mysqli_num_rows($chat_query) > 0) {
    while ($row = mysqli_fetch_assoc($chat_query)) {
        
        $message_date = date("Y-m-d", strtotime($row['created_at']));
        $current_date = date("Y-m-d");

        if ($message_date === $current_date) {
            $formatted_time = "Ma " . date("H:i", strtotime($row['created_at']));
        } else {
            $formatted_time = date("H:i Y.m.d.", strtotime($row['created_at']));
        }

        if ($row['sender_id'] === $outgoing_id) {
            $output .= '<div class="chat outgoing">';
            $output .= '<div class="details">';
            if (!empty($row['dm_image_url'])) {
                $output .= '<img src="php/img/' . htmlspecialchars($row['dm_image_url']) . '" alt="Kép" class="dmKep" style="max-width: 250px; margin: 5px; border-radius:8px">';
            }
            if (!empty($row['content'])) {
                if(empty($row['dm_image_url'])){
                    $output .= '<p> ' . htmlspecialchars($row['content']) . '</p>';
                } else {
                    $output .= '<p>' . htmlspecialchars($row['content']) . '</p>';
                }
            }
            $output .= '<span style="font-size:12px" class="time">' . $formatted_time . '</span>';
            $output .= '</div></div>';
        }else {
            $output .= '<div class="chat incoming">';
            $output .= '<div class="profile-container_dm">'; // Profilkép tároló
            if (!empty($row['profile_picture_url'])) {
                $output .= '<img src="php/img/' . htmlspecialchars($row['profile_picture_url']) . '" alt="Profilkép" class="profile-picture_dm">';
            }
            $output .= '</div>';
            $output .= '<div class="details">';
            if (!empty($row['dm_image_url'])) {
                $output .= '<img src="php/img/' . htmlspecialchars($row['dm_image_url']) . '" alt="Kép" style="max-width: 250px; margin: 5px; border-radius:8px">';
            }
            if (!empty($row['content'])) {
                if(empty($row['dm_image_url'])){
                    $output .= '<p>' . htmlspecialchars($row['content']) . '</p>';
                } else {
                    $output .= '<p>' . htmlspecialchars($row['content']) . '</p>';
                }
            }
            $output .= '<span style="font-size:12px" class="time">' . $formatted_time . '</span>';
            $output .= '</div></div>';
        }
    }
    echo $output;
} else {
    echo "<p style='color:white'>Nincsenek üzenetek!</p>";
}
?>