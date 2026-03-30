<?php
require_once 'config/helpers.php';
startSession();
if (isLoggedIn()) redirect('dashboard.php');
else redirect('login.php');
