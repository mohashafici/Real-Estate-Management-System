<!-- <?php
require_once "config/database.php";

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Get filters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$limit = isset($_GET['show']) ? (int)$_GET['show'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Assuming user is logged in with ID 1 (you should replace this with actual user session)
$user_id = 1;

// Build query to get favorite properties
$query = "SELECT p.* FROM properties p 
          INNER JOIN favorites f ON p.id = f.property_id 
          WHERE f.user_id = :user_id";

if (!empty($search)) {
    $query .= " AND (p.title LIKE :search OR p.location LIKE :search)";
}

$query .= " ORDER BY f.added_on DESC LIMIT :limit OFFSET :offset";

// Prepare and execute query
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
if (!empty($search)) {
    $search_term = "%{$search}%";
    $stmt->bindParam(':search', $search_term);
}
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
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
        .sidebar {
            background-color: #1a1c23;
            min-height: 100vh;
            color: #8a8b9f;
            padding: 24px 16px;
            width: 250px;
            position: fixed;
            left: 0;
            top: 0;
        }
        .nav-item {
            margin: 4px 0;
        }
        .nav-link {
            color: #8a8b9f;
            padding: 12px 16px;
            border-radius: 8px;
            transition: all 0.2s;
            font-size: 14px;
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        .nav-link:hover, .nav-link.active {
            background-color: #2d303a;
            color: #fff;
        }
        .nav-link i {
            width: 20px;
            margin-right: 12px;
            font-size: 16px;
        }
        .brand-title {
            color: #fff;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 32px;
            padding: 0 16px;
        }
        .property-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .property-image {
            width: 200px;
            height: 140px;
            object-fit: cover;
            border-radius: 8px;
        }
        .property-title {
            font-size: 16px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
        }
        .property-location {
            color: #718096;
            font-size: 14px;
            margin-bottom: 16px;
        }
        .property-info {
            display: flex;
            gap: 24px;
            margin-bottom: 16px;
        }
        .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #718096;
            font-size: 14px;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
        }
        .badge-available {
            background: #E6F6F4;
            color: #00B087;
        }
        .badge-featured {
            background: #FFF3E5;
            color: #FFB547;
        }
        .badge-sold {
            background: #FFE5E5;
            color: #FF2D2D;
        }
        .price {
            color: #00B087;
            font-size: 18px;
            font-weight: 600;
        }
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        .btn-action {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
        }
        .search-box {
            position: relative;
            margin-bottom: 24px;
        }
        .search-box input {
            padding-left: 40px;
            height: 44px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            width: 100%;
        }
        .search-box i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #718096;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="brand-title">RealEstate</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-th-large"></i>
                    My Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="properties.php">
                    <i class="fas fa-home"></i>
                    Properties
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="favorites.php">
                    <i class="fas fa-heart"></i>
                    Favorites
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="bookings.php">
                    <i class="far fa-calendar-alt"></i>
                    Bookings
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="profile.php">
                    <i class="far fa-user"></i>
                    Profile
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="saved-searches.php">
                    <i class="far fa-bell"></i>
                    Saved Searches
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="membership.php">
                    <i class="far fa-id-card"></i>
                    Membership
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    Sign Out
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="container-fluid" style="margin-left: 250px; padding: 24px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h4 mb-0">Favorites</h1>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control" placeholder="Search Properties" value="<?php echo htmlspecialchars($search); ?>">
                </div>

                <?php while ($property = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="property-card">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <img src="uploads/<?php echo htmlspecialchars($property['photo']); ?>" 
                                 alt="<?php echo htmlspecialchars($property['title']); ?>" 
                                 class="property-image">
                        </div>
                        <div class="col">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="property-title"><?php echo htmlspecialchars($property['title']); ?></h5>
                                    <p class="property-location">
                                        <i class="fas fa-map-marker-alt me-2"></i>
                                        <?php echo htmlspecialchars($property['location']); ?>
                                    </p>
                                    <div class="property-info">
                                        <div class="info-item">
                                            <i class="fas fa-bed"></i>
                                            <?php echo $property['bedrooms']; ?> Bedrooms
                                        </div>
                                        <div class="info-item">
                                            <i class="fas fa-bath"></i>
                                            <?php echo $property['bathrooms']; ?> Bathrooms
                                        </div>
                                        <div class="info-item">
                                            <i class="fas fa-vector-square"></i>
                                            <?php echo number_format($property['area']); ?> sqft
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="mb-2">
                                        <span class="status-badge badge-<?php echo strtolower($property['status']); ?>">
                                            <?php echo ucfirst($property['status']); ?>
                                        </span>
                                        <?php if ($property['is_featured']): ?>
                                        <span class="status-badge badge-featured ms-2">Featured</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="price mb-3">$<?php echo number_format($property['price']); ?></div>
                                    <div class="action-buttons">
                                        <a href="view-property.php?id=<?php echo $property['id']; ?>" class="btn-action btn btn-light">
                                            <i class="fas fa-eye me-2"></i> View
                                        </a>
                                        <button onclick="removeFavorite(<?php echo $property['id']; ?>)" class="btn-action btn btn-danger">
                                            <i class="fas fa-heart me-2"></i> Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <div class="col-lg-4">
                <!-- Additional content or filters can go here -->
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle search input
        const searchInput = document.querySelector('.search-box input');
        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const url = new URL(window.location);
                url.searchParams.set('search', e.target.value);
                url.searchParams.set('page', '1');
                window.location = url;
            }, 500);
        });

        // Handle remove favorite
        function removeFavorite(propertyId) {
            if (confirm('Are you sure you want to remove this property from favorites?')) {
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
                        location.reload();
                    } else {
                        alert('Error removing property from favorites');
                    }
                });
            }
        }
    </script>
</body>
</html>  -->