<?php
// logout.php
require_once 'config/helpers.php';
startSession();
if (isLoggedIn()) logActivity('LOGOUT', 'User logout');
session_destroy();
header('Location: login.php');
exit;
