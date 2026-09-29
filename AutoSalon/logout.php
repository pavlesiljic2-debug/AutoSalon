<?php
session_start();

// Uništava sesiju
session_destroy();

// Briše sve sesijske varijable
$_SESSION = array();

// Preusmerava na početnu stranicu
header('Location: index.php');
exit();
?>
