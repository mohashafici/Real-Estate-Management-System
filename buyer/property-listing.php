<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Listings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        .property-card {
            transition: transform 0.2s;
            position: relative;
        }
        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .favorite-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            padding: 8px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s;
            z-index: 1;
        }
        .favorite-btn:hover {
            background: rgba(255, 255, 255, 1);
            transform: scale(1.1);
        }
        .favorite-btn i {
            color: #dc3545;
            font-size: 20px;
        }
        .favorite-btn.active i {
            animation: heartBeat 0.3s;
        }
        @keyframes heartBeat {
            0% { transform: scale(1); }
            50% { transform: scale(1.3); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
            <a class="navbar-brand" href="home.php">Real Estate</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="property-listing.php">Property Listing</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="favorites.php">
                            <i class="fas fa-heart"></i> Favorites
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
                <form class="d-flex" role="search" method="GET" action="search.php">
                    <input class="form-control me-2" type="search" name="search" placeholder="Search properties" aria-label="Search">
                    <button class="btn btn-outline-success" type="submit">Search</button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Property Listing Section -->
    <div class="container mt-4">
        <h2 class="mb-4">All Properties</h2>
        
        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-md-12">
                <form method="GET" class="row g-3">
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">Property Status</option>
                            <option value="available" <?php echo (isset($_GET['status']) && $_GET['status'] == 'available') ? 'selected' : ''; ?>>Available</option>
                            <option value="sold" <?php echo (isset($_GET['status']) && $_GET['status'] == 'sold') ? 'selected' : ''; ?>>Sold</option>
                            <option value="rented" <?php echo (isset($_GET['status']) && $_GET['status'] == 'rented') ? 'selected' : ''; ?>>Rented</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="bedrooms" class="form-select">
                            <option value="">Bedrooms</option>
                            <option value="1" <?php echo (isset($_GET['bedrooms']) && $_GET['bedrooms'] == '1') ? 'selected' : ''; ?>>1</option>
                            <option value="2" <?php echo (isset($_GET['bedrooms']) && $_GET['bedrooms'] == '2') ? 'selected' : ''; ?>>2</option>
                            <option value="3" <?php echo (isset($_GET['bedrooms']) && $_GET['bedrooms'] == '3') ? 'selected' : ''; ?>>3</option>
                            <option value="4" <?php echo (isset($_GET['bedrooms']) && $_GET['bedrooms'] == '4') ? 'selected' : ''; ?>>4+</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="price_range" class="form-select">
                            <option value="">Price Range</option>
                            <option value="0-500000" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] == '0-500000') ? 'selected' : ''; ?>>Under $500,000</option>
                            <option value="500000-1000000" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] == '500000-1000000') ? 'selected' : ''; ?>>$500,000 - $1,000,000</option>
                            <option value="1000000+" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] == '1000000+') ? 'selected' : ''; ?>>Above $1,000,000</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="location" class="form-control" placeholder="Location" value="<?php echo isset($_GET['location']) ? htmlspecialchars($_GET['location']) : ''; ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <?php if (!empty($_GET)) { ?>
                            <a href="property-listing.php" class="btn btn-outline-secondary">Clear Filters</a>
                        <?php } ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Property Grid -->
        <div class="row">
            <?php
            require_once('../config/database.php');
            $database = new Database();
            $conn = $database->getConnection();

            // For favorites functionality
            $user_id = 1; // Fixed user ID for now
            
            // Get user's favorites
            $favorites_query = "SELECT property_id FROM favorites WHERE user_id = :user_id";
            $favorites_stmt = $conn->prepare($favorites_query);
            $favorites_stmt->bindParam(':user_id', $user_id);
            $favorites_stmt->execute();
            $favorites = $favorites_stmt->fetchAll(PDO::FETCH_COLUMN, 0);

            // Build query based on filters
            $where_conditions = [];
            $params = array();
            
            if (isset($_GET['status']) && !empty($_GET['status'])) {
                $where_conditions[] = "status = :status";
                $params[':status'] = $_GET['status'];
            }
            
            if (isset($_GET['bedrooms']) && !empty($_GET['bedrooms'])) {
                $bedrooms = intval($_GET['bedrooms']);
                if ($bedrooms == 4) {
                    $where_conditions[] = "bedrooms >= :bedrooms";
                    $params[':bedrooms'] = 4;
                } else {
                    $where_conditions[] = "bedrooms = :bedrooms";
                    $params[':bedrooms'] = $bedrooms;
                }
            }
            
            if (isset($_GET['location']) && !empty($_GET['location'])) {
                $where_conditions[] = "location LIKE :location";
                $params[':location'] = '%' . $_GET['location'] . '%';
            }
            
            if (isset($_GET['price_range']) && !empty($_GET['price_range'])) {
                $price_range = explode('-', $_GET['price_range']);
                if (count($price_range) == 2) {
                    $where_conditions[] = "price BETWEEN :price_min AND :price_max";
                    $params[':price_min'] = intval($price_range[0]);
                    $params[':price_max'] = intval($price_range[1]);
                } elseif (strpos($_GET['price_range'], '+') !== false) {
                    $min_price = intval($price_range[0]);
                    $where_conditions[] = "price >= :price_min";
                    $params[':price_min'] = $min_price;
                }
            }

            $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
            $query = "SELECT * FROM properties $where_clause ORDER BY added_on DESC";
            $stmt = $conn->prepare($query);
            $stmt->execute($params);

            if ($stmt->rowCount() > 0) {
                while ($property = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $is_favorited = in_array($property['id'], $favorites);
                    ?>
                    <div class="col-md-4 mb-4">
                        <div class="card property-card">
                            <button class="favorite-btn" onclick="toggleFavorite(<?php echo $property['id']; ?>)" title="<?php echo $is_favorited ? 'Remove from favorites' : 'Add to favorites'; ?>">
                                <i class="fa-heart <?php echo $is_favorited ? 'fa-solid' : 'fa-regular'; ?>" id="heart-<?php echo $property['id']; ?>"></i>
                            </button>
                            <img src="../uploads/<?php echo htmlspecialchars($property['photo']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($property['title']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($property['title']); ?></h5>
                                <p class="card-text">
                                    <strong>Price:</strong> $<?php echo number_format($property['price']); ?><br>
                                    <strong>Location:</strong> <?php echo htmlspecialchars($property['location']); ?><br>
                                    <strong>Bedrooms:</strong> <?php echo $property['bedrooms']; ?><br>
                                    <strong>Bathrooms:</strong> <?php echo $property['bathrooms']; ?><br>
                                    <strong>Area:</strong> <?php echo number_format($property['area']) . ' ' . $property['area_unit']; ?><br>
                                    <strong>Status:</strong> <?php echo ucfirst($property['status']); ?>
                                </p>
                                <a href="property-details.php?id=<?php echo $property['id']; ?>" class="btn btn-primary w-100">View Details</a>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle me-2"></i>
                        No properties found matching your criteria.
                        <?php if (!empty($_GET)) { ?>
                            <br>
                            <a href="property-listing.php" class="btn btn-outline-primary mt-2">Clear All Filters</a>
                        <?php } ?>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function toggleFavorite(propertyId) {
        const heartIcon = document.getElementById(`heart-${propertyId}`);
        
        // Send AJAX request to save/remove favorite
        fetch('save-favorite.php', {
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
                if (data.action === 'added') {
                    heartIcon.classList.remove('fa-regular');
                    heartIcon.classList.add('fa-solid');
                } else {
                    heartIcon.classList.remove('fa-solid');
                    heartIcon.classList.add('fa-regular');
                }
                
                // Add animation class
                const btn = heartIcon.closest('.favorite-btn');
                btn.classList.add('active');
                
                // Remove animation class after animation completes
                setTimeout(() => {
                    btn.classList.remove('active');
                }, 300);
            }
        });
    }
    </script>
</body>
</html> 