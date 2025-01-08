<?php
session_start();

// Check if user is logged in and is an agent
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] !== 'agent') {
    header("Location: buyer/home.php");
    exit;
}

require_once "config/database.php";

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize stats array with all required keys
$stats = [
    'total' => 0,
    'featured' => 0,
    'available' => 0,
    'rental' => 0
];

try {
    // Total Properties
    $stmt = $db->query("SELECT COUNT(*) FROM properties");
    $stats['total'] = $stmt->fetchColumn();

    // Featured Properties
    $stmt = $db->query("SELECT COUNT(*) FROM properties WHERE is_featured = 1");
    $stats['featured'] = $stmt->fetchColumn();

    // Available Properties
    $stmt = $db->query("SELECT COUNT(*) FROM properties WHERE status = 'available'");
    $stats['available'] = (int)$stmt->fetchColumn();

    // Rental Properties
    $stmt = $db->query("SELECT COUNT(*) FROM properties WHERE status = 'rented'");
    $stats['rental'] = $stmt->fetchColumn();
} catch (PDOException $e) {
    // Handle any database errors
    error_log($e->getMessage());
}

// My Favorites (this would typically be user-specific)
$stats['favorites'] = 12; // Placeholder value
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - RealEstate</title>
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
        .main-wrapper {
            margin-left: 250px;
            padding: 24px;
        }
        .sidebar .nav-link {
            color: #8a8b9f;
            padding: 12px 16px;
            margin: 4px 0;
            border-radius: 8px;
            transition: all 0.2s;
            font-size: 14px;
            display: flex;
            align-items: center;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: #2d303a;
            color: #fff;
        }
        .sidebar .nav-link i {
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
        .stats-card {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            height: 100%;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
            transition: all 0.3s ease;
            position: relative;
        }
        .hover-effect:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .stats-card .icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 16px;
        }
        .stats-card .title {
            color: #A3AED0;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
        }
        .stats-card .value {
            color: #2B3674;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 16px;
        }
        .view-more {
            color: #4318FF;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .stats-card:hover .view-more {
            opacity: 1;
        }
        .icon-total { background: rgba(67, 24, 255, 0.1); color: #4318FF; }
        .icon-featured { background: rgba(255, 181, 71, 0.1); color: #FFB547; }
        .icon-favorites { background: rgba(0, 176, 135, 0.1); color: #00B087; }
        .icon-rental { background: rgba(108, 93, 211, 0.1); color: #6C5DD3; }
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
        .profile-icon {
            width: 40px;
            height: 40px;
            background: #f1f3f4;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
        }
        .icon-available { background: rgba(0, 176, 135, 0.1); color: #00B087; }
        .icon-performance { background: rgba(255, 86, 48, 0.1); color: #FF5630; }
        .stats-card .value.performance {
            color: #FF5630;
            font-size: 18px;
        }
        .main-wrapper {
            margin-left: 250px;
            padding: 24px;
            max-width: 1400px;
        }
        .stats-card {
            min-height: 180px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .dropdown-menu {
            background-color: #fff;
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 8px 0;
            margin-top: 8px;
        }
        .dropdown-item {
            color: #1a1c23;
            padding: 8px 16px;
            font-size: 14px;
            transition: all 0.2s;
        }
        .dropdown-item:hover {
            background-color: #f8f9fa;
            color: #0066FF;
        }
        .dropdown-divider {
            margin: 4px 0;
            border-color: #e2e8f0;
        }
        .fa-user-circle {
            color: #718096;
        }
        .nav-link {
            padding: 8px;
        }
        .nav-link:hover .fa-user-circle {
            color: #0066FF;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="brand-title">RealEstate</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="index.php">
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
                <a class="nav-link" href="properties.php?status=available">
                    <i class="fas fa-check-circle"></i>
                    Available Properties
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="properties.php?status=rented">
                    <i class="fas fa-building"></i>
                    Rental Properties
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
            <h1 class="page-title">Dashboard</h1>
            <div class="d-flex align-items-center">
                <div class="dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle fa-lg"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                        <li><a class="dropdown-item" href="manage-users.php">Manage Users</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Total Properties -->
            <div class="col-md-4 mb-4">
                <a href="properties.php" class="text-decoration-none">
                    <div class="stats-card hover-effect">
                        <div class="icon icon-total">
                            <i class="fas fa-home"></i>
                        </div>
                        <div class="title">Total Properties</div>
                        <div class="value"><?php echo $stats['total']; ?></div>
                        <div class="view-more">
                            <span>View Details</span>
                            <i class="fas fa-arrow-right ms-2"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Featured Properties -->
            <div class="col-md-4 mb-4">
                <a href="Featured-properties.php" class="text-decoration-none">
                    <div class="stats-card hover-effect">
                        <div class="icon icon-featured">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="title">Featured Properties</div>
                        <div class="value"><?php echo $stats['featured']; ?></div>
                        <div class="view-more">
                            <span>View Details</span>
                            <i class="fas fa-arrow-right ms-2"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Available Properties -->
            <div class="col-md-4 mb-4">
                <a href="properties.php?status=available" class="text-decoration-none">
                    <div class="stats-card hover-effect">
                        <div class="icon icon-available">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="title">Available Properties</div>
                        <div class="value"><?php echo $stats['available']; ?></div>
                        <div class="view-more">
                            <span>View Details</span>
                            <i class="fas fa-arrow-right ms-2"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Rental Properties -->
            <div class="col-md-6 mb-4">
                <a href="properties.php?status=rented" class="text-decoration-none">
                    <div class="stats-card hover-effect">
                        <div class="icon icon-rental">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="title">Rental Properties</div>
                        <div class="value"><?php echo $stats['rental']; ?></div>
                        <div class="view-more">
                            <span>View Details</span>
                            <i class="fas fa-arrow-right ms-2"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Featured Properties Performance -->
            <div class="col-md-6 mb-4">
                <a href="reports.php?report_type=featured_performance" class="text-decoration-none">
                    <div class="stats-card hover-effect">
                        <div class="icon icon-performance">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="title">Featured Properties Performance</div>
                        <div class="value">View Report</div>
                        <div class="view-more">
                            <span>View Analytics</span>
                            <i class="fas fa-arrow-right ms-2"></i>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        
    </div>
    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 