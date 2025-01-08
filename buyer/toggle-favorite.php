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

// Check if property exists in favorites
$check_query = "SELECT id FROM favorites WHERE user_id = :user_id AND property_id = :property_id";
$check_stmt = $db->prepare($check_query);
$check_stmt->bindParam(':user_id', $_SESSION['user_id']);
$check_stmt->bindParam(':property_id', $data->property_id);
$check_stmt->execute();

// Get current favorites from cookie
$favorites = isset($_COOKIE['favorites']) ? json_decode($_COOKIE['favorites'], true) : [];

if ($check_stmt->rowCount() > 0) {
    // Remove from favorites
    $delete_query = "DELETE FROM favorites WHERE user_id = :user_id AND property_id = :property_id";
    $delete_stmt = $db->prepare($delete_query);
    $delete_stmt->bindParam(':user_id', $_SESSION['user_id']);
    $delete_stmt->bindParam(':property_id', $data->property_id);
    
    if ($delete_stmt->execute()) {
        // Remove from cookie
        $favorites = array_diff($favorites, [$data->property_id]);
        setcookie('favorites', json_encode($favorites), time() + (86400 * 30), '/'); // 30 days
        
        echo json_encode([
            'success' => true,
            'action' => 'removed',
            'message' => 'Property removed from favorites'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error removing property from favorites'
        ]);
    }
} else {
    // Add to favorites
    $insert_query = "INSERT INTO favorites (user_id, property_id, created_at) VALUES (:user_id, :property_id, NOW())";
    $insert_stmt = $db->prepare($insert_query);
    $insert_stmt->bindParam(':user_id', $_SESSION['user_id']);
    $insert_stmt->bindParam(':property_id', $data->property_id);
    
    if ($insert_stmt->execute()) {
        // Add to cookie
        $favorites[] = $data->property_id;
        setcookie('favorites', json_encode(array_unique($favorites)), time() + (86400 * 30), '/'); // 30 days
        
        echo json_encode([
            'success' => true,
            'action' => 'added',
            'message' => 'Property added to favorites'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error adding property to favorites'
        ]);
    }
} 