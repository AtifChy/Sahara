<?php

require_once __DIR__ . '/../config/database.php';

// Start session if not already started
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

    // // Check if email is verified
    // if (!$result['email_verified']) {
    //     return ['error' => 'email_not_verified'];
    // }

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

/**
 * Generate CSRF token
 * 
 * @return string
 */
function generateCSRFToken()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * 
 * @param string $token Token to verify
 * @return bool
 */
function verifyCSRFToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
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

    // Generate verification token
    // $verificationToken = bin2hex(random_bytes(32));
    // $verificationExpires = date('Y-m-d H:i:s', strtotime('+24 hours'));

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

    // Send verification email
    // $emailSent = sendVerificationEmail($email, $name, $verificationToken);

    return [
        'success' => true,
        'user_id' => $userId,
        // 'verification_token' => $verificationToken,
        // 'email_sent' => $emailSent
    ];
}

function emailExists($email)
{
    $result = fetchOne(
        "SELECT id FROM users WHERE email = '$email'"
    );

    return $result !== null;
}

/**
 * Send verification email
 * 
 * @param string $email Recipient email
 * @param string $name Recipient name
 * @param string $token Verification token
 * @return bool Success status
 */
function sendVerificationEmail($email, $name, $token)
{
    $verificationUrl = "http://" . $_SERVER['HTTP_HOST'] . "/sahara/auth/verify-email.php?token=" . $token;

    $subject = "Verify Your Sahara Account";
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #2563eb; color: white; padding: 20px; text-align: center; }
            .content { background: #f9fafb; padding: 30px; }
            .button { display: inline-block; padding: 12px 30px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Welcome to Sahara!</h1>
            </div>
            <div class='content'>
                <p>Hi " . htmlspecialchars($name) . ",</p>
                <p>Thank you for registering with Sahara. Please verify your email address to activate your account.</p>
                <p style='text-align: center;'>
                    <a href='" . $verificationUrl . "' class='button'>Verify Email Address</a>
                </p>
                <p>Or copy and paste this link into your browser:</p>
                <p style='word-break: break-all; color: #2563eb;'>" . $verificationUrl . "</p>
                <p>This link will expire in 24 hours.</p>
                <p>If you didn't create this account, please ignore this email.</p>
            </div>
            <div class='footer'>
                <p>&copy; 2026 Sahara E-Commerce. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Sahara <noreply@sahara.com>" . "\r\n";

    // In development, log the verification URL instead of sending email
    if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
        error_log("Verification URL: " . $verificationUrl);
        return true;
    }

    return mail($email, $subject, $message, $headers);
}

/**
 * Verify email with token
 * 
 * @param string $token Verification token
 * @return array Result with 'success' (bool) and 'message'
 */
function verifyEmail($token)
{
    // Find user with this token
    $user = fetchOne(
        "SELECT id, email, verification_expires FROM users WHERE verification_token = ?",
        's',
        [$token]
    );

    if (!$user) {
        return ['success' => false, 'message' => 'Invalid verification token'];
    }

    // Check if token has expired
    if (strtotime($user['verification_expires']) < time()) {
        return ['success' => false, 'message' => 'Verification link has expired'];
    }

    // Mark email as verified
    $result = query(
        "UPDATE users SET email_verified = 1, verification_token = NULL, verification_expires = NULL WHERE id = ?",
        'i',
        [$user['id']]
    );

    if (!$result) {
        return ['success' => false, 'message' => 'Verification failed. Please try again.'];
    }

    return ['success' => true, 'message' => 'Email verified successfully!'];
}

/**
 * Resend verification email
 * 
 * @param string $email Email address
 * @return array Result with 'success' (bool) and 'message'
 */
function resendVerificationEmail($email)
{
    // Find user
    $user = fetchOne(
        "SELECT id, name, email_verified FROM users WHERE email = ?",
        's',
        [$email]
    );

    if (!$user) {
        return ['success' => false, 'message' => 'Email not found'];
    }

    if ($user['email_verified']) {
        return ['success' => false, 'message' => 'Email already verified'];
    }

    // Generate new token
    $verificationToken = bin2hex(random_bytes(32));
    $verificationExpires = date('Y-m-d H:i:s', strtotime('+24 hours'));

    // Update token in database
    $result = query(
        "UPDATE users SET verification_token = ?, verification_expires = ? WHERE id = ?",
        'ssi',
        [$verificationToken, $verificationExpires, $user['id']]
    );

    if (!$result) {
        return ['success' => false, 'message' => 'Failed to generate new verification link'];
    }

    // Send email
    $emailSent = sendVerificationEmail($email, $user['name'], $verificationToken);

    if (!$emailSent) {
        return ['success' => false, 'message' => 'Failed to send email'];
    }

    return ['success' => true, 'message' => 'Verification email sent!'];
}

function validatePhone($phone)
{
    // Remove all non-digit characters
    $digitsOnly = preg_replace('/\D/', '', $phone);

    // Check if it has between 10 and 15 digits (international format)
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
