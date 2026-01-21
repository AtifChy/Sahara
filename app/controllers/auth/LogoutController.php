<?php
require_once __DIR__ . '/../../models/Auth.php';

logout();

header('Location: /auth/login.php');
exit;
