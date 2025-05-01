<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user'];

// Debugging: Output the session user ID to check its value
error_log("Session user_id in dashboard: " . print_r($user_id, true)); // Log to error log

// Determine the correct identifier based on session
$identifier = $user_id; // Default to user_id
$param_type = "i"; // Default to integer for id

// Check if the session contains an email or username instead of id
if (filter_var($user_id, FILTER_VALIDATE_EMAIL)) {
    $identifier_column = "s.email";
    $param_type = "s"; // String for email
    error_log("Detected email in session: $user_id");
} elseif (!is_numeric($user_id)) {
    $identifier_column = "s.name"; // Fallback to name if not numeric or email
    $param_type = "s";
    error_log("Detected non-numeric, assuming username: $user_id");
} else {
    $identifier_column = "s.id"; // Use id if numeric
}

// Fetch student details along with result details using a JOIN query
$sql = "SELECT 
            s.name, s.email, s.college, s.department, s.current_year, 
            s.cgpa, s.backlogs, s.passout_year,
            r.test, r.gd, r.technical_interview, r.hr_interview
        FROM students s
        LEFT JOIN result r ON s.id = r.id
        WHERE $identifier_column = ?";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log("Prepare failed: " . $conn->error);
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param($param_type, $identifier);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    if ($student === null) {
        error_log("No student found for identifier: $identifier (column: $identifier_column)");
    }
} else {
    error_log("Execute failed: " . $stmt->error);
    echo "Error: " . $conn->error;
}

// Fetch announcements
$announcement_sql = "SELECT title, content, created_at FROM announcements ORDER BY created_at DESC";
$announcement_result = $conn->query($announcement_sql);
if ($announcement_result === false) {
    error_log("Announcement query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="stylesDASHBOARD.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Student Details Section -->
        <div class="student-details">
            <h1>Welcome, <?php echo htmlspecialchars($student['name'] ?? 'User'); ?>!</h1>
            <?php if ($student === null): ?>
                <p style="color: red;">No student data found. Please contact support or check your login.</p>
            <?php else: ?>
                <ul class="details-list">
                    <li><strong>Email:</strong> <?php echo htmlspecialchars($student['email'] ?? 'N/A'); ?></li>
                    <li><strong>College:</strong> <?php echo htmlspecialchars($student['college'] ?? 'N/A'); ?></li>
                    <li><strong>Department:</strong> <?php echo htmlspecialchars($student['department'] ?? 'N/A'); ?></li>
                    <li><strong>Current Year:</strong> <?php echo htmlspecialchars($student['current_year'] ?? 'N/A'); ?></li>
                    <li><strong>CGPA:</strong> <?php echo htmlspecialchars($student['cgpa'] ?? 'N/A'); ?></li>
                    <li><strong>Backlogs:</strong> <?php echo htmlspecialchars($student['backlogs'] ?? 'N/A'); ?></li>
                    <li><strong>Passout Year:</strong> <?php echo htmlspecialchars($student['passout_year'] ?? 'N/A'); ?></li>
                </ul>
            <?php endif; ?>
        </div>

        <!-- Result Section -->
        <div class="result-section">
            <h2>Result</h2>
            <?php if ($student === null): ?>
                <p style="color: red;">No result data available.</p>
            <?php else: ?>
                <ul class="result-list">
                    <li><strong>Test:</strong> <?php echo htmlspecialchars($student['test'] ?? 'YET TO HAPPEN'); ?></li>
                    <li><strong>Group Discussion:</strong> <?php echo htmlspecialchars($student['gd'] ?? 'YET TO HAPPEN'); ?></li>
                    <li><strong>Technical Interview:</strong> <?php echo htmlspecialchars($student['technical_interview'] ?? 'YET TO HAPPEN'); ?></li>
                    <li><strong>HR Interview:</strong> <?php echo htmlspecialchars($student['hr_interview'] ?? 'YET TO HAPPEN'); ?></li>
                </ul>
            <?php endif; ?>
        </div>

        <!-- Announcements Section -->
        <div class="announcements">
            <h2>Announcements</h2>
            <?php while ($announcement = $announcement_result->fetch_assoc()): ?>
                <div class="announcement-item">
                    <h3><?php echo htmlspecialchars($announcement['title']); ?></h3>
                    <p><?php echo nl2br(htmlspecialchars($announcement['content'])); ?></p>
                    <small><?php echo date("d M Y, h:i A", strtotime($announcement['created_at'])); ?></small>
                </div>
            <?php endwhile; ?>
            <?php if ($announcement_result->num_rows === 0): ?>
                <p>No announcements available.</p>
            <?php endif; ?>
        </div>
    </div>

    <a href="logout.php" class="logout-btn">Logout</a>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>