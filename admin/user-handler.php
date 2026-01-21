<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!isLoggedIn() || $_SESSION['user_role'] !== 'ADMIN') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$action = $_POST['action'] ?? '';
$userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

if (!$userId || !$action) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

if ($userId == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'You cannot modify your own account']);
    exit;
}

switch ($action) {
    case 'change_role':
        $newRole = $_POST['role'] ?? '';
        $validRoles = ['CUSTOMER', 'SELLER', 'ADMIN'];

        if (!in_array($newRole, $validRoles)) {
            echo json_encode(['success' => false, 'message' => 'Invalid role']);
            exit;
        }

        $result = query("UPDATE users SET role = '$newRole', updated_at = NOW() WHERE id = $userId");

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'User role updated successfully',
                'role' => $newRole
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update role']);
        }
        break;

    case 'toggle_status':
        $newStatus = isset($_POST['status']) ? (int)$_POST['status'] : 0;

        $result = query("UPDATE users SET is_active = $newStatus, updated_at = NOW() WHERE id = $userId");

        if ($result) {
            $statusText = $newStatus ? 'activated' : 'deactivated';
            echo json_encode([
                'success' => true,
                'message' => "User $statusText successfully",
                'status' => $newStatus
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update status']);
        }
        break;

    case 'delete_user':
        $result = query("DELETE FROM users WHERE id = $userId");

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
