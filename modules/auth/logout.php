<?php
require_once '../modules/auth/Auth.php';
$auth = new Auth($pdo);
$auth->logout();
header("Location: ../modules/auth/login.php");
exit();
?>
