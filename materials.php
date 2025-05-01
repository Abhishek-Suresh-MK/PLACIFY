<?php
session_start();
include("config.php");

// Fetch materials from the database
$sql = "SELECT company_name, drive_link, created_at FROM materials ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Materials - Placify</title>
    <link rel="stylesheet" href="stylesMATERIALS.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Materials Section -->
    <section class="materials-section">
        <div class="materials-container">
            <h1>Study Materials</h1>
            <table>
                <thead>
                    <tr>
                        <th>Company Name</th>
                        <th>Question Paper Link</th>
                        <th>Added On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                                <td><a href="<?php echo htmlspecialchars($row['drive_link']); ?>" target="_blank" class="drive-link">View Question Paper</a></td>
                                <td><?php echo date("d M Y", strtotime($row['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">No study materials available yet.</td>
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
$conn->close();
?>