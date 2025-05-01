<?php
session_start();
include("config.php");

// Check if admin is logged in
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Fetch student and result details for editing
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $stmt = $conn->prepare("
        SELECT s.*, r.test, r.gd, r.technical_interview, r.hr_interview 
        FROM students s 
        LEFT JOIN result r ON s.id = r.id 
        WHERE s.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    $stmt->close();
}

// Handle form submission for updating student and result details
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $college = $_POST['college'];
    $university_no = $_POST['university_no'];
    $address = $_POST['address'];
    $age = $_POST['age'];
    $department = $_POST['department'];
    $current_year = $_POST['current_year'];
    $cgpa = $_POST['cgpa'];
    $backlogs = $_POST['backlogs'];
    $passout_year = $_POST['passout_year'];

    $test = $_POST['test'];
    $gd = $_POST['gd'];
    $technical_interview = $_POST['technical_interview'];
    $hr_interview = $_POST['hr_interview'];

    // Update students table
    $stmt = $conn->prepare("
        UPDATE students 
        SET name=?, phone=?, email=?, college=?, university_no=?, address=?, age=?, department=?, current_year=?, cgpa=?, backlogs=?, passout_year=? 
        WHERE id=?
    ");
    $stmt->bind_param("sssssisidissi", $name, $phone, $email, $college, $university_no, $address, $age, $department, $current_year, $cgpa, $backlogs, $passout_year, $id);
    $stmt->execute();
    $stmt->close();

    // Update result table
    $stmt = $conn->prepare("
        UPDATE result 
        SET test=?, gd=?, technical_interview=?, hr_interview=? 
        WHERE id=?
    ");
    $stmt->bind_param("ssssi", $test, $gd, $technical_interview, $hr_interview, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin.php");
    exit();
}

$conn->close();

// Options for result statuses
$status_options = ["Yet to Happen", "Failed", "Passed"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
    <link rel="stylesheet" href="edit_student.css">
</head>
<body>
    <h1>Edit Student Details</h1>
    <form action="edit_student.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $student['id']; ?>">

        <label for="name">Full Name:</label>
        <input type="text" id="name" name="name" value="<?php echo $student['name']; ?>" required>

        <label for="phone">Phone Number:</label>
        <input type="tel" id="phone" name="phone" value="<?php echo $student['phone']; ?>" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo $student['email']; ?>" required>

        <label for="college">College Name:</label>
        <input type="text" id="college" name="college" value="<?php echo $student['college']; ?>" required>

        <label for="university_no">University Register No:</label>
        <input type="text" id="university_no" name="university_no" value="<?php echo $student['university_no']; ?>" required>

        <label for="address">Address:</label>
        <input type="text" id="address" name="address" value="<?php echo $student['address']; ?>" required>

        <label for="age">Age:</label>
        <input type="number" id="age" name="age" value="<?php echo $student['age']; ?>" required>

        <label for="department">Department:</label>
        <input type="text" id="department" name="department" value="<?php echo $student['department']; ?>" required>

        <label for="current_year">Current Year:</label>
        <input type="number" id="current_year" name="current_year" value="<?php echo $student['current_year']; ?>" required>

        <label for="cgpa">CGPA:</label>
        <input type="number" step="0.01" id="cgpa" name="cgpa" value="<?php echo $student['cgpa']; ?>" required>

        <label for="backlogs">Number of Backlogs:</label>
        <input type="number" id="backlogs" name="backlogs" value="<?php echo $student['backlogs']; ?>" required>

        <label for="passout_year">Year of Passout:</label>
        <input type="number" id="passout_year" name="passout_year" value="<?php echo $student['passout_year']; ?>" required>

        <h2>Update Exam Progress</h2>

        <label for="test">Test Status:</label>
        <select id="test" name="test" required>
            <?php foreach ($status_options as $option) { ?>
                <option value="<?php echo $option; ?>" <?php echo ($student['test'] == $option) ? 'selected' : ''; ?>>
                    <?php echo $option; ?>
                </option>
            <?php } ?>
        </select>

        <label for="gd">GD Status:</label>
        <select id="gd" name="gd" required>
            <?php foreach ($status_options as $option) { ?>
                <option value="<?php echo $option; ?>" <?php echo ($student['gd'] == $option) ? 'selected' : ''; ?>>
                    <?php echo $option; ?>
                </option>
            <?php } ?>
        </select>

        <label for="technical_interview">Technical Interview Status:</label>
        <select id="technical_interview" name="technical_interview" required>
            <?php foreach ($status_options as $option) { ?>
                <option value="<?php echo $option; ?>" <?php echo ($student['technical_interview'] == $option) ? 'selected' : ''; ?>>
                    <?php echo $option; ?>
                </option>
            <?php } ?>
        </select>

        <label for="hr_interview">HR Interview Status:</label>
        <select id="hr_interview" name="hr_interview" required>
            <?php foreach ($status_options as $option) { ?>
                <option value="<?php echo $option; ?>" <?php echo ($student['hr_interview'] == $option) ? 'selected' : ''; ?>>
                    <?php echo $option; ?>
                </option>
            <?php } ?>
        </select>

        <button type="submit">Update</button>
    </form>
    <a href="admin.php">Back to Admin Dashboard</a>
</body>
</html>