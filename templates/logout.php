<?php 
session_unset();
header('Location: ' . $root . 'login?err=You have been successfully logged out');
?>
