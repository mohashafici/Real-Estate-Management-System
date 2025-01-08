<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit;
}

// Check if user is a buyer
if ($_SESSION['role'] !== 'buyer') {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

$database = new Database();
$db = $database->getConnection();

// Get user's inquiries
$query = "SELECT i.*, p.title as property_title, p.photo as property_photo, p.price as property_price 
          FROM inquiries i 
          INNER JOIN properties p ON i.property_id = p.id 
          WHERE i.email = (SELECT email FROM users WHERE id = :user_id)
          ORDER BY i.created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Inquiries - RealEstate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #1a1c23;
            padding: 16px 0;
        }
        .navbar-brand {
            color: #fff;
            font-size: 20px;
            font-weight: 600;
        }
        .nav-link {
            color: #8a8b9f;
            padding: 8px 16px;
            transition: all 0.2s;
        }
        .nav-link:hover {
            color: #fff;
        }
        .page-header {
            background: linear-gradient(135deg, #0066FF 0%, #5C85FF 100%);
            padding: 48px 0;
            color: white;
            margin-bottom: 48px;
        }
        .page-title {
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 16px;
        }
        .inquiry-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 24px;
        }
        .inquiry-header {
            display: flex;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        .property-image {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
            margin-right: 16px;
        }
        .property-info h5 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .property-price {
            color: #0066FF;
            font-size: 16px;
            font-weight: 600;
        }
        .inquiry-content {
            padding: 20px;
        }
        .inquiry-message {
            color: #4a5568;
            margin-bottom: 16px;
            line-height: 1.6;
        }
        .inquiry-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #718096;
            font-size: 14px;
        }
        .inquiry-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-pending {
            background-color: #fff8e1;
            color: #f59e0b;
        }
        .status-replied {
            background-color: #e8f5e9;
            color: #22c55e;
        }
        .empty-state {
            text-align: center;
            padding: 48px 0;
        }
        .empty-state i {
            font-size: 48px;
            color: #718096;
            margin-bottom: 16px;
        }
        .empty-state h3 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #1a1c23;
        }
        .empty-state p {
            color: #718096;
            margin-bottom: 24px;
        }
        .response-text {
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 16px;
            margin-top: 16px;
            color: #4a5568;
        }
        .response-text p {
            margin-bottom: 8px;
        }
        .response-meta {
            font-size: 12px;
            color: #718096;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">RealEstate</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="properties-listing.php">Properties</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="favorites.php">Favorites</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="inquiries.php">My Inquiries</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Sign Out</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="page-header">
        <div class="container">
            <h1 class="page-title">My Inquiries</h1>
            <p class="lead">Track and manage your property inquiries</p>
        </div>
    </div>

    <div class="container">
        <?php if ($stmt->rowCount() > 0): ?>
            <?php while ($inquiry = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <div class="inquiry-card">
                <div class="inquiry-header">
                    <img src="../uploads/<?php echo htmlspecialchars($inquiry['property_photo']); ?>" 
                         alt="<?php echo htmlspecialchars($inquiry['property_title']); ?>" 
                         class="property-image">
                    <div class="property-info">
                        <h5><?php echo htmlspecialchars($inquiry['property_title']); ?></h5>
                        <div class="property-price">$<?php echo number_format($inquiry['property_price']); ?></div>
                    </div>
                </div>
                <div class="inquiry-content">
                    <div class="inquiry-message">
                        <?php echo nl2br(htmlspecialchars($inquiry['message'])); ?>
                    </div>
                    <div class="inquiry-meta">
                        <div>
                            <strong>Contact Info:</strong><br>
                            Name: <?php echo htmlspecialchars($inquiry['name']); ?><br>
                            <?php echo htmlspecialchars($inquiry['preferred_contact']); ?>: 
                            <?php echo htmlspecialchars($inquiry['preferred_contact'] === 'email' ? $inquiry['email'] : $inquiry['phone']); ?>
                        </div>
                        <div class="text-end">
                            <div>Sent on <?php echo date('M j, Y', strtotime($inquiry['created_at'])); ?></div>
                            <span class="inquiry-status <?php echo $inquiry['status'] === 'pending' ? 'status-pending' : 'status-replied'; ?>">
                                <?php echo ucfirst($inquiry['status']); ?>
                            </span>
                        </div>
                    </div>
                    <?php if (!empty($inquiry['response'])): ?>
                    <div class="response-text">
                        <p><strong>Response:</strong></p>
                        <?php echo nl2br(htmlspecialchars($inquiry['response'])); ?>
                        <div class="response-meta">
                            Responded on <?php echo date('M j, Y', strtotime($inquiry['response_date'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="far fa-comment-dots"></i>
                <h3>No Inquiries Yet</h3>
                <p>You haven't made any property inquiries yet</p>
                <a href="properties-listing.php" class="btn btn-primary">Browse Properties</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 