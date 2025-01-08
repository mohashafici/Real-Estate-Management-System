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

// Get property ID from URL
$property_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($property_id === 0) {
    header("Location: properties-listing.php");
    exit;
}

// Get property details
$query = "SELECT * FROM properties WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $property_id);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    header("Location: properties-listing.php");
    exit;
}

$property = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if property is in user's favorites
$favorites_query = "SELECT COUNT(*) FROM favorites WHERE user_id = :user_id AND property_id = :property_id";
$favorites_stmt = $db->prepare($favorites_query);
$favorites_stmt->bindParam(':user_id', $_SESSION['user_id']);
$favorites_stmt->bindParam(':property_id', $property_id);
$favorites_stmt->execute();
$is_favorite = $favorites_stmt->fetchColumn() > 0;

// Handle inquiry submission
$success_message = '';
$error_message = '';

// Get user's information
$user_query = "SELECT username, email FROM users WHERE id = :user_id";
$user_stmt = $db->prepare($user_query);
$user_stmt->bindParam(':user_id', $_SESSION['user_id']);
$user_stmt->execute();
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_inquiry'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $message = trim($_POST['message']);
    $preferred_contact = trim($_POST['preferred_contact']);
    
    if (empty($name) || empty($email) || empty($phone) || empty($message) || empty($preferred_contact)) {
        $error_message = "Please fill in all required fields.";
    } else {
        $inquiry_query = "INSERT INTO inquiries (name, email, phone, property_id, message, preferred_contact, status, created_at) 
                         VALUES (:name, :email, :phone, :property_id, :message, :preferred_contact, 'pending', NOW())";
        $inquiry_stmt = $db->prepare($inquiry_query);
        $inquiry_stmt->bindParam(':name', $name);
        $inquiry_stmt->bindParam(':email', $email);
        $inquiry_stmt->bindParam(':phone', $phone);
        $inquiry_stmt->bindParam(':property_id', $property_id);
        $inquiry_stmt->bindParam(':message', $message);
        $inquiry_stmt->bindParam(':preferred_contact', $preferred_contact);
        
        if ($inquiry_stmt->execute()) {
            $success_message = "Your inquiry has been submitted successfully.";
        } else {
            $error_message = "There was an error submitting your inquiry. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($property['title']); ?> - RealEstate</title>
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
        .property-header {
            background: linear-gradient(135deg, #0066FF 0%, #5C85FF 100%);
            padding: 48px 0;
            color: white;
            margin-bottom: 48px;
        }
        .property-title {
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 16px;
        }
        .property-location {
            font-size: 18px;
            opacity: 0.9;
        }
        .property-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 24px;
        }
        .property-details {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .property-price {
            font-size: 24px;
            font-weight: 600;
            color: #0066FF;
            margin-bottom: 24px;
        }
        .property-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .feature-item {
            display: flex;
            align-items: center;
            color: #4a5568;
        }
        .feature-item i {
            width: 24px;
            margin-right: 8px;
            color: #0066FF;
        }
        .property-description {
            color: #4a5568;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .inquiry-form {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .favorite-btn {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .favorite-btn:hover {
            background: #f8fafc;
        }
        .favorite-btn i {
            color: #dc3545;
        }
        .favorite-btn.active {
            background: #dc3545;
            border-color: #dc3545;
            color: white;
        }
        .favorite-btn.active i {
            color: white;
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
                        <a class="nav-link" href="inquiries.php">My Inquiries</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Sign Out</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="property-header">
        <div class="container">
            <h1 class="property-title"><?php echo htmlspecialchars($property['title']); ?></h1>
            <p class="property-location">
                <i class="fas fa-map-marker-alt me-2"></i>
                <?php echo htmlspecialchars($property['location']); ?>
            </p>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <img src="../uploads/<?php echo htmlspecialchars($property['photo']); ?>" 
                     alt="<?php echo htmlspecialchars($property['title']); ?>" 
                     class="property-image">
                
                <div class="property-details">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="property-price">$<?php echo number_format($property['price']); ?></div>
                        <button class="favorite-btn <?php echo $is_favorite ? 'active' : ''; ?>" onclick="toggleFavorite(<?php echo $property_id; ?>)">
                            <i class="<?php echo $is_favorite ? 'fas' : 'far'; ?> fa-heart"></i>
                            <?php echo $is_favorite ? 'Remove from Favorites' : 'Add to Favorites'; ?>
                        </button>
                    </div>

                    <div class="property-features">
                        <div class="feature-item">
                            <i class="fas fa-bed"></i>
                            <?php echo $property['bedrooms']; ?> Bedrooms
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-bath"></i>
                            <?php echo $property['bathrooms']; ?> Bathrooms
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-ruler-combined"></i>
                            <?php echo number_format($property['area']); ?> <?php echo htmlspecialchars($property['area_unit']); ?>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-home"></i>
                            <?php echo htmlspecialchars($property['property_type']); ?>
                        </div>
                    </div>

                    <h3 class="h5 mb-3">Description</h3>
                    <div class="property-description">
                        <?php echo nl2br(htmlspecialchars($property['description'])); ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="inquiry-form">
                    <h3 class="h5 mb-4">Inquire About This Property</h3>
                    
                    <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <?php echo $success_message; ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($error_message): ?>
                    <div class="alert alert-danger">
                        <?php echo $error_message; ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="preferred_contact" class="form-label">Preferred Contact Method</label>
                            <select class="form-control" id="preferred_contact" name="preferred_contact" required>
                                <option value="">Select preferred contact method</option>
                                <option value="email">Email</option>
                                <option value="phone">Phone</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Your Message</label>
                            <textarea class="form-control" id="message" name="message" rows="6" placeholder="Enter your message here..." required></textarea>
                        </div>
                        <button type="submit" name="submit_inquiry" class="btn btn-primary w-100">Submit Inquiry</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get favorites from cookie
        const favorites = document.cookie.split('; ')
            .find(row => row.startsWith('favorites='))
            ?.split('=')[1];
        
        if (favorites) {
            const favoritesList = JSON.parse(decodeURIComponent(favorites));
            const propertyId = <?php echo $property_id; ?>;
            const favoriteBtn = document.querySelector('.favorite-btn');
            
            if (favoritesList.includes(propertyId.toString())) {
                favoriteBtn.classList.add('active');
                const icon = favoriteBtn.querySelector('i');
                icon.classList.remove('far');
                icon.classList.add('fas');
                favoriteBtn.textContent = 'Remove from Favorites';
                favoriteBtn.insertBefore(icon, favoriteBtn.firstChild);
            }
        }
    });

    function toggleFavorite(propertyId) {
        fetch('toggle-favorite.php', {
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
                const btn = document.querySelector('.favorite-btn');
                const icon = btn.querySelector('i');
                
                if (data.action === 'added') {
                    btn.classList.add('active');
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                    btn.textContent = 'Remove from Favorites';
                } else {
                    btn.classList.remove('active');
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                    btn.textContent = 'Add to Favorites';
                }
                
                // Prepend the icon back
                btn.insertBefore(icon, btn.firstChild);
                
                // Update favorites cookie
                const favorites = document.cookie.split('; ')
                    .find(row => row.startsWith('favorites='))
                    ?.split('=')[1];
                
                if (favorites) {
                    let favoritesList = JSON.parse(decodeURIComponent(favorites));
                    if (data.action === 'added') {
                        favoritesList.push(propertyId.toString());
                    } else {
                        favoritesList = favoritesList.filter(id => id !== propertyId.toString());
                    }
                    document.cookie = `favorites=${encodeURIComponent(JSON.stringify(favoritesList))}; path=/; max-age=${30*24*60*60}`;
                }
            }
        });
    }
    </script>
</body>
</html> 