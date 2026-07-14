<?php
$conn = mysqli_connect("localhost", "root", "", "hrms");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>