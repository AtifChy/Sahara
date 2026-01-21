<?php

require_once __DIR__ . '/../controllers/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function authenticateUser($email, $password)
{
    if (empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'Email and password are required'];
    }

    if (!validateEmail($email)) {
        return ['success' => false, 'message' => 'Invalid email format'];
    }

    // Query database for user
    $result = fetchOne(
        "SELECT
            u.id,
            u.email,
            u.password,
            u.role,
            u.is_active,
            p.first_name,
            p.last_name
        FROM users u
        LEFT JOIN user_profiles p ON u.id = p.user_id
        WHERE u.email = '$email'
        ",
    );

    if (!$result) {
        return ['success' => false, 'message' => 'User not found'];
    }

    if (!$result['is_active']) {
        return ['success' => false, 'message' => 'Account is deactivated'];
    }

    // Verify password
    if (!password_verify($password, $result['password'])) {
        return ['success' => false, 'message' => 'Incorrect password'];
    }

    // Update last login time
    query(
        "UPDATE users SET last_login = NOW() WHERE id = '{$result['id']}'",
    );

    // Remove password from returned data
    unset($result['password']);

    return ['success' => true, 'user' => $result];
}

function isLoggedIn()
{
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function getCurrentUser()
{
    if (!isLoggedIn()) {
        return null;
    }

    return [
        'id' => $_SESSION['user_id'] ?? null,
        'email' => $_SESSION['user_email'] ?? null,
        'fname' => $_SESSION['user_fname'] ?? null,
        'lname' => $_SESSION['user_lname'] ?? null,
        'role' => $_SESSION['user_role'] ?? 'customer'
    ];
}

function logout()
{
    // Clear session
    $_SESSION = [];

    // Destroy session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    // Clear remember token
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }

    // Destroy session
    session_destroy();
}

function requireAuth($redirectTo = '../login.php')
{
    if (!isLoggedIn()) {
        header('Location: ' . $redirectTo);
        exit;
    }
}

function requireRole($role, $redirectTo = '../index.php')
{
    $user = getCurrentUser();

    if (!$user || $user['role'] !== $role) {
        header('Location: ' . $redirectTo);
        exit;
    }
}

function sanitizeInput($input)
{
    return htmlspecialchars(trim($input));
}

function validateEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function registerUser($name, $email, $phone, $gender, $password, $newsletter = false)
{
    // Validate inputs
    if (empty($name) || empty($email) || empty($phone) || empty($gender) || empty($password)) {
        return ['success' => false, 'message' => 'All fields are required'];
    }

    $name = explode(' ', $name);
    if (count($name) >= 2) {
        $last_name = array_pop($name);
        $first_name = implode(' ', $name);
    } else {
        $first_name = $name[0];
        $last_name = '';
    }

    if (!validateEmail($email)) {
        return ['success' => false, 'message' => 'Invalid email address'];
    }

    if (!validatePhone($phone)) {
        return ['success' => false, 'message' => 'Invalid phone number'];
    }

    if (strlen($password) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters'];
    }

    if (emailExists($email)) {
        return ['success' => false, 'message' => 'Email already registered'];
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into database
    $result = query(
        "INSERT INTO users (email, password) VALUES ('$email', '$hashedPassword')"
    );

    if (!$result) {
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }

    $userId = lastInsertId();

    $result = query(
        "INSERT INTO user_profiles
        (user_id, first_name, last_name, phone, gender, newsletter)
        VALUES ('$userId', '$first_name', '$last_name', '$phone', '$gender', '$newsletter')"
    );

    if (!$result) {
        return ['success' => false, 'message' => 'Failed to create user profile. Please try again.'];
    }

    return [
        'success' => true,
        'user_id' => $userId,
    ];
}

function emailExists($email)
{
    $result = fetchOne(
        "SELECT id FROM users WHERE email = '$email'"
    );

    return $result !== null;
}

function validatePhone($phone)
{
    $digitsOnly = preg_replace('/\D/', '', $phone);
    return strlen($digitsOnly) >= 10 && strlen($digitsOnly) <= 15;
}

function createUserSession($user, $remember = false)
{
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_fname'] = $user['first_name'];
    $_SESSION['user_lname'] = $user['last_name'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['logged_in'] = true;

    // Set session expiration
    if ($remember) {
        // Remember for 30 days
        $_SESSION['remember'] = true;
        setcookie('remember_token', generateRememberToken($user['id']), time() + (30 * 24 * 60 * 60), '/');
    } else {
        // Session expires when browser closes
        $_SESSION['remember'] = false;
    }
}

function generateRememberToken($userId)
{
    // Generate secure random token
    $token = bin2hex(random_bytes(32));

    // Set expiration to 30 days from now
    $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

    // Store token in database
    query(
        "INSERT INTO remember_tokens
        (user_id, token, expires_at)
        VALUES ('$userId', '$token', '$expiresAt')"
    );

    return $token;
}

function validateRememberToken($token)
{
    // Query token from database
    $result = fetchOne(
        "SELECT user_id, expires_at FROM remember_tokens WHERE token = '$token'",
    );

    if (!$result) {
        return false;
    }

    // Check if token has expired
    if (strtotime($result['expires_at']) < time()) {
        // Delete expired token
        query("DELETE FROM remember_tokens WHERE token = '$token'");
        return false;
    }

    return $result['user_id'];
}

function checkRememberToken()
{
    if (isLoggedIn() || ! isset($_COOKIE['remember_token'])) {
        return false;
    }

    $token = $_COOKIE['remember_token'] ?? null;
    if (!$token) {
        return false;
    }

    $userId = validateRememberToken($token);
    if (!$userId) {
        setcookie('remember_token', '', time() - 3600, '/');
        return false;
    }

    $user = fetchOne(
        "SELECT
            u.id,
            u.email,
            u.role,
            u.is_active,
            p.first_name,
            p.last_name
        FROM users u
        LEFT JOIN user_profiles p ON u.id = p.user_id
        WHERE u.id = '$userId'"
    );

    if (!$user || !$user['is_active']) {
        query("DELETE FROM remember_tokens WHERE token = '$token'");
        setcookie('remember_token', '', time() - 3600, '/');
        return false;
    }

    createUserSession($user, true);
}
