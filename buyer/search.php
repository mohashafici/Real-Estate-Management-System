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

// Get search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;
$location = isset($_GET['location']) ? $_GET['location'] : '';
$property_type = isset($_GET['property_type']) ? $_GET['property_type'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 9; // Properties per page
$offset = ($page - 1) * $limit;

// Build query
$query = "SELECT * FROM properties WHERE status = 'available'";
$params = array();

if (!empty($search)) {
    $query .= " AND (title LIKE :search OR description LIKE :search)";
    $params[':search'] = "%{$search}%";
}

if (!empty($location)) {
    $query .= " AND location LIKE :location";
    $params[':location'] = "%{$location}%";
}

if (!empty($property_type)) {
    $query .= " AND property_type = :property_type";
    $params[':property_type'] = $property_type;
}

if ($min_price !== null) {
    $query .= " AND price >= :min_price";
    $params[':min_price'] = $min_price;
}

if ($max_price !== null) {
    $query .= " AND price <= :max_price";
    $params[':max_price'] = $max_price;
}

$query .= " ORDER BY added_on DESC LIMIT :limit OFFSET :offset";
$params[':limit'] = $limit;
$params[':offset'] = $offset;

// Prepare and execute query
$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    if (in_array($key, [':limit', ':offset'])) {
        $stmt->bindValue($key, $value, PDO::PARAM_INT);
    } else {
        $stmt->bindValue($key, $value);
    }
}
$stmt->execute();

// Get total count for pagination
$count_query = str_replace("SELECT *", "SELECT COUNT(*)", substr($query, 0, strpos($query, " LIMIT")));
$count_stmt = $db->prepare($count_query);
foreach ($params as $key => $value) {
    if (!in_array($key, [':limit', ':offset'])) {
        $count_stmt->bindValue($key, $value);
    }
}
$count_stmt->execute();
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Get distinct locations for filter
$locations_query = "SELECT DISTINCT location FROM properties WHERE status = 'available' ORDER BY location";
$locations_stmt = $db->query($locations_query);
$locations = $locations_stmt->fetchAll(PDO::FETCH_COLUMN);

// Get distinct property types for filter
$types_query = "SELECT DISTINCT property_type FROM properties WHERE status = 'available' ORDER BY property_type";
$types_stmt = $db->query($types_query);
$property_types = $types_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Properties - RealEstate</title>
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
        .filter-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
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
        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
        }
        .form-control, .form-select {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 16px;
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

    <div class="container py-5">
        <div class="row">
            <div class="col-lg-3">
                <div class="filter-card">
                    <h5 class="mb-4">Filter Properties</h5>
                    <form action="search.php" method="GET">
                        <div class="mb-3">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search properties...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <select class="form-select" name="location">
                                <option value="">All Locations</option>
                                <?php foreach ($locations as $loc): ?>
                                <option value="<?php echo htmlspecialchars($loc); ?>" <?php echo $location === $loc ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($loc); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Property Type</label>
                            <select class="form-select" name="property_type">
                                <option value="">All Types</option>
                                <?php foreach ($property_types as $type): ?>
                                <option value="<?php echo htmlspecialchars($type); ?>" <?php echo $property_type === $type ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price Range</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" class="form-control" name="min_price" value="<?php echo $min_price; ?>" placeholder="Min">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control" name="max_price" value="<?php echo $max_price; ?>" placeholder="Max">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-9">
            <div class="row">
                    <?php if ($stmt->rowCount() > 0): ?>
                        <?php while ($property = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="property-card">
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
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                    <div class="col-12">
                            <div class="alert alert-info">
                                No properties found matching your criteria.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&location=<?php echo urlencode($location); ?>&property_type=<?php echo urlencode($property_type); ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price; ?>">
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
</body>
</html> 