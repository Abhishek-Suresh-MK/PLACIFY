<?php
session_start();
include("config.php");

// Search logic
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_query = "";
$params = [];

if (!empty($search)) {
    $search_query = "WHERE name LIKE ? OR company_name LIKE ?";
    $search_param = "%$search%";
    $params = [$search_param, $search_param];
}

// Prepare SQL query
$sql = "SELECT student_id, name, company_name, placement_date, job_role, package 
        FROM history 
        $search_query 
        ORDER BY placement_date DESC";
$stmt = $conn->prepare($sql);

if (!empty($search)) {
    $stmt->bind_param("ss", ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placement History - Placify</title>
    <link rel="stylesheet" href="stylesHISTORY.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- History Section -->
    <section class="history-section">
        <div class="history-container">
            <h1>Placement History</h1>
            <form method="GET" action="history.php" class="search-form">
                <input type="text" name="search" placeholder="Search by Name or Company" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Company</th>
                        <th>Job Role</th>
                        <th>Package (LPA)</th>
                        <th>Placement Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['job_role']); ?></td>
                                <td><?php echo $row['package'] ? htmlspecialchars($row['package']) : 'N/A'; ?></td>
                                <td><?php echo date("d M Y", strtotime($row['placement_date'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No placement records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </section>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>