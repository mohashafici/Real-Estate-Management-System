<?php
require_once "config/database.php";

header('Content-Type: application/json');

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);
$property_id = isset($data['property_id']) ? (int)$data['property_id'] : 0;

// Assuming user is logged in with ID 1 (you should replace this with actual user session)
$user_id = 1;

if ($property_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid property ID']);
    exit;
}

try {
    // Initialize database connection
    $database = new Database();
    $db = $database->getConnection();

    // Delete the favorite
    $stmt = $db->prepare("DELETE FROM favorites WHERE user_id = :user_id AND property_id = :property_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':property_id', $property_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to remove favorite']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 