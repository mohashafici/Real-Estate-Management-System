<?php
require_once "config/database.php";

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Get filters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12; // Fixed limit of 12 items per page
$offset = ($page - 1) * $limit;

// Build query for rental properties
$query = "SELECT * FROM properties WHERE status = 'rented'";
if (!empty($search)) {
    $query .= " AND (title LIKE :search OR location LIKE :search)";
}
$query .= " ORDER BY added_on DESC LIMIT :limit OFFSET :offset";

// Prepare and execute query
$stmt = $db->prepare($query);
if (!empty($search)) {
    $search_term = "%{$search}%";
    $stmt->bindParam(':search', $search_term);
}
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

// Get total count for pagination
$count_query = "SELECT COUNT(*) FROM properties WHERE status = 'rented'";
if (!empty($search)) {
    $count_query .= " AND (title LIKE :search OR location LIKE :search)";
}
$count_stmt = $db->prepare($count_query);
if (!empty($search)) {
    $count_stmt->bindParam(':search', $search_term);
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
    <title>Rental Properties - RealEstate</title>
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
        .brand-title {
            color: #fff;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 32px;
            padding: 0 16px;
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
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        .nav-link i {
            width: 20px;
            margin-right: 12px;
            font-size: 16px;
        }
        .submenu {
            display: none;
            list-style: none;
            padding-left: 48px;
            margin: 0;
        }
        .submenu .nav-link {
            padding: 8px 16px;
            font-size: 14px;
            color: #8a8b9f;
        }
        .submenu .nav-link:hover {
            color: #fff;
        }
        .has-submenu.open .submenu {
            display: block;
        }
        .has-submenu .nav-link::after {
            content: '\f107';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            margin-left: auto;
            transition: transform 0.2s;
        }
        .has-submenu.open .nav-link::after {
            transform: rotate(180deg);
        }
        .property-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .rental-badge {
            display: inline-block;
            background: #00B087;
            color: #fff;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 16px;
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
        .rental-info {
            background: #f8f9fa;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 16px;
        }
        .rental-info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .rental-info-label {
            color: #718096;
        }
        .rental-info-value {
            color: #2d3748;
            font-weight: 500;
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
            <li class="nav-item has-submenu">
                <a class="nav-link" href="javascript:void(0);">
                    <i class="fas fa-home"></i>
                    Properties
                </a>
                <ul class="submenu">
                    <li class="nav-item">
                        <a class="nav-link" href="add-property.php">
                            Add Property
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="properties.php?status=published">
                            Published
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="properties.php?status=pending">
                            Pending Review
                        </a>
                    </li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="featured-properties.php">
                    <i class="fas fa-star"></i>
                    Featured Properties
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="favorites.php">
                    <i class="far fa-heart"></i>
                    My Favorites
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="rental-properties.php">
                    <i class="fas fa-building"></i>
                    Rental Properties
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="container-fluid" style="margin-left: 250px; padding: 24px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h4 mb-0">Rental Properties</h1>
            <a href="add-property.php?type=rental" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add Rental Property
            </a>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="search-box mb-4">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control" placeholder="Search rental properties..." value="<?php echo htmlspecialchars($search); ?>">
                </div>

                <div class="row">
                    <?php while ($property = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="property-card">
                            <span class="rental-badge">
                                <i class="fas fa-key me-1"></i>Rental
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
                            <div class="rental-info">
                                <div class="rental-info-item">
                                    <span class="rental-info-label">Monthly Rent</span>
                                    <span class="rental-info-value">$<?php echo number_format($property['price']); ?></span>
                                </div>
                                <div class="rental-info-item">
                                    <span class="rental-info-label">Security Deposit</span>
                                    <span class="rental-info-value">$<?php echo number_format($property['price'] * 2); ?></span>
                                </div>
                                <div class="rental-info-item">
                                    <span class="rental-info-label">Lease Term</span>
                                    <span class="rental-info-value">12 months</span>
                                </div>
                            </div>
                            <div class="action-buttons">
                                <a href="view-property.php?id=<?php echo $property['id']; ?>" class="btn btn-light btn-action">
                                    <i class="fas fa-eye me-2"></i>View
                                </a>
                                <a href="edit-property.php?id=<?php echo $property['id']; ?>" class="btn btn-light btn-action">
                                    <i class="fas fa-edit me-2"></i>Edit
                                </a>
                                <button onclick="updatePropertyStatus(<?php echo $property['id']; ?>, 'available')" class="btn btn-danger btn-action">
                                    <i class="fas fa-times me-2"></i>End Rental
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
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
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

        // Handle property status update
        function updatePropertyStatus(propertyId, status) {
            if (confirm('Are you sure you want to end this rental?')) {
                fetch('update-property-status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        property_id: propertyId,
                        status: status
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error updating property status');
                    }
                });
            }
        }

        // Handle submenu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const submenuItems = document.querySelectorAll('.nav-item.has-submenu');
            
            submenuItems.forEach(item => {
                const link = item.querySelector('.nav-link');
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    submenuItems.forEach(otherItem => {
                        if (otherItem !== item) {
                            otherItem.classList.remove('open');
                        }
                    });
                    item.classList.toggle('open');
                });
            });

            // Set active state based on current page
            const currentPath = window.location.pathname;
            const currentLink = document.querySelector(`a[href="${currentPath}"]`);
            if (currentLink) {
                currentLink.classList.add('active');
                const parentSubmenu = currentLink.closest('.has-submenu');
                if (parentSubmenu) {
                    parentSubmenu.classList.add('open');
                }
            }
        });
    </script>
</body>
</html> 