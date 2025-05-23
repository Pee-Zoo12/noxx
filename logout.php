<?php
require_once 'includes/init.php';

// Logout user
$user->logout();
 
// Redirect to home page
header('Location: index.php');
exit(); 