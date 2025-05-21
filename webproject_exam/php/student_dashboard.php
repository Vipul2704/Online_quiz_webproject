<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

// Retrieve student information from session
$username = $_SESSION['username'];

// Query to get user details including the profile photo
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $profilePhoto = $user['profile_photo'];
    $email = $user['email'];
} else {
    echo "User not found.";
    exit();
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #f39c12;
            --background-color: #f5f7fa;
            --sidebar-color: #ffffff;
            --text-color: #333333;
            --hover-color: #3498db;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body, html {
            font-family: 'Roboto', sans-serif;
            background-color: var(--background-color);
            height: 100%;
            color: var(--text-color);
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 240px;
            background-color: var(--sidebar-color);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            padding: 20px;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }

        .content {
            flex-grow: 1;
            padding: 30px;
            background-color: var(--background-color);
            transition: all 0.3s ease;
        }

        .profile-info {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-pic {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto 10px;
            background-color: #e0e0e0;
            background-image: url('uploads/<?php echo $profilePhoto; ?>');
            background-size: cover;
            background-position: center;
            border: 3px solid var(--primary-color);
            transition: transform 0.3s ease;
        }

        .profile-pic:hover {
            transform: scale(1.05);
        }

        .username {
            font-size: 18px;
            font-weight: 500;
            color: var(--text-color);
        }

        .email {
            font-size: 14px;
            color: #777;
        }

        .sidebar-menu {
            list-style-type: none;
        }

        .sidebar-menu li {
            margin-bottom: 10px;
        }

        .sidebar-menu a {
            display: block;
            padding: 10px 15px;
            text-decoration: none;
            color: var(--text-color);
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: var(--primary-color);
            color: #fff;
            transform: translateX(5px);
        }

        .logout-btn {
            margin-top: auto;
            background-color: var(--secondary-color);
            color: white;
            border: none;
            padding: 10px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #e67e22;
            transform: translateY(-2px);
        }

        .content-card {
            background-color: var(--sidebar-color);
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .content-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }

        h2 {
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            padding: 10px;
            color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .dashboard {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                display: none;
            }

            .sidebar.active {
                display: block;
            }

            .content {
                width: 100%;
            }

            .menu-toggle {
                display: block;
                position: fixed;
                top: 10px;
                left: 10px;
                z-index: 1000;
            }
        }
    </style>
</head>
<body>
    <button class="menu-toggle">☰</button>
    <div class="dashboard">
        <div class="sidebar">
            <div class="profile-info">
                <div class="profile-pic"></div>
                <div class="username"><?php echo $username; ?></div>
                <div class="email"><?php echo $email; ?></div>
            </div>
            <ul class="sidebar-menu">
                <li><a href="#home" class="active">Home</a></li>
                <li><a href="q_start.php">Quiz</a></li>
                <li><a href="#result">Result</a></li>
                <li><a href="../html/forgotpassword.html">Change Password</a></li>
            </ul>
            <form method="POST" action="../html/studentlogin.html">
                <button class="logout-btn" type="submit">Logout</button>
            </form>
        </div>
        <div class="content">
            <div class="content-card">
                <h2>Welcome to Your Dashboard</h2>
                <p>Here you can access your quizzes, view results, and manage your account settings.</p>
            </div>
            <!-- Add more content cards for different sections as needed -->
        </div>
    </div>

    <script>
        // Toggle active class for sidebar menu items
        const menuItems = document.querySelectorAll('.sidebar-menu a');
        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                menuItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Mobile menu toggle
        const menuToggle = document.querySelector('.menu-toggle');
        const sidebar = document.querySelector('.sidebar');
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });
    </script>
</body>
</html>