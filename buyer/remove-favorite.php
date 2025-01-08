<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Check if user is a buyer
if ($_SESSION['role'] !== 'buyer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get JSON data
$json = file_get_contents('php://input');
$data = json_decode($json);

if (!$data || !isset($data->property_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

require_once "../config/database.php";

$database = new Database();
$db = $database->getConnection();

// Remove from favorites
$delete_query = "DELETE FROM favorites WHERE user_id = :user_id AND property_id = :property_id";
$delete_stmt = $db->prepare($delete_query);
$delete_stmt->bindParam(':user_id', $_SESSION['user_id']);
$delete_stmt->bindParam(':property_id', $data->property_id);

if ($delete_stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Property removed from favorites'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error removing property from favorites'
    ]);
} 