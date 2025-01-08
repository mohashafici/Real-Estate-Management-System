<?php
require_once "config/database.php";

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Get filters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12; // Fixed limit of 12 items per page
$offset = ($page - 1) * $limit;

// Build query
$query = "SELECT * FROM properties WHERE is_featured = 1";
if (!empty($search)) {
    $query .= " AND (title LIKE :search OR location LIKE :search)";
}
if ($status_filter !== 'all') {
    $query .= " AND status = :status";
}
$query .= " ORDER BY added_on DESC LIMIT :limit OFFSET :offset";

// Prepare and execute query
$stmt = $db->prepare($query);
if (!empty($search)) {
    $search_term = "%{$search}%";
    $stmt->bindParam(':search', $search_term);
}
if ($status_filter !== 'all') {
    $stmt->bindParam(':status', $status_filter);
}
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

// Get total count for pagination
$count_query = "SELECT COUNT(*) FROM properties WHERE is_featured = 1";
if (!empty($search)) {
    $count_query .= " AND (title LIKE :search OR location LIKE :search)";
}
if ($status_filter !== 'all') {
    $count_query .= " AND status = :status";
}
$count_stmt = $db->prepare($count_query);
if (!empty($search)) {
    $count_stmt->bindParam(':search', $search_term);
}
if ($status_filter !== 'all') {
    $count_stmt->bindParam(':status', $status_filter);
}
$count_stmt->execute();
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Featured Properties - RealEstate</title>
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
            position: relative;
        }
        .featured-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #FFB547;
            color: #fff;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
        }
        .property-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 16px;
        }
        .property-title {
            font-size: 18px;
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
            padding-bottom: 16px;
            border-bottom: 1px solid #e2e8f0;
        }
        .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #718096;
            font-size: 14px;
        }
        .price {
            color: #00B087;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 16px;
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
        }
        .search-box {
            position: relative;
            max-width: 800px;
            width: 100%;
        }
        .search-box input {
            padding: 12px 16px 12px 40px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            width: 100%;
            height: 48px;
            transition: all 0.2s;
            background-color: #fff;
            color: #333;
        }
        .search-box input:focus {
            border-color: #0066FF;
            box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.1);
            outline: none;
        }
        .search-box input::placeholder {
            color: #718096;
        }
        .search-box i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #718096;
            font-size: 14px;
        }
        .filters {
            display: flex;
            gap: 16px;
            margin-bottom: 24px;
        }
        .filter-select {
            min-width: 120px;
        }
        .submenu {
            list-style: none;
            padding-left: 32px;
            margin: 0;
            display: none;
        }
        .has-submenu.open .submenu {
            display: block;
        }
        .submenu .nav-link {
            padding: 8px 16px;
            font-size: 13px;
            color: #8a8b9f;
            opacity: 0.8;
        }
        .submenu .nav-link:hover {
            opacity: 1;
            color: #fff;
        }
        .nav-link {
            display: flex;
            align-items: center;
            color: #8a8b9f;
            text-decoration: none;
            padding: 12px 16px;
            border-radius: 8px;
            transition: all 0.2s;
            font-size: 14px;
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
        .form-select {
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            min-width: 180px;
            height: 48px;
            font-size: 14px;
            background-position: right 12px center;
        }
        .form-select:focus {
            border-color: #0066FF;
            box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.1);
        }
        .properties-header {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .btn-primary {
            height: 48px;
            padding: 0 24px;
            font-size: 14px;
            font-weight: 500;
        }
        .main-wrapper {
            margin-left: 250px;
            padding: 24px;
            max-width: calc(100% - 250px);
            width: 100%;
        }
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }
        .page-title {
            color: #2B3674;
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }
        .properties-header {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            width: 100%;
        }
        .search-box {
            position: relative;
            width: calc(100% - 200px);
            margin-right: 16px;
        }
        .search-box input {
            width: 100%;
            padding: 12px 16px 12px 40px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            height: 48px;
            transition: all 0.2s;
            background-color: #fff;
            color: #333;
        }
        .form-select {
            width: 180px;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            height: 48px;
            font-size: 14px;
            background-position: right 12px center;
            flex-shrink: 0;
        }
        .btn-primary {
            height: 48px;
            padding: 0 24px;
            font-size: 14px;
            font-weight: 500;
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
            <li class="nav-item has-submenu">
                <a class="nav-link" href="properties.php">
                    <i class="fas fa-home"></i>
                    Properties
                </a>
                <ul class="submenu">
                    <li class="nav-item">
                        <a class="nav-link" href="add-property.php">
                            Add Property
                        </a>
                    </li>
                </ul>
            </li>
            <!-- <li class="nav-item">
                <a class="nav-link" href="favorites.php">
                    <i class="far fa-heart"></i>
                    Favorites
                </a>
            </li> -->
            <!-- <li class="nav-item">
                <a class="nav-link" href="bookings.php">
                    <i class="far fa-calendar-alt"></i>
                    Bookings
                </a>
            </li> -->
            <li class="nav-item">
                <a class="nav-link" href="reports.php">
                    <i class="fas fa-chart-bar"></i>
                    Reports
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="featured-properties.php">
                    <i class="fas fa-star"></i>
                    Featured Properties
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage-queries.php">
                    <i class="far fa-comment-dots"></i>
                    Manage Queries
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
    <div class="main-wrapper">
        <div class="header-section">
            <h1 class="page-title">Featured Properties</h1>
            <a href="add-property.php?featured=1" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add Featured Property
            </a>
        </div>

        <div class="properties-header">
            <div class="d-flex align-items-center">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control" placeholder="Search featured properties..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <select class="form-select">
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All</option>
                    <option value="available" <?php echo $status_filter === 'available' ? 'selected' : ''; ?>>Available</option>
                    <option value="sold" <?php echo $status_filter === 'sold' ? 'selected' : ''; ?>>Sold</option>
                    <option value="rented" <?php echo $status_filter === 'rented' ? 'selected' : ''; ?>>Rented</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="row mt-4">
                    <?php while ($property = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="property-card">
                            <span class="featured-badge">
                                <i class="fas fa-star me-1"></i>Featured
                            </span>
                            <img src="uploads/<?php echo htmlspecialchars($property['photo']); ?>" 
                                 alt="<?php echo htmlspecialchars($property['title']); ?>" 
                                 class="property-image">
                            <h5 class="property-title"><?php echo htmlspecialchars($property['title']); ?></h5>
                            <p class="property-location">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <?php echo htmlspecialchars($property['location']); ?>
                            </p>
                            <div class="property-info">
                                <div class="info-item">
                                    <i class="fas fa-bed"></i>
                                    <?php echo $property['bedrooms']; ?> Beds
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-bath"></i>
                                    <?php echo $property['bathrooms']; ?> Baths
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-vector-square"></i>
                                    <?php echo number_format($property['area']); ?> sqft
                                </div>
                            </div>
                            <div class="price">$<?php echo number_format($property['price']); ?></div>
                            <div class="action-buttons">
                                <a href="view-property.php?id=<?php echo $property['id']; ?>" class="btn btn-light btn-action">
                                    <i class="fas fa-eye me-2"></i>View
                                </a>
                                <a href="edit-property.php?id=<?php echo $property['id']; ?>" class="btn btn-light btn-action">
                                    <i class="fas fa-edit me-2"></i>Edit
                                </a>
                                <button onclick="removeFromFeatured(<?php echo $property['id']; ?>)" class="btn btn-danger btn-action">
                                    <i class="fas fa-star me-2"></i>Remove
                                </button>
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
                            <a class="page-link" href="?page=<?php echo $i; ?>&show=<?php echo $limit; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
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

        // Handle status filter
        document.querySelector('.filter-select').addEventListener('change', (e) => {
                const url = new URL(window.location);
                if (e.target.value === 'all') {
                    url.searchParams.delete('status');
                } else {
                url.searchParams.set('status', e.target.value);
                }
                url.searchParams.set('page', '1');
                window.location = url;
        });

        // Handle remove from featured
        function removeFromFeatured(propertyId) {
            if (confirm('Are you sure you want to remove this property from featured?')) {
                fetch('update-featured.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        property_id: propertyId,
                        is_featured: false
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error updating featured status');
                    }
                });
            }
        }

        // Add dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const submenuItems = document.querySelectorAll('.nav-item.has-submenu');
            
            submenuItems.forEach(item => {
                const link = item.querySelector('.nav-link');
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    item.classList.toggle('open');
                });
            });
        });
    </script>
</body>
</html> 