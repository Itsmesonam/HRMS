<?php

require_once __DIR__ . "/config/database/db.php";

$email = "admin@gmail.com";
$new_password = "Admin@123";

$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

$sql = "UPDATE users SET password = ?, role = 'admin' WHERE email = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ss",
    $hashed_password,
    $email
);

if (mysqli_stmt_execute($stmt)) {
    echo "Admin password reset successfully.";
} else {
    echo "Error: " . mysqli_error($conn);
}

mysqli_stmt_close($stmt);
?>