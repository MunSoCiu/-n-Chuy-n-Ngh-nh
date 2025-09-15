<?php
require_once '../../app/config/config.php';
session_start();
session_destroy();
header("Location: " . BASE_URL . "/index.php");
exit(); 