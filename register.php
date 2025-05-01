<?php
session_start();
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Insert into students table
    $sql = "INSERT INTO students (name, phone, email, college, university_no, address, age, department, current_year, cgpa, backlogs, passout_year, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssisidiss", $name, $phone, $email, $college, $university_no, $address, $age, $department, $current_year, $cgpa, $backlogs, $passout_year, $password);
    
    if ($stmt->execute()) {
        // Get the last inserted student's ID
        $student_id = $stmt->insert_id;

        // Insert into result table
        $sql_result = "INSERT INTO result (id) VALUES (?)"; 
        $stmt_result = $conn->prepare($sql_result);
        $stmt_result->bind_param("i", $student_id);
        $stmt_result->execute();
        $stmt_result->close();

        // Prepare skill data
        $skills = ['java', 'c', 'python', 'cplusplus', 'javascript', 'csharp', 'php', 'sql', 'html'];
        $skill_values = [];

        foreach ($skills as $skill) {
            $skill_values[$skill] = isset($_POST[$skill]) ? 1 : 0; // If checked, store 1 (TRUE), else 0 (FALSE)
        }

        // Insert into skill table
        $sql_skill = "INSERT INTO skill (id, java, c, python, cplusplus, javascript, csharp, php, `sql`, html) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_skill = $conn->prepare($sql_skill);
        $stmt_skill->bind_param("iiiiiiiiii", $student_id, $skill_values['java'], $skill_values['c'], $skill_values['python'], 
                                $skill_values['cplusplus'], $skill_values['javascript'], $skill_values['csharp'], 
                                $skill_values['php'], $skill_values['sql'], $skill_values['html']);

        if ($stmt_skill->execute()) {
            echo "Registration successful!";
            header("Location: index.php");
            exit();
        } else {
            echo "Error inserting into skill table: " . $stmt_skill->error;
        }

        $stmt_skill->close();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stylesREGISTER.css">
    <title>Register - Placify</title>
</head>
<body>
    <form action="register.php" method="POST">
        <h1>Register</h1>
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="tel" name="phone" placeholder="Phone Number" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="college" placeholder="College Name" required>
        <input type="text" name="university_no" placeholder="University Register No" required>
        <input type="text" name="address" placeholder="Address" required>
        <input type="number" name="age" placeholder="Age" required>
        <input type="text" name="department" placeholder="Department" required>
        <input type="number" name="current_year" placeholder="Current Year" required>
        <input type="number" step="0.01" name="cgpa" placeholder="CGPA" required>
        <input type="number" name="backlogs" placeholder="No of Backlogs" required>
        <input type="number" name="passout_year" placeholder="Year of Passout" required>
        <input type="password" name="password" placeholder="Password" required>

        <h3>Select Your Skills:</h3>
        <div class="skills-container">
            <label><input type="checkbox" name="java"> Java</label>
            <label><input type="checkbox" name="c"> C</label>
            <label><input type="checkbox" name="python"> Python</label>
            <label><input type="checkbox" name="cplusplus"> C++</label>
            <label><input type="checkbox" name="javascript"> JavaScript</label>
            <label><input type="checkbox" name="csharp"> C#</label>
            <label><input type="checkbox" name="php"> PHP</label>
            <label><input type="checkbox" name="sql"> SQL</label>
            <label><input type="checkbox" name="html"> HTML</label>
        </div>

        <button type="submit">Register</button>
    </form>
</body>
</html>