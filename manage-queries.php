<?php
require_once "config/database.php";

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Handle Response Submission
if (isset($_POST['submit_response'])) {
    $inquiry_id = intval($_POST['inquiry_id']);
    $response = trim($_POST['response']);
    $response_date = date('Y-m-d H:i:s');
    
    $update_query = "UPDATE inquiries SET 
                    response = :response,
                    response_date = :response_date,
                    status = 'responded'
                    WHERE id = :id";
    
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':response', $response);
    $update_stmt->bindParam(':response_date', $response_date);
    $update_stmt->bindParam(':id', $inquiry_id);
    
    if ($update_stmt->execute()) {
        header('Location: manage-queries.php?msg=responded');
        exit;
    }
}

// Handle Delete Action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $delete_query = "DELETE FROM inquiries WHERE id = :id";
    $delete_stmt = $db->prepare($delete_query);
    $delete_stmt->bindParam(':id', $id);
    $delete_stmt->execute();
    header('Location: manage-queries.php?msg=deleted');
    exit;
}

// Handle Status Update
if (isset($_POST['update_status'])) {
    $id = intval($_POST['inquiry_id']);
    $status = $_POST['status'];
    $update_query = "UPDATE inquiries SET status = :status WHERE id = :id";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':status', $status);
    $update_stmt->bindParam(':id', $id);
    $update_stmt->execute();
    header('Location: manage-queries.php?msg=updated');
    exit;
}

// Fetch Queries with Property Information
$query = "SELECT i.*, p.title as property_title 
          FROM inquiries i 
          LEFT JOIN properties p ON i.property_id = p.id 
          ORDER BY i.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Queries - RealEstate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 32px;
            padding: 0 16px;
        }
        .query-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-new { background: #E5F6FD; color: #0098DA; }
        .status-inprogress { background: #FFF3E5; color: #FFB547; }
        .status-responded { background: #E6F6F4; color: #00B087; }
        .response-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        .response-date {
            font-size: 12px;
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
                <a class="nav-link" href="Featured-properties.php">
                    <i class="far fa-heart"></i>
                    Featured Properties
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="manage-queries.php">
                    <i class="far fa-comment-dots"></i>
                    Manage Queries
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="reports.php">
                    <i class="fas fa-chart-bar"></i>
                    Reports
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
    <div class="container-fluid py-4" style="margin-left: 250px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h4 mb-0">Manage Queries</h1>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <?php if ($_GET['msg'] == 'deleted'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Query deleted successfully.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($_GET['msg'] == 'updated'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Query status updated successfully.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($_GET['msg'] == 'responded'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Response sent successfully.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php while ($query = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <div class="query-card">
                <div class="row">
                    <div class="col-md-4">
                        <h5 class="mb-1"><?php echo htmlspecialchars($query['name']); ?></h5>
                        <p class="mb-2">
                            <i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($query['email']); ?><br>
                            <i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($query['phone']); ?>
                        </p>
                        <p class="mb-0">
                            <small class="text-muted">
                                Preferred Contact: <?php echo ucfirst($query['preferred_contact']); ?>
                            </small>
                        </p>
                    </div>
                    <div class="col-md-4">
                        <h6>Property Interest</h6>
                        <p class="mb-0"><?php echo htmlspecialchars($query['property_title']); ?></p>
                        <p class="mb-0"><small class="text-muted">Message:</small><br>
                        <?php echo htmlspecialchars($query['message']); ?></p>
                        
                        <?php if (!empty($query['response'])): ?>
                            <div class="response-box">
                                <strong>Your Response:</strong><br>
                                <?php echo htmlspecialchars($query['response']); ?>
                                <div class="response-date">
                                    Responded on: <?php echo date('M d, Y H:i', strtotime($query['response_date'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-2">
                        <span class="status-badge status-<?php echo strtolower($query['status']); ?>">
                            <?php echo ucfirst($query['status']); ?>
                        </span>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex flex-column gap-2">
                            <?php if ($query['status'] !== 'responded'): ?>
                                <button type="button" 
                                        class="btn btn-sm btn-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#responseModal<?php echo $query['id']; ?>">
                                    <i class="fas fa-reply me-1"></i> Respond
                                </button>
                            <?php endif; ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="inquiry_id" value="<?php echo $query['id']; ?>">
                                <select name="status" class="form-select form-select-sm mb-2" onchange="this.form.submit()">
                                    <option value="new" <?php echo $query['status'] == 'new' ? 'selected' : ''; ?>>New</option>
                                    <option value="inprogress" <?php echo $query['status'] == 'inprogress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="responded" <?php echo $query['status'] == 'responded' ? 'selected' : ''; ?>>Responded</option>
                                </select>
                                <input type="hidden" name="update_status" value="1">
                            </form>
                            <a href="manage-queries.php?action=delete&id=<?php echo $query['id']; ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Are you sure you want to delete this query?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Response Modal -->
            <div class="modal fade" id="responseModal<?php echo $query['id']; ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Respond to Query</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="inquiry_id" value="<?php echo $query['id']; ?>">
                                <div class="mb-3">
                                    <label class="form-label">Your Response</label>
                                    <textarea name="response" class="form-control" rows="5" required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" name="submit_response" class="btn btn-primary">Send Response</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 