<?php
require_once('../config/database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    // Get form data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $property_id = intval($_POST['property_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');
    $contact_method = $_POST['contact_method'] ?? 'email';
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($phone) || empty($message) || empty($property_id)) {
        $response['message'] = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address.';
    } else {
        try {
            $database = new Database();
            $conn = $database->getConnection();
            
            // Insert into inquiries table
            $query = "INSERT INTO inquiries (name, email, phone, property_id, message, preferred_contact, status, created_at) 
                     VALUES (:name, :email, :phone, :property_id, :message, :contact_method, 'new', NOW())";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':property_id', $property_id);
            $stmt->bindParam(':message', $message);
            $stmt->bindParam(':contact_method', $contact_method);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Thank you for your message! Our agent will contact you soon.';
                
                // You could add email notification here
                // mail($agent_email, 'New Property Inquiry', $message);
            } else {
                $response['message'] = 'Sorry, there was an error sending your message. Please try again.';
            }
        } catch (PDOException $e) {
            $response['message'] = 'Database error. Please try again later.';
        }
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// If not POST request, redirect to contact page
header('Location: contact.php');
exit; 