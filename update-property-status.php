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
$status = isset($data['status']) ? $data['status'] : '';

if ($property_id <= 0 || !in_array($status, ['available', 'rented', 'sold'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid property ID or status']);
    exit;
}

try {
    // Initialize database connection
    $database = new Database();
    $db = $database->getConnection();

    // Update property status
    $stmt = $db->prepare("UPDATE properties SET status = :status WHERE id = :id");
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':id', $property_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update property status']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 