<?php
require_once __DIR__ . '/../../backend/functions/helpers.php';

session_name('petstore_session');
session_start();
session_destroy();
header('Location: ' . url(''));
exit;
?>