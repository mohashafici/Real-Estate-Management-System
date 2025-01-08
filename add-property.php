<?php
require_once "config/database.php";

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = new Database();
        $db = $database->getConnection();

        // Handle file upload
        $photo = '';
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
        }

        // Insert property
        $stmt = $db->prepare("
            INSERT INTO properties (
                title, description, bedrooms, bathrooms, 
                area, price, location, status, 
                property_status, is_featured, photo
            ) VALUES (
                :title, :description, :bedrooms, :bathrooms,
                :area, :price, :location, :status,
                :property_status, :is_featured, :photo
            )
        ");

        $result = $stmt->execute([
            ':title' => $_POST['title'],
            ':description' => $_POST['description'],
            ':bedrooms' => $_POST['bedrooms'],
            ':bathrooms' => $_POST['bathrooms'],
            ':area' => $_POST['area'],
            ':price' => $_POST['price'],
            ':location' => $_POST['location'],
            ':status' => $_POST['status'],
            ':property_status' => 'pending',
            ':is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            ':photo' => $photo
        ]);

        if ($result) {
            // Redirect to properties page after successful submission
            header('Location: properties.php?property_status=pending');
            exit;
        } else {
            throw new Exception('Failed to add property.');
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Property - RealEstate Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .form-container {
            background: #fff;
            border-radius: 12px;
            padding: 32px;
            margin: 32px auto;
            max-width: 800px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .form-label {
            font-weight: 500;
            color: #2B3674;
            margin-bottom: 8px;
        }
        .form-control, .form-select {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px 16px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #4318FF;
            box-shadow: 0 0 0 3px rgba(67, 24, 255, 0.1);
        }
        .btn-primary {
            background: #4318FF;
            border: none;
            padding: 12px 24px;
            font-weight: 500;
        }
        .btn-primary:hover {
            background: #3515CC;
        }
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            border-radius: 8px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1 class="h3 mb-4">Add New Property</h1>

            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control" name="location" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="4" required></textarea>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Bedrooms</label>
                        <input type="number" class="form-control" name="bedrooms" required min="0">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Bathrooms</label>
                        <input type="number" class="form-control" name="bathrooms" required min="0">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Area (sqft)</label>
                        <input type="number" class="form-control" name="area" required min="0">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Price ($)</label>
                        <input type="number" class="form-control" name="price" required min="0">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="available">Available</option>
                            <option value="sold">Sold</option>
                            <option value="rented">Rented</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Property Photo</label>
                        <input type="file" class="form-control" name="photo" accept="image/*" required>
                        <img id="preview" class="preview-image mt-2">
                    </div>
                </div>

                <div class="mb-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="is_featured" id="is_featured">
                        <label class="form-check-label" for="is_featured">Mark as Featured Property</label>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="properties.php" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary">Add Property</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Image preview
        document.querySelector('input[type="file"]').addEventListener('change', function(e) {
            const preview = document.getElementById('preview');
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html> 