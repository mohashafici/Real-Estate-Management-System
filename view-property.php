<?php
require_once "config/database.php";

// Get property ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: properties.php');
    exit;
}

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Get property details
$stmt = $db->prepare("SELECT * FROM properties WHERE id = ?");
$stmt->execute([$id]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$property) {
    header('Location: properties.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Property - <?php echo htmlspecialchars($property['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .property-header {
            background: white;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .property-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 24px;
        }
        .property-info {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .info-item {
            margin-bottom: 16px;
        }
        .info-label {
            color: #718096;
            font-size: 14px;
            margin-bottom: 4px;
        }
        .info-value {
            font-weight: 500;
            color: #2D3748;
        }
        .badge {
            padding: 6px 12px;
            font-weight: 500;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="property-header">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0"><?php echo htmlspecialchars($property['title']); ?></h1>
                <div>
                    <a href="edit-property.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">Edit Property</a>
                    <a href="properties.php" class="btn btn-light ms-2">Back to List</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <img src="uploads/<?php echo htmlspecialchars($property['photo']); ?>" 
                     alt="<?php echo htmlspecialchars($property['title']); ?>" 
                     class="property-image">
            </div>
            <div class="col-md-4">
                <div class="property-info">
                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            <span class="badge <?php echo $property['status'] === 'sold' ? 'bg-danger' : 'bg-success'; ?>">
                                <?php echo ucfirst($property['status']); ?>
                            </span>
                            <?php if ($property['is_featured']): ?>
                            <span class="badge bg-warning ms-2">Featured</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Price</div>
                        <div class="info-value text-success h4">$<?php echo number_format($property['price']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Location</div>
                        <div class="info-value"><?php echo htmlspecialchars($property['location']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Property Details</div>
                        <div class="d-flex gap-4 mt-2">
                            <div class="text-center">
                                <i class="fas fa-bed mb-2"></i>
                                <div class="small"><?php echo $property['bedrooms']; ?> Beds</div>
                            </div>
                            <div class="text-center">
                                <i class="fas fa-bath mb-2"></i>
                                <div class="small"><?php echo $property['bathrooms']; ?> Baths</div>
                            </div>
                            <div class="text-center">
                                <i class="fas fa-vector-square mb-2"></i>
                                <div class="small"><?php echo number_format($property['area']); ?> sqft</div>
                            </div>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Description</div>
                        <div class="info-value"><?php echo nl2br(htmlspecialchars($property['description'])); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Added On</div>
                        <div class="info-value"><?php echo date('F j, Y', strtotime($property['added_on'])); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 