<?php
require_once('../config/database.php');
header('Content-Type: application/json');

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);
$response = ['success' => false];

if (isset($data['property_id'])) {
    $database = new Database();
    $conn = $database->getConnection();
    
    // For now, we'll use a fixed user_id (1) since we don't have authentication yet
    $user_id = 1;
    $property_id = $data['property_id'];
    
    try {
        // Check if already favorited
        $check_query = "SELECT id FROM favorites WHERE user_id = :user_id AND property_id = :property_id";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bindParam(':user_id', $user_id);
        $check_stmt->bindParam(':property_id', $property_id);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            // If exists, remove from favorites
            $delete_query = "DELETE FROM favorites WHERE user_id = :user_id AND property_id = :property_id";
            $stmt = $conn->prepare($delete_query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':property_id', $property_id);
            $stmt->execute();
            $response['success'] = true;
            $response['action'] = 'removed';
        } else {
            // If doesn't exist, add to favorites
            $insert_query = "INSERT INTO favorites (user_id, property_id) VALUES (:user_id, :property_id)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':property_id', $property_id);
            $stmt->execute();
            $response['success'] = true;
            $response['action'] = 'added';
        }
    } catch (PDOException $e) {
        $response['error'] = $e->getMessage();
    }
}

echo json_encode($response); 