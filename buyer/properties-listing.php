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

// Get filters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'available';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 9; // Properties per page
$offset = ($page - 1) * $limit;

// Build query
$query = "SELECT * FROM properties WHERE status = :status";
if (!empty($search)) {
    $query .= " AND (title LIKE :search OR location LIKE :search)";
}
$query .= " ORDER BY added_on DESC LIMIT :limit OFFSET :offset";

// Prepare and execute query
$stmt = $db->prepare($query);
$stmt->bindParam(':status', $status_filter);
if (!empty($search)) {
    $search_term = "%{$search}%";
    $stmt->bindParam(':search', $search_term);
}
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

// Get total count for pagination
$count_query = "SELECT COUNT(*) FROM properties WHERE status = :status";
if (!empty($search)) {
    $count_query .= " AND (title LIKE :search OR location LIKE :search)";
}
$count_stmt = $db->prepare($count_query);
$count_stmt->bindParam(':status', $status_filter);
if (!empty($search)) {
    $count_stmt->bindParam(':search', $search_term);
}
$count_stmt->execute();
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// After database connection, add this query to check user's favorites
// Get user's favorites
$favorites_check_query = "SELECT property_id FROM favorites WHERE user_id = :user_id";
$favorites_check_stmt = $db->prepare($favorites_check_query);
$favorites_check_stmt->bindParam(':user_id', $_SESSION['user_id']);
$favorites_check_stmt->execute();
$user_favorites = $favorites_check_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Properties - RealEstate</title>
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
        .property-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 24px;
            transition: transform 0.2s;
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
        .search-box {
            position: relative;
            max-width: 800px;
            margin: 0 auto 48px;
        }
        .search-box input {
            width: 100%;
            height: 54px;
            padding: 0 24px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 16px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .search-box button {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: none;
            color: #718096;
        }
        .favorite-btn {
            position: absolute;
            top: 16px;
            right: 16px;
            background: rgba(255, 255, 255, 0.9);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            z-index: 1;
        }
        .favorite-btn:hover {
            background: white;
            transform: scale(1.1);
        }
        .favorite-btn i {
            color: #dc3545;
            font-size: 20px;
        }
        .favorite-btn.active {
            background: #dc3545;
        }
        .favorite-btn.active i {
            color: white;
        }
        .position-relative {
            position: relative;
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
                        <a class="nav-link active" href="properties-listing.php">Properties</a>
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

    <div class="container py-5">
        <form action="search.php" method="GET" class="search-box">
            <input type="text" name="search" placeholder="Search properties by name or location..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">
                <i class="fas fa-search"></i>
            </button>
        </form>

        <div class="row">
            <?php while ($property = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <div class="col-md-4">
                <div class="property-card">
                    <div class="position-relative">
                        <img src="../uploads/<?php echo htmlspecialchars($property['photo']); ?>" 
                             alt="<?php echo htmlspecialchars($property['title']); ?>" 
                             class="property-image">
                        <button class="favorite-btn <?php echo in_array($property['id'], $user_favorites) ? 'active' : ''; ?>" 
                                onclick="toggleFavorite(<?php echo $property['id']; ?>, this)">
                            <i class="<?php echo in_array($property['id'], $user_favorites) ? 'fas' : 'far'; ?> fa-heart"></i>
                        </button>
                    </div>
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
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function toggleFavorite(propertyId, button) {
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
                const icon = button.querySelector('i');
                
                if (data.action === 'added') {
                    button.classList.add('active');
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                } else {
                    button.classList.remove('active');
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                }
                
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

    // Initialize favorites from cookie
    document.addEventListener('DOMContentLoaded', function() {
        const favorites = document.cookie.split('; ')
            .find(row => row.startsWith('favorites='))
            ?.split('=')[1];
        
        if (favorites) {
            const favoritesList = JSON.parse(decodeURIComponent(favorites));
            document.querySelectorAll('.favorite-btn').forEach(btn => {
                const propertyId = btn.getAttribute('onclick').match(/\d+/)[0];
                if (favoritesList.includes(propertyId)) {
                    btn.classList.add('active');
                    const icon = btn.querySelector('i');
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                }
            });
        }
    });
    </script>
</body>
</html> 