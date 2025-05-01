<?php
session_start();
include("config.php");

if (!isset($_SESSION["user"]) || $_SESSION["user"] !== "recruiter") {
    header("Location: index.php");
    exit();
}

$cutoff = isset($_POST['cgpa_cutoff']) ? floatval($_POST['cgpa_cutoff']) : 0;
$selected_skills = isset($_POST['skills']) ? $_POST['skills'] : [];
$selected_stages = isset($_POST['stages']) ? $_POST['stages'] : [];
$search = isset($_POST['search']) ? trim($_POST['search']) : ''; // Add search input

// Base SQL Query
$sql = "SELECT students.id, students.name, students.phone, students.email, students.college, students.university_no, 
               students.address, students.age, students.department, students.current_year, students.cgpa, students.backlogs, 
               students.passout_year, result.test, result.gd, result.technical_interview, result.hr_interview 
        FROM students 
        LEFT JOIN result ON students.id = result.id
        LEFT JOIN skill ON students.id = skill.id";

// Build conditions
$conditions = [];
$params = [];
$param_values = [];

// Search condition
if (!empty($search)) {
    $conditions[] = "(students.name LIKE ? OR students.college LIKE ?)";
    $search_param = "%$search%";
    $params[] = "s";
    $params[] = "s";
    $param_values[] = $search_param;
    $param_values[] = $search_param;
}

// CGPA condition
$conditions[] = "students.cgpa >= ?";
$params[] = "d";
$param_values[] = $cutoff;

// Skills filtering
$skill_conditions = [];
if (!empty($selected_skills)) {
    foreach ($selected_skills as $skill) {
        $skill_conditions[] = "skill.$skill = ?";
        $params[] = "i";
        $param_values[] = 1;
    }
}

if (!empty($skill_conditions)) {
    $conditions[] = "(" . implode(" AND ", $skill_conditions) . ")";
}

// Stages filtering (check for "PASSED")
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
    $where_clause = " WHERE " . implode(" AND ", $conditions);
}

$sql .= $where_clause;

// Prepare the statement
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Query Preparation Failed: " . $conn->error);
}

// Bind parameters dynamically
if (!empty($param_values)) {
    $types = implode("", $params);
    $stmt->bind_param($types, ...$param_values);
}

$stmt->execute();
$result = $stmt->get_result();

// Fetch all records into an array to count them
$records = [];
while ($row = $result->fetch_assoc()) {
    $records[] = $row;
}

// Get the total number of records
$total_records = count($records);

// Fetch available skills dynamically from the database
$skills_query = "SHOW COLUMNS FROM skill WHERE Field != 'id'";
$skills_result = $conn->query($skills_query);
$skills = [];

while ($row = $skills_result->fetch_assoc()) {
    $skills[] = $row['Field'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Recruiter Dashboard - Placify</title>
    <link rel="stylesheet" href="stylesRECRUITERS.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="recruiter-container">
        <h2>Recruiter Dashboard</h2>

        <form method="post">
            <!-- Search Bar -->
            <div class="search-container">
                <input type="text" name="search" placeholder="Search by Name or College" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </div>

            <label for="cgpa_cutoff">Enter CGPA Cutoff:</label>
            <input type="number" step="0.01" name="cgpa_cutoff" value="<?php echo htmlspecialchars($cutoff); ?>">

            <h3>Select Required Skills:</h3>
            <div class="skills-container">
                <?php foreach ($skills as $skill) { ?>
                    <label>
                        <input type="checkbox" name="skills[]" value="<?php echo $skill; ?>" 
                        <?php echo in_array($skill, $selected_skills) ? 'checked' : ''; ?>>
                        <?php echo ucfirst($skill); ?>
                    </label>
                <?php } ?>
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
            
            <button type="submit">Filter</button>
        </form>

        <!-- Display total number of records for debugging -->
        <p style="margin-bottom: 20px; font-size: 16px; color: #e0e0e0;">
            Total Records: <?php echo $total_records; ?>
        </p>

        <!-- Wrap the table in a div to control scrolling -->
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>College</th>
                        <th>University No</th>
                        <th>Address</th>
                        <th>Age</th>
                        <th>Department</th>
                        <th>Current Year</th>
                        <th>CGPA</th>
                        <th>Backlogs</th>
                        <th>Passout Year</th>
                        <th>Test Score</th>
                        <th>Group Discussion</th>
                        <th>Technical Interview</th>
                        <th>HR Interview</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $row) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row["id"] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row["name"] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row["phone"] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row["email"] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row["college"] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row["university_no"] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row["address"] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row["age"] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row["department"] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row["current_year"] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row["cgpa"] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row["backlogs"] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row["passout_year"] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row["test"] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row["gd"] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row["technical_interview"] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row["hr_interview"] ?? 'N/A'); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>