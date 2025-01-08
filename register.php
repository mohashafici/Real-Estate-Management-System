<?php
session_start();
require_once "config/database.php";

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'agent') {
        header("Location: index.php");
    } else {
        header("Location: buyer/home.php");
    }
    exit;
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $db = $database->getConnection();
    
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    
    // Validate password match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Check if email already exists
        $query = "SELECT id FROM users WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            $error = "Email already exists";
        } else {
            // Hash password and insert user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (username, email, password, role, created_at) VALUES (:username, :email, :password, :role, NOW())";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":password", $hashed_password);
            $stmt->bindParam(":role", $role);
            
            if ($stmt->execute()) {
                $success = "Registration successful! Please login.";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - RealEstate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }
        .register-container {
            max-width: 450px;
            width: 100%;
            padding: 40px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .register-title {
            font-size: 24px;
            font-weight: 600;
            color: #2B3674;
            margin-bottom: 32px;
            text-align: center;
        }
        .form-control {
            height: 48px;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 16px;
        }
        .btn-primary {
            height: 48px;
            font-weight: 500;
            width: 100%;
        }
        .login-link {
            text-align: center;
            margin-top: 24px;
        }
        .role-selector {
            display: flex;
            gap: 16px;
            margin-bottom: 16px;
        }
        .role-option {
            flex: 1;
            text-align: center;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .role-option.active {
            background-color: #0066FF;
            color: white;
            border-color: #0066FF;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h1 class="register-title">Create Account</h1>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="mb-3">
                <input type="text" class="form-control" name="username" placeholder="Username" required>
            </div>
            <div class="mb-3">
                <input type="email" class="form-control" name="email" placeholder="Email" required>
            </div>
            <div class="mb-3">
                <input type="password" class="form-control" name="password" placeholder="Password" required>
            </div>
            <div class="mb-3">
                <input type="password" class="form-control" name="confirm_password" placeholder="Confirm Password" required>
            </div>
            <div class="role-selector">
                <label class="role-option">
                    <input type="radio" name="role" value="buyer" required checked hidden>
                    <span>Buyer</span>
                </label>
                <label class="role-option">
                    <input type="radio" name="role" value="agent" required hidden>
                    <span>Agent</span>
                </label>
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
        <div class="login-link">
            Already have an account? <a href="login.php">Login</a>
        </div>
    </div>

    <script>
        // Handle role selection styling
        document.querySelectorAll('.role-option').forEach(option => {
            const input = option.querySelector('input');
            if (input.checked) option.classList.add('active');
            
            option.addEventListener('click', () => {
                document.querySelectorAll('.role-option').forEach(opt => opt.classList.remove('active'));
                option.classList.add('active');
            });
        });
    </script>
</body>
</html> 