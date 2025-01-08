<?php
require_once "config/database.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid property ID']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // First get the property photo to delete it
    $stmt = $db->prepare("SELECT photo FROM properties WHERE id = ?");
    $stmt->execute([$id]);
    $property = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($property && $property['photo']) {
        $photo_path = __DIR__ . '/uploads/' . $property['photo'];
        if (file_exists($photo_path)) {
            unlink($photo_path);
        }
    }

    // Delete the property
    $stmt = $db->prepare("DELETE FROM properties WHERE id = ?");
    $result = $stmt->execute([$id]);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to delete property');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 