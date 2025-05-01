<?php
session_start();
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Check if admin login
    if ($username === "admin" && $password === "admin123") {
        $_SESSION["user"] = "admin";
        header("Location: admin.php");
        exit();
    }

    // Check if recruiter login
    if ($username === "main" && $password === "main123") {
        $_SESSION["user"] = "recruiter";
        header("Location: recruiters.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT password FROM students WHERE name = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($db_password);
        $stmt->fetch();

        if (password_verify($password, $db_password)) {  
            $_SESSION["user"] = $username;
            header("Location: dashboard.php");
            exit();
        } else {
            echo "<script>alert('Incorrect password. Try again!'); window.location.href='index.php';</script>";
        }
    } else {
        echo "<script>alert('User not found. Register first!'); window.location.href='index.php';</script>";
    }
    $stmt->close();
}
$conn->close();
?>