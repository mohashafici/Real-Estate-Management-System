<?php
session_start();

// Check if user is logged in and is an agent
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'agent') {
    header("Location: login.php");
    exit;
}

require_once "config/database.php";

$database = new Database();
$db = $database->getConnection();

// Create reports directory if it doesn't exist
if (!file_exists('reports')) {
    mkdir('reports', 0777, true);
}

// Create reports table if not exists
$create_table_query = "CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_name VARCHAR(255),
    price DECIMAL(10,2),
    type VARCHAR(50),
    location VARCHAR(255),
    status VARCHAR(50),
    total_inquiries INT DEFAULT 0,
    total_sales INT DEFAULT 0,
    total_revenue DECIMAL(10,2) DEFAULT 0,
    report_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$db->exec($create_table_query);

// Handle report generation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $report_type = $_POST['report_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $format = $_POST['format'];

    // Insert data into reports table based on report type
    switch ($report_type) {
        case 'property_sales':
            $insert_query = "INSERT INTO reports (property_name, price, type, location, status, total_sales, total_revenue, report_date)
                           SELECT 
                               p.title,
                               p.price,
                               p.type,
                               p.location,
                               p.status,
                               COUNT(*) as total_sales,
                               SUM(p.price) as total_revenue,
                               CURRENT_DATE
                           FROM properties p 
                           WHERE p.status = 'sold' 
                           AND p.updated_at BETWEEN :start_date AND :end_date
                           GROUP BY p.title, p.price, p.type, p.location, p.status";
            break;
        case 'featured_performance':
            $insert_query = "INSERT INTO reports (property_name, price, type, location, status, total_inquiries, report_date)
                           SELECT 
                               p.title,
                               p.price,
                               p.type,
                               p.location,
                               p.status,
                               COUNT(i.id) as total_inquiries,
                               CURRENT_DATE
                           FROM properties p 
                           LEFT JOIN inquiries i ON p.id = i.property_id
                           WHERE p.is_featured = 1 
                           AND p.created_at BETWEEN :start_date AND :end_date
                           GROUP BY p.title, p.price, p.type, p.location, p.status";
            break;
    }

    if (isset($insert_query)) {
        $stmt = $db->prepare($insert_query);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();
    }

    // Original query for display
    switch ($report_type) {
        case 'property_sales':
            $query = "SELECT 
                     p.title as property_name,
                     p.price,
                     p.status,
                     p.type,
                     p.location,
                     p.updated_at as sale_date
                     FROM properties p 
                     WHERE p.status = 'sold' 
                     AND p.updated_at BETWEEN :start_date AND :end_date
                     ORDER BY p.updated_at DESC";
            break;
        case 'inquiries':
            $query = "SELECT 
                     p.title as property_name,
                     i.name as inquirer_name,
                     i.email as inquirer_email,
                     i.message,
                     i.status as inquiry_status,
                     i.created_at as inquiry_date
                     FROM inquiries i 
                     LEFT JOIN properties p ON i.property_id = p.id 
                     WHERE i.created_at BETWEEN :start_date AND :end_date
                     ORDER BY i.created_at DESC";
            break;
        case 'featured_performance':
            $query = "SELECT 
                     p.title as property_name,
                     p.price,
                     p.type,
                     p.location,
                     p.status,
                     COUNT(i.id) as total_inquiries,
                     p.created_at as listing_date
                     FROM properties p 
                     LEFT JOIN inquiries i ON p.id = i.property_id 
                     WHERE p.is_featured = 1 
                     AND p.created_at BETWEEN :start_date AND :end_date
                     GROUP BY p.id
                     ORDER BY total_inquiries DESC";
            break;
        case 'user_activity':
            $query = "SELECT 
                     i.name as username,
                     i.email,
                     COUNT(i.id) as total_inquiries,
                     i.created_at as last_activity_date
                     FROM inquiries i 
                     WHERE i.created_at BETWEEN :start_date AND :end_date 
                     GROUP BY i.name, i.email
                     ORDER BY total_inquiries DESC";
            break;
    }

    // Execute query
    $stmt = $db->prepare($query);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate report file
    $filename = 'report_' . date('Y-m-d_His');
    $filepath = 'reports/' . $filename;

    if ($format === 'csv') {
        $filepath .= '.csv';
        if ($fp = fopen($filepath, 'w')) {
            // Write headers
            if (!empty($results)) {
                fputcsv($fp, array_keys($results[0]));
            }
            
            // Write data
            foreach ($results as $row) {
                fputcsv($fp, $row);
            }
            fclose($fp);
        }
    } else if ($format === 'pdf') {
        $filepath = 'report_' . date('Y-m-d_His') . '.html';
        
        // Create HTML content
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>' . ucwords(str_replace('_', ' ', $report_type)) . ' Report</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 20px;
                    color: #333;
                }
                h1 {
                    color: #2b3035;
                    text-align: center;
                    margin-bottom: 10px;
                }
                .period {
                    text-align: center;
                    color: #666;
                    margin-bottom: 30px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }
                th, td {
                    padding: 10px;
                    border: 1px solid #ddd;
                    text-align: left;
                }
                th {
                    background-color: #f5f5f5;
                    font-weight: bold;
                }
                tr:nth-child(even) {
                    background-color: #f9f9f9;
                }
                @media print {
                    body {
                        margin: 0;
                        padding: 20px;
                    }
                    button {
                        display: none;
                    }
                }
            </style>
        </head>
        <body>
            <h1>' . ucwords(str_replace('_', ' ', $report_type)) . ' Report</h1>
            <div class="period">Period: ' . $start_date . ' to ' . $end_date . '</div>
            <button onclick="window.print()" style="padding: 10px 20px; margin-bottom: 20px; background: #0d6efd; color: white; border: none; border-radius: 4px; cursor: pointer;">Print Report</button>
            <table>';

        // Add headers
        if (!empty($results)) {
            $html .= '<tr>';
            foreach (array_keys($results[0]) as $header) {
                $html .= '<th>' . ucwords(str_replace('_', ' ', $header)) . '</th>';
            }
            $html .= '</tr>';

            // Add data
            foreach ($results as $row) {
                $html .= '<tr>';
                foreach ($row as $value) {
                    $html .= '<td>' . htmlspecialchars($value) . '</td>';
                }
                $html .= '</tr>';
            }
        }

        $html .= '</table></body></html>';

        // Save HTML file
        file_put_contents('reports/' . $filepath, $html);
        $filepath = 'reports/' . $filepath;
    }

    // Save report to database
    $save_report_query = "INSERT INTO reports (title, type, parameters, created_by, file_path, format) 
                         VALUES (:title, :type, :parameters, :created_by, :file_path, :format)";
    $save_stmt = $db->prepare($save_report_query);
    $title = ucwords(str_replace('_', ' ', $report_type)) . ' Report';
    $parameters = json_encode(['start_date' => $start_date, 'end_date' => $end_date]);
    $save_stmt->bindParam(':title', $title);
    $save_stmt->bindParam(':type', $report_type);
    $save_stmt->bindParam(':parameters', $parameters);
    $save_stmt->bindParam(':created_by', $_SESSION['user_id']);
    $save_stmt->bindParam(':file_path', $filepath);
    $save_stmt->bindParam(':format', $format);
    $save_stmt->execute();

    // Redirect to download
    header("Location: $filepath");
    exit;
}

// Get recent reports
$recent_reports_query = "SELECT * FROM reports ORDER BY created_at DESC LIMIT 10";
$recent_reports = $db->query($recent_reports_query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - RealEstate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #2b3035;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .report-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .section-title {
            font-size: 24px;
            font-weight: 600;
            color: #1a1c23;
            margin-bottom: 24px;
        }
        .form-label {
            font-weight: 500;
            color: #4a5568;
        }
        .nav-link {
            color: #ffffff;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }
        .nav-link:hover, .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }
        .nav-link i {
            width: 20px;
            text-align: center;
            margin-right: 10px;
        }
        .logo {
            font-size: 24px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 30px;
            display: block;
        }
        .table {
            margin-top: 20px;
            background-color: #ffffff;
            border-radius: 8px;
        }
        .table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            color: #4a5568;
            font-weight: 600;
        }
        .table td {
            vertical-align: middle;
            color: #4a5568;
        }
        .btn-primary {
            background-color: #0d6efd;
            border: none;
            padding: 8px 16px;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
        }
        .table-responsive {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <a href="index.php" class="logo text-decoration-none">
            <i class="fas fa-home"></i> RealEstate
        </a>
        <nav class="nav flex-column">
            <a href="index.php" class="nav-link">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="properties.php" class="nav-link">
                <i class="fas fa-building"></i> Properties
            </a>
            <a href="featured-properties.php" class="nav-link">
                <i class="fas fa-star"></i> Featured Properties
            </a>
            <a href="reports.php" class="nav-link active">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
            <a href="manage-queries.php" class="nav-link">
                <i class="fas fa-envelope"></i> Manage Queries
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="report-card">
            <h2 class="section-title">Generate Report</h2>
            <form method="POST" action="">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="report_type" class="form-label">Report Type</label>
                        <select class="form-select" id="report_type" name="report_type" required>
                            <option value="property_sales">Property Sales Report</option>
                            <option value="inquiries">Inquiries Report</option>
                            <option value="featured_performance">Featured Properties Performance</option>
                            <option value="user_activity">User Activity Report</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="format" class="form-label">Export Format</label>
                        <select class="form-select" id="format" name="format" required>
                            <option value="csv">CSV</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" required>
                    </div>
                    <div class="col-md-6">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" required>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="report-card">
            <h2 class="section-title">Generated Report</h2>
            <?php if (!empty($results)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <?php foreach (array_keys($results[0]) as $header): ?>
                            <th><?php echo ucwords(str_replace('_', ' ', $header)); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $row): ?>
                        <tr>
                            <?php foreach ($row as $value): ?>
                            <td><?php echo htmlspecialchars($value); ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-muted">No data available for the selected period.</p>
            <?php endif; ?>
        </div>

        <div class="report-card">
            <h2 class="section-title">Recent Reports</h2>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Generated On</th>
                            <th>Format</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_reports as $report): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($report['title']); ?></td>
                            <td><?php echo ucwords(str_replace('_', ' ', $report['type'])); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($report['created_at'])); ?></td>
                            <td><?php echo strtoupper($report['format']); ?></td>
                            <td>
                                <a href="<?php echo htmlspecialchars($report['file_path']); ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 