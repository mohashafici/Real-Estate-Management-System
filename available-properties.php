<?php
require_once "config/database.php";

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Get filters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$limit = isset($_GET['show']) ? (int)$_GET['show'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Build query for available properties
$query = "SELECT * FROM properties WHERE status = 'available'";
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
$count_query = "SELECT COUNT(*) FROM properties WHERE status = 'available'";
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
    <title>Available Properties - RealEstate</title>
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
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 40px;
            padding: 0 12px;
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
        .search-box {
            position: relative;
            max-width: 500px;
        }
        .search-box input {
            padding: 12px 16px 12px 45px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.95rem;
            width: 100%;
        }
        .search-box i {
            position: absolute;
            left: 16px;
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
                <a class="nav-link" href="featured-properties.php">
                    <i class="fas fa-star"></i>
                    Featured Properties
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="available-properties.php">
                    <i class="fas fa-check-circle"></i>
                    Available Properties
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

    <div class="container-fluid py-4" style="margin-left: 250px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h4 mb-0">Available Properties</h1>
        </div>

        <div class="properties-header p-4 bg-white rounded-3 shadow-sm mb-4">
            <div class="d-flex justify-content-between align-items-center gap-3">
                <div class="search-box flex-grow-1">
                    <input 
                        type="text" 
                        class="form-control" 
                        placeholder="Search Available Properties" 
                        value="<?php echo htmlspecialchars($search); ?>"
                    >
                    <i class="fas fa-search"></i>
                </div>
            </div>
        </div>

        <!-- Property listings -->
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
                            <div class="text-success h5 mb-0">$<?php echo number_format($property['price']); ?></div>
                        </div>
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
                    <a class="page-link" href="?page=<?php echo $i; ?>&show=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
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
    </script>
</body>
</html> 