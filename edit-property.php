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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Handle file upload if new photo is provided
        $photo = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($file_extension, $allowed_extensions)) {
                throw new Exception('Invalid file type. Only JPG, PNG and GIF are allowed.');
            }

            $photo = uniqid() . '.' . $file_extension;
            $target_path = $upload_dir . $photo;

            if (!move_uploaded_file($_FILES['photo']['tmp_name'], $target_path)) {
                throw new Exception('Failed to upload image.');
            }

            // Delete old photo if exists
            $stmt = $db->prepare("SELECT photo FROM properties WHERE id = ?");
            $stmt->execute([$id]);
            $old_photo = $stmt->fetchColumn();
            if ($old_photo && file_exists($upload_dir . $old_photo)) {
                unlink($upload_dir . $old_photo);
            }
        }

        // Update property
        $sql = "UPDATE properties SET 
                title = :title,
                description = :description,
                bedrooms = :bedrooms,
                bathrooms = :bathrooms,
                area = :area,
                price = :price,
                location = :location,
                status = :status,
                is_featured = :is_featured";

        if ($photo) {
            $sql .= ", photo = :photo";
        }

        $sql .= " WHERE id = :id";

        $stmt = $db->prepare($sql);
        $params = [
            ':title' => $_POST['title'],
            ':description' => $_POST['description'],
            ':bedrooms' => $_POST['bedrooms'],
            ':bathrooms' => $_POST['bathrooms'],
            ':area' => $_POST['area'],
            ':price' => $_POST['price'],
            ':location' => $_POST['location'],
            ':status' => $_POST['status'],
            ':is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            ':id' => $id
        ];

        if ($photo) {
            $params[':photo'] = $photo;
        }

        if ($stmt->execute($params)) {
            header('Location: properties.php');
            exit;
        } else {
            throw new Exception('Failed to update property.');
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

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
    <title>Edit Property - <?php echo htmlspecialchars($property['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .form-container {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .form-label {
            color: #718096;
            font-weight: 500;
        }
        .preview-image {
            max-width: 200px;
            border-radius: 8px;
            margin-top: 12px;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Edit Property</h1>
            <a href="properties.php" class="btn btn-light">Back to List</a>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($property['title']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control" name="location" value="<?php echo htmlspecialchars($property['location']); ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="4" required><?php echo htmlspecialchars($property['description']); ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Bedrooms</label>
                        <input type="number" class="form-control" name="bedrooms" value="<?php echo $property['bedrooms']; ?>" required min="0">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Bathrooms</label>
                        <input type="number" class="form-control" name="bathrooms" value="<?php echo $property['bathrooms']; ?>" required min="0">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Area (sqft)</label>
                        <input type="number" class="form-control" name="area" value="<?php echo $property['area']; ?>" required min="0">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Price ($)</label>
                        <input type="number" class="form-control" name="price" value="<?php echo $property['price']; ?>" required min="0">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="available" <?php echo $property['status'] === 'available' ? 'selected' : ''; ?>>Available</option>
                            <option value="sold" <?php echo $property['status'] === 'sold' ? 'selected' : ''; ?>>Sold</option>
                            <option value="rented" <?php echo $property['status'] === 'rented' ? 'selected' : ''; ?>>Rented</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Property Photo</label>
                        <input type="file" class="form-control" name="photo" accept="image/*">
                        <?php if ($property['photo']): ?>
                            <img src="uploads/<?php echo htmlspecialchars($property['photo']); ?>" alt="Current photo" class="preview-image">
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="is_featured" id="is_featured" <?php echo $property['is_featured'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_featured">Mark as Featured Property</label>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="properties.php" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Property</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Image preview
        document.querySelector('input[type="file"]').addEventListener('change', function(e) {
            const preview = document.querySelector('.preview-image');
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (preview) {
                        preview.src = e.target.result;
                    } else {
                        const newPreview = document.createElement('img');
                        newPreview.src = e.target.result;
                        newPreview.classList.add('preview-image');
                        document.querySelector('input[type="file"]').parentNode.appendChild(newPreview);
                    }
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html> 