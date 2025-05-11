<?php
require_once 'baza.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])) {
    $userId = $_POST['user_id'];

    $query = "DELETE FROM uporabniki WHERE id = $userId";

    if (mysqli_query($link, $query)) {
        http_response_code(204);
        exit();
    } else {
        http_response_code(500);
        exit();
    }
} else {
    http_response_code(400);
    exit();
}
