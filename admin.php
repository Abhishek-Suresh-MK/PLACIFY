<?php
session_start();
include("config.php");

// Check if admin is logged in
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Handle delete request
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// Sorting logic
$allowed_columns = ['id', 'name', 'phone', 'email', 'college', 'university_no', 'address', 'age', 'department', 'current_year', 'cgpa', 'backlogs', 'passout_year', 'test', 'gd', 'technical_interview', 'hr_interview'];
$sort_column = isset($_GET['sort']) && in_array($_GET['sort'], $allowed_columns) ? $_GET['sort'] : 'id';
$sort_order = isset($_GET['order']) && $_GET['order'] == 'desc' ? 'DESC' : 'ASC';
$next_order = $sort_order === 'ASC' ? 'desc' : 'asc';

// Search and filter logic
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$selected_stages = isset($_GET['stages']) ? $_GET['stages'] : [];

$conditions = [];
$param_types = "";
$param_values = [];

// Search condition
if (!empty($search)) {
    $conditions[] = "(students.name LIKE ? OR students.email LIKE ? OR students.college LIKE ?)";
    $search_param = "%$search%";
    $param_types .= "sss";
    $param_values[] = $search_param;
    $param_values[] = $search_param;
    $param_values[] = $search_param;
}

// Filter by passed stages (check for "PASSED")
$stages = ['test', 'gd', 'technical_interview', 'hr_interview'];
$stage_conditions = [];

foreach ($stages as $stage) {
    if (in_array($stage, $selected_stages)) {
        $stage_conditions[] = "result.$stage = 'PASSED'";
    }
}

if (!empty($stage_conditions)) {
    $conditions[] = "(" . implode(" AND ", $stage_conditions) . ")";
}

// Build the WHERE clause
$where_clause = "";
if (!empty($conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $conditions);
}

// Prepare SQL query with JOIN
$sql = "SELECT students.*, result.test, result.gd, result.technical_interview, result.hr_interview 
        FROM students 
        LEFT JOIN result ON students.id = result.id 
        $where_clause 
        ORDER BY $sort_column $sort_order";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Query Preparation Failed: " . $conn->error);
}

if (!empty($param_values)) {
    $stmt->bind_param($param_types, ...$param_values);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Placify</title>
    <link rel="stylesheet" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <h1>Admin Dashboard</h1>

        <!-- Logout Form -->
        <form action="logout.php" method="POST" class="logout-form">
            <button type="submit" class="logout-btn">Logout</button>
        </form>

        <!-- Search and Filter Form -->
        <form method="GET" action="admin.php" class="filter-form">
            <div class="search-container">
                <input type="text" name="search" placeholder="Search by Name, Email, College" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" name="action" value="search">Search</button>
            </div>

            <h3>Filter by Passed Stages:</h3>
            <div class="stages-container">
                <?php foreach ($stages as $stage) { ?>
                    <label>
                        <input type="checkbox" name="stages[]" value="<?php echo $stage; ?>" 
                        <?php echo in_array($stage, $selected_stages) ? 'checked' : ''; ?>>
                        <?php echo ucfirst(str_replace('_', ' ', $stage)); ?>
                    </label>
                <?php } ?>
            </div>
            <button type="submit" name="action" value="filter" class="filter-btn">Filter</button>
        </form>

        <table>
            <tr>
                <th><a href="?sort=id&order=<?php echo $next_order; ?>">ID</a></th>
                <th><a href="?sort=name&order=<?php echo $next_order; ?>">Name</a></th>
                <th><a href="?sort=phone&order=<?php echo $next_order; ?>">Phone</a></th>
                <th><a href="?sort=email&order=<?php echo $next_order; ?>">Email</a></th>
                <th><a href="?sort=college&order=<?php echo $next_order; ?>">College</a></th>
                <th><a href="?sort=university_no&order=<?php echo $next_order; ?>">University No</a></th>
                <th><a href="?sort=department&order=<?php echo $next_order; ?>">Department</a></th>
                <th><a href="?sort=cgpa&order=<?php echo $next_order; ?>">CGPA</a></th>
                <th><a href="?sort=test&order=<?php echo $next_order; ?>">Test</a></th>
                <th><a href="?sort=gd&order=<?php echo $next_order; ?>">GD</a></th>
                <th><a href="?sort=technical_interview&order=<?php echo $next_order; ?>">Technical Interview</a></th>
                <th><a href="?sort=hr_interview&order=<?php echo $next_order; ?>">HR Interview</a></th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['college']); ?></td>
                    <td><?php echo htmlspecialchars($row['university_no']); ?></td>
                    <td><?php echo htmlspecialchars($row['department']); ?></td>
                    <td><?php echo htmlspecialchars($row['cgpa']); ?></td>
                    <td><?php echo htmlspecialchars($row['test'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['gd'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['technical_interview'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['hr_interview'] ?? 'N/A'); ?></td>
                    <td>
                        <a href="edit_student.php?id=<?php echo $row['id']; ?>" class="action-link edit">Edit</a> |
                        <a href="admin.php?delete=<?php echo $row['id']; ?>" class="action-link delete" onclick="return confirm('Are you sure?');">Delete</a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>