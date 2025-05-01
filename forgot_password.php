<?php
session_start();
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if email exists in students table
    $stmt = $conn->prepare("SELECT id FROM students WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Generate a unique token
        $token = bin2hex(random_bytes(50)); // 100-character token
        $reset_link = "http://yourdomain.com/reset_password.php?token=" . $token;

        // Store token in password_resets table
        $stmt = $conn->prepare("INSERT INTO password_resets (email, token) VALUES (?, ?) ON DUPLICATE KEY UPDATE token = ?");
        $stmt->bind_param("sss", $email, $token, $token);
        $stmt->execute();

        // Send email (configure your email settings in production)
        $subject = "Placify Password Reset";
        $message = "Click the following link to reset your password: $reset_link\nThis link will expire in 1 hour.";
        $headers = "From: no-reply@placify.com";
        if (mail($email, $subject, $message, $headers)) {
            $success = "A password reset link has been sent to your email.";
        } else {
            $error = "Failed to send email. Please try again.";
        }
    } else {
        $error = "No account found with that email.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Placify</title>
    <link rel="stylesheet" href="stylesINDEX.css"> <!-- Reuse index styles for consistency -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <section class="section login-section">
        <div class="background-slideshow">
            <div class="slide" style="background-image: url('images/college-bg.jpeg');"></div>
            <div class="slide-overlay"></div>
        </div>
        <div class="login-container">
            <img src="images/logo.png" alt="Placify Logo" class="logo" onerror="this.src='https://via.placeholder.com/150?text=Placify';">
            <h1>Forgot Password</h1>
            <p class="subtitle">Enter your email to reset your password</p>
            <?php if (isset($success)): ?>
                <p class="success-msg"><?php echo $success; ?></p>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <p class="error-msg"><?php echo $error; ?></p>
            <?php endif; ?>
            <form action="forgot_password.php" method="POST" class="login-form">
                <div class="form-group">
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <button type="submit" class="login-btn">Send Reset Link</button>
            </form>
            <div class="extra-links">
                <p>Back to <a href="index.php">Sign in</a></p>
            </div>
        </div>
    </section>
</body>
</html>

<?php
$conn->close();
?>