<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Agent - Real Estate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .contact-form {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .property-select {
            max-height: 200px;
            overflow-y: auto;
        }
        .property-option {
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .property-option:hover {
            background-color: #f8f9fa;
        }
        .property-option.selected {
            background-color: #e7f1ff;
            border-color: #0d6efd;
        }
        .property-option img {
            width: 100px;
            height: 70px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
        }
        .property-info {
            font-size: 14px;
        }
        .required-field::after {
            content: "*";
            color: red;
            margin-left: 4px;
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
                        <a class="nav-link" href="property-listing.php">Property Listing</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="favorites.php">
                            <i class="fas fa-heart"></i> Favorites
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="contact.php">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contact Section -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="contact-form">
                    <h2 class="text-center mb-4">Contact Agent</h2>
                    
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
                        require_once('../config/database.php');
                        $database = new Database();
                        $conn = $database->getConnection();

                        // Get form data
                        $name = trim($_POST['name']);
                        $email = trim($_POST['email']);
                        $phone = trim($_POST['phone']);
                        $property_id = intval($_POST['property_id']);
                        $message = trim($_POST['message']);
                        $preferred_contact = $_POST['contact_method'];

                        try {
                            // Insert into inquiries table
                            $query = "INSERT INTO inquiries (name, email, phone, property_id, message, preferred_contact, status) 
                                     VALUES (:name, :email, :phone, :property_id, :message, :preferred_contact, 'new')";
                            
                            $stmt = $conn->prepare($query);
                            $stmt->bindParam(':name', $name);
                            $stmt->bindParam(':email', $email);
                            $stmt->bindParam(':phone', $phone);
                            $stmt->bindParam(':property_id', $property_id);
                            $stmt->bindParam(':message', $message);
                            $stmt->bindParam(':preferred_contact', $preferred_contact);
                            
                            if ($stmt->execute()) {
                                echo '<div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    Thank you for your message! Our agent will contact you soon.
                                </div>';
                            } else {
                                echo '<div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    Sorry, there was an error sending your message. Please try again.
                                </div>';
                            }
                        } catch (PDOException $e) {
                            echo '<div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                Database error. Please try again later.
                            </div>';
                        }
                    }
                    ?>

                    <form method="POST" action="contact.php" id="contactForm">
                        <div class="mb-3">
                            <label for="name" class="form-label required-field">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label required-field">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label required-field">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required-field">Interested Property</label>
                            <div class="property-select">
                                <?php
                                require_once('../config/database.php');
                                $database = new Database();
                                $conn = $database->getConnection();

                                // Fetch only available properties
                                $query = "SELECT id, title, price, location, photo FROM properties WHERE status = 'available' ORDER BY added_on DESC";
                                $stmt = $conn->prepare($query);
                                $stmt->execute();

                                while ($property = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    ?>
                                    <div class="property-option d-flex align-items-center" onclick="selectProperty(this, <?php echo $property['id']; ?>)">
                                        <img src="../uploads/<?php echo htmlspecialchars($property['photo']); ?>" alt="<?php echo htmlspecialchars($property['title']); ?>">
                                        <div class="property-info">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($property['title']); ?></h6>
                                            <p class="mb-0">
                                                <strong>Price:</strong> $<?php echo number_format($property['price']); ?><br>
                                                <strong>Location:</strong> <?php echo htmlspecialchars($property['location']); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <input type="hidden" name="property_id" id="property_id" required>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label required-field">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Preferred Contact Method</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="contact_method" id="email_method" value="email" checked>
                                <label class="form-check-label" for="email_method">Email</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="contact_method" id="phone_method" value="phone">
                                <label class="form-check-label" for="phone_method">Phone</label>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" name="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Send Message
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Contact Information -->
                <div class="mt-5 text-center">
                    <h3>Other Ways to Reach Us</h3>
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <i class="fas fa-phone fa-2x mb-3 text-primary"></i>
                            <h5>Phone</h5>
                            <p><a href="tel:+1234567890" class="text-decoration-none">+1 (234) 567-890</a></p>
                        </div>
                        <div class="col-md-4">
                            <i class="fas fa-envelope fa-2x mb-3 text-primary"></i>
                            <h5>Email</h5>
                            <p><a href="mailto:info@realestate.com" class="text-decoration-none">info@realestate.com</a></p>
                        </div>
                        <div class="col-md-4">
                            <i class="fas fa-map-marker-alt fa-2x mb-3 text-primary"></i>
                            <h5>Office</h5>
                            <p>123 Real Estate Street<br>City, State 12345</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function selectProperty(element, propertyId) {
        // Remove selected class from all options
        document.querySelectorAll('.property-option').forEach(option => {
            option.classList.remove('selected');
        });
        
        // Add selected class to clicked option
        element.classList.add('selected');
        
        // Set the property ID in hidden input
        document.getElementById('property_id').value = propertyId;
    }

    // Form validation
    document.getElementById('contactForm').addEventListener('submit', function(e) {
        if (!document.getElementById('property_id').value) {
            e.preventDefault();
            alert('Please select a property you are interested in.');
        }
    });
    </script>
</body>
</html> 