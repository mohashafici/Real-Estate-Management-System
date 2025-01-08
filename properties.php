<?php
require_once "config/database.php";

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Get filters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$property_status = isset($_GET['property_status']) ? $_GET['property_status'] : 'all';
$limit = isset($_GET['show']) ? (int)$_GET['show'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Build query
$query = "SELECT * FROM properties WHERE 1=1";
if (!empty($search)) {
    $query .= " AND (title LIKE :search OR location LIKE :search)";
}
if ($status_filter !== 'all') {
    $query .= " AND status = :status";
}
if ($property_status !== 'all') {
    $query .= " AND property_status = :property_status";
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
if ($property_status !== 'all') {
    $stmt->bindParam(':property_status', $property_status);
}
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

// Get total count for pagination
$count_query = "SELECT COUNT(*) FROM properties WHERE 1=1";
if (!empty($search)) {
    $count_query .= " AND (title LIKE :search OR location LIKE :search)";
}
if ($status_filter !== 'all') {
    $count_query .= " AND status = :status";
}
if ($property_status !== 'all') {
    $count_query .= " AND property_status = :property_status";
}
$count_stmt = $db->prepare($count_query);
if (!empty($search)) {
    $count_stmt->bindParam(':search', $search_term);
}
if ($status_filter !== 'all') {
    $count_stmt->bindParam(':status', $status_filter);
}
if ($property_status !== 'all') {
    $count_stmt->bindParam(':property_status', $property_status);
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
    <title>Properties - RealEstate</title>
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
            opacity: 0.8;
        }
        .submenu .nav-link:hover {
            opacity: 1;
        }
        .brand-title {
            color: #fff;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 32px;
            padding: 0 16px;
        }
        .properties-header {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .property-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .property-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-published {
            background: #E6F6F4;
            color: #00B087;
        }
        .status-featured {
            background: #FFF3E5;
            color: #FFB547;
        }
        .status-sold {
            background: #FFE5E5;
            color: #FF2D2D;
        }
        .property-info {
            display: flex;
            gap: 24px;
            margin-top: 12px;
        }
        .property-info-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
            font-size: 14px;
        }
        .property-actions a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
            margin-left: 16px;
        }
        .property-actions a:hover {
            color: #333;
        }
        .search-box {
            position: relative;
            max-width: 500px;
            width: 100%;
        }
        .search-box input {
            padding: 10px 16px 10px 40px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.875rem;
            width: 100%;
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
        .filter-dropdown {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            height: 44px;
        }
        .table {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .table th {
            font-weight: 500;
            color: #718096;
            padding: 16px;
            border-bottom: 1px solid #e2e8f0;
        }
        .table td {
            padding: 16px;
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
        }
        .badge {
            padding: 6px 12px;
            font-weight: 500;
            border-radius: 6px;
        }
        .btn-primary {
            background: #0066FF;
            border: none;
            padding: 8px 16px;
            font-weight: 500;
        }
        .form-select {
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            min-width: 140px;
            font-size: 0.95rem;
            background-position: right 12px center;
        }
        .form-select:focus {
            border-color: #0066FF;
            box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.1);
        }
        .properties-header {
            margin-bottom: 24px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="brand-title">RealEstate</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-th-large"></i>
                    My Dashboard
                </a>
            </li>
            <li class="nav-item has-submenu open">
                <a class="nav-link active" href="#">
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
            <li class="nav-item">
                <a class="nav-link" href="featured-properties.php">
                    <i class="fas fa-star"></i>
                    Featured Properties
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="reports.php">
                    <i class="fas fa-chart-bar"></i>
                    Reports
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage-queries.php">
                    <i class="far fa-comment-dots"></i>
                    Manage Queries
                </a>
            </li>
            <!-- <li class="nav-item">
                <a class="nav-link" href="profile.php">
                    <i class="far fa-user"></i>
                    Profile
                </a>
            </li> -->
            <!-- <li class="nav-item">
                <a class="nav-link" href="saved-searches.php">
                    <i class="far fa-bell"></i>
                    Saved Searches
                </a>
            </li> -->
            <!-- <li class="nav-item">
                <a class="nav-link" href="membership.php">
                    <i class="far fa-id-card"></i>
                    Featured
                </a>
            </li> -->
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    Sign Out
                </a>
            </li>
        </ul>
    </div>

    <div class="container-fluid py-4" style="margin-left: 250px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h4 mb-0">Properties</h1>
            <a href="add-property.php" class="btn btn-primary">Add Property</a>
        </div>

        <div class="properties-header p-4 bg-white rounded-3 shadow-sm">
            <div class="d-flex justify-content-between align-items-center gap-3">
                <div class="search-box flex-grow-1">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control" placeholder="Search Properties" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div>
                    <select class="form-select">
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All</option>
                        <option value="available" <?php echo $status_filter === 'available' ? 'selected' : ''; ?>>Available</option>
                        <option value="sold" <?php echo $status_filter === 'sold' ? 'selected' : ''; ?>>Sold</option>
                        <option value="rented" <?php echo $status_filter === 'rented' ? 'selected' : ''; ?>>Rented</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table">
        <?php while ($property = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
        <div class="property-card">
            <div class="row align-items-center">
                <div class="col-auto">
                    <img src="uploads/<?php echo htmlspecialchars($property['photo']); ?>" alt="<?php echo htmlspecialchars($property['title']); ?>" class="property-image">
                </div>
                <div class="col">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="mb-2"><?php echo htmlspecialchars($property['title']); ?></h5>
                            <div class="property-info">
                                <div class="property-info-item">
                                    <i class="fas fa-bed"></i>
                                    <?php echo $property['bedrooms']; ?> Bedrooms
                                </div>
                                <div class="property-info-item">
                                    <i class="fas fa-bath"></i>
                                    <?php echo $property['bathrooms']; ?> Bathrooms
                                </div>
                                <div class="property-info-item">
                                    <i class="fas fa-vector-square"></i>
                                    <?php echo number_format($property['area']); ?> sqft
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="mb-2">
                                <span class="status-badge status-<?php echo strtolower($property['status']); ?>">
                                    <?php echo ucfirst($property['status']); ?>
                                </span>
                                <?php if ($property['is_featured']): ?>
                                <span class="status-badge status-featured ms-2">Featured</span>
                                <?php endif; ?>
                            </div>
                            <div class="text-success h5 mb-0">$<?php echo number_format($property['price']); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="property-actions">
                        <a href="view-property.php?id=<?php echo $property['id']; ?>">View</a>
                        <a href="edit-property.php?id=<?php echo $property['id']; ?>">Edit</a>
                        <a href="#" class="text-danger" onclick="deleteProperty(<?php echo $property['id']; ?>)">Delete</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-end">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&show=<?php echo $limit; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this property?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let propertyToDelete = null;
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

        function deleteProperty(id) {
            propertyToDelete = id;
            deleteModal.show();
        }

        function confirmDelete() {
            if (propertyToDelete) {
                fetch(`delete-property.php?id=${propertyToDelete}`, {
                    method: 'DELETE'
                }).then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting property');
                    }
                });
            }
            deleteModal.hide();
        }

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

        // Handle filters
        document.querySelectorAll('.filter-dropdown').forEach(select => {
            select.addEventListener('change', (e) => {
                const url = new URL(window.location);
                if (e.target.value === 'all') {
                    url.searchParams.delete('status');
                } else {
                    url.searchParams.set(e.target.classList.contains('me-2') ? 'status' : 'show', e.target.value);
                }
                url.searchParams.set('page', '1');
                window.location = url;
            });
        });

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