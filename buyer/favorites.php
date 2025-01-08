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

// Get user's favorite properties
$query = "SELECT p.*, f.created_at as favorited_at 
          FROM properties p 
          INNER JOIN favorites f ON p.id = f.property_id 
          WHERE f.user_id = :user_id 
          ORDER BY f.created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favorites - RealEstate</title>
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
        .property-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 24px;
            transition: transform 0.2s;
            position: relative;
        }
        .property-card:hover {
            transform: translateY(-5px);
        }
        .property-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .property-content {
            padding: 20px;
        }
        .property-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .property-location {
            color: #718096;
            font-size: 14px;
            margin-bottom: 16px;
        }
        .property-price {
            color: #0066FF;
            font-size: 20px;
            font-weight: 600;
        }
        .remove-favorite {
            position: absolute;
            top: 16px;
            right: 16px;
            background: rgba(255, 255, 255, 0.9);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            color: #dc3545;
        }
        .remove-favorite:hover {
            background: #fff;
            transform: scale(1.1);
        }
        .favorited-date {
            font-size: 12px;
            color: #718096;
            margin-top: 8px;
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
                        <a class="nav-link active" href="favorites.php">Favorites</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="inquiries.php">My Inquiries</a>
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
            <h1 class="page-title">My Favorite Properties</h1>
            <p class="lead">Manage your collection of favorite properties</p>
        </div>
    </div>

    <div class="container">
        <?php if ($stmt->rowCount() > 0): ?>
            <div class="row">
                <?php while ($property = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="property-card">
                        <button class="remove-favorite" onclick="removeFavorite(<?php echo $property['id']; ?>)" title="Remove from favorites">
                            <i class="fas fa-heart"></i>
                        </button>
                        <img src="../uploads/<?php echo htmlspecialchars($property['photo']); ?>" 
                             alt="<?php echo htmlspecialchars($property['title']); ?>" 
                             class="property-image">
                        <div class="property-content">
                            <h5 class="property-title"><?php echo htmlspecialchars($property['title']); ?></h5>
                            <p class="property-location">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <?php echo htmlspecialchars($property['location']); ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="property-price">$<?php echo number_format($property['price']); ?></div>
                                <a href="property-details.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">View Details</a>
                            </div>
                            <div class="favorited-date">
                                Added to favorites on <?php echo date('M j, Y', strtotime($property['favorited_at'])); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="far fa-heart"></i>
                <h3>No Favorite Properties Yet</h3>
                <p>Start adding properties to your favorites to see them here</p>
                <a href="properties-listing.php" class="btn btn-primary">Browse Properties</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function removeFavorite(propertyId) {
        if (confirm('Are you sure you want to remove this property from your favorites?')) {
            fetch('remove-favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    property_id: propertyId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the property card from the DOM
                    const card = document.querySelector(`[onclick="removeFavorite(${propertyId})"]`).closest('.col-md-6');
                    card.remove();
                    
                    // Check if there are any properties left
                    const remainingCards = document.querySelectorAll('.property-card');
                    if (remainingCards.length === 0) {
                        // Show empty state
                        const container = document.querySelector('.container');
                        container.innerHTML = `
                            <div class="empty-state">
                                <i class="far fa-heart"></i>
                                <h3>No Favorite Properties Yet</h3>
                                <p>Start adding properties to your favorites to see them here</p>
                                <a href="properties-listing.php" class="btn btn-primary">Browse Properties</a>
                            </div>
                        `;
                    }
                }
            });
        }
    }
    </script>
</body>
</html> 