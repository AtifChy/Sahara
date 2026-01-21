<?php
require_once __DIR__ . '/../../models/Auth.php';

logout();

header('Location: /auth.php?page=login');
exit;
