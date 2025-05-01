<?php
session_start();
include("config.php");

// Fetch published announcements from announcementsindex
$announcement_sql = "SELECT title, content, drive_date, created_at FROM announcementsindex WHERE status = 'Published' ORDER BY created_at DESC LIMIT 5";
$announcement_result = $conn->query($announcement_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placify - Your Placement Gateway</title>
    <link rel="stylesheet" href="stylesINDEX.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="#home" class="nav-logo">
                <img src="images/logo.png" alt="Placify Logo" class="logo-small">
                <span>Placify</span>
            </a>
            <div class="nav-links">
                <a href="#home" class="nav-link">Home</a>
                <a href="#gallery" class="nav-link">Gallery</a>
                <a href="#announcements" class="nav-link">Announcements</a>
                <a href="history.php" class="nav-link">Placement History</a>
                <a href="materials.php" class="nav-link">Study Materials</a>
            </div>
        </div>
    </nav>

    <!-- Login Section -->
    <section id="home" class="section login-section">
        <div class="background-slideshow">
            <div class="slide" style="background-image: url('images/college-bg.jpeg');"></div>
            <div class="slide" style="background-image: url('images/college-bg4.jpg');"></div>
            <div class="slide" style="background-image: url('images/college-bg3.jpg');"></div>
            <div class="slide-overlay"></div>
        </div>
        <div class="login-container">
            <img src="images/logo.png" alt="Placify Logo" class="logo" onerror="this.src='https://via.placeholder.com/150?text=Placify';">
            <h1>Welcome to Placify</h1>
            <p class="subtitle">Your placement gateway</p>
            <?php if (isset($_GET['registered'])): ?>
                <p class="success-msg">Registration successful! Please sign in.</p>
            <?php endif; ?>
            <form action="login.php" method="POST" class="login-form">
                <div class="form-group">
                    <input type="text" id="username" name="username" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <input type="password" id="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" name="login" class="login-btn">Sign In</button>
            </form>
            <div class="extra-links">
                <p>New to Placify? <a href="register.php">Sign up now</a></p>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section id="gallery" class="section gallery-section">
        <h2>Placed Students Gallery</h2>
        <div class="gallery-container">
            <div class="gallery-item">
                <img src="images/student111.jpeg" alt="Student 1">
                <div class="overlay">
                    <p>ALEN - <span class="company">Google</span></p>
                </div>
            </div>
            <div class="gallery-item">
                <img src="images/student222.jpg" alt="Student 2">
                <div class="overlay">
                    <p>MATHEW - <span class="company">Microsoft</span></p>
                </div>
            </div>
            <div class="gallery-item">
                <img src="images/student333.jpg" alt="Student 3">
                <div class="overlay">
                    <p>ROSE - <span class="company">Amazon</span></p>
                </div>
            </div>
            <div class="gallery-item">
                <img src="images/student444.jpg" alt="Student 4">
                <div class="overlay">
                    <p>HONEY - <span class="company">Tesla</span></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Announcements Section -->
    <section id="announcements" class="section announcements-section">
        <h2>Upcoming Placement Drives</h2>
        <div class="announcements-container">
            <?php if ($announcement_result->num_rows > 0): ?>
                <?php while ($announcement = $announcement_result->fetch_assoc()): ?>
                    <div class="announcement-item">
                        <h3><?php echo htmlspecialchars($announcement['title']); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($announcement['content'])); ?></p>
                        <p><strong>Drive Date:</strong> <?php echo date("d M Y", strtotime($announcement['drive_date'])); ?></p>
                        <small><?php echo date("d M Y, h:i A", strtotime($announcement['created_at'])); ?></small>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="no-announcements">No upcoming placement drives announced yet.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Smooth Scroll Script -->
    <script>
        document.querySelectorAll('.nav-link, .nav-logo').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href.startsWith('#')) {
                    e.preventDefault();
                    const targetId = href.substring(1);
                    const targetSection = document.getElementById(targetId);
                    if (targetSection) {
                        targetSection.scrollIntoView({ behavior: 'smooth' });
                    }
                }
            });
        });

        // Slideshow Animation
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        function nextSlide() {
            slides[currentSlide].style.opacity = '0';
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].style.opacity = '1';
        }
        setInterval(nextSlide, 5000);
    </script>
</body>
</html>

<?php
$conn->close();
?>