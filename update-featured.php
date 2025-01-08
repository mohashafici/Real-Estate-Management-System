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
$is_featured = isset($data['is_featured']) ? (bool)$data['is_featured'] : false;

if ($property_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid property ID']);
    exit;
}

try {
    // Initialize database connection
    $database = new Database();
    $db = $database->getConnection();

    // Update featured status
    $stmt = $db->prepare("UPDATE properties SET is_featured = :is_featured WHERE id = :id");
    $stmt->bindParam(':is_featured', $is_featured, PDO::PARAM_BOOL);
    $stmt->bindParam(':id', $property_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update featured status']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 