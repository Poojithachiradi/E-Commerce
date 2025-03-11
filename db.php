<?php
$servername = "localhost";
$username = "root";
$password = "";
$dname = "ecommerce";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable error reporting

$conn = mysqli_connect($servername, $username, $password, $dname);

if (!$conn) {
    exit("Connection failed: " . mysqli_connect_error());
}
else
echo "successfully connected";
?>

