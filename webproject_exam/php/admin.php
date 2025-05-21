<?php
session_start();
$conn = new mysqli("localhost", "root", "", "signupforms");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process Login
if (isset($_POST['login'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    
    // Replace with your actual admin credentials
    $admin_email = "admin@gmail.com";
    $admin_password = "admin@123";
    
    if ($email === $admin_email && $password === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $error = "Invalid email or password!";
    }
}

// Process Teacher Creation
if (isset($_POST['create_teacher'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);
    
    $check_sql = "SELECT id FROM teachers WHERE username = '$username' OR email = '$email'";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows > 0) {
        $teacher_error = "Username or email already exists!";
    } else {
        $sql = "INSERT INTO teachers (username, password, full_name, email) VALUES ('$username', '$password', '$full_name', '$email')";
        
        if ($conn->query($sql) === TRUE) {
            $success = "Teacher account created successfully!";
        } else {
            $teacher_error = "Error creating teacher account!";
        }
    }
}

// Process Teacher Deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $conn->query("DELETE FROM teachers WHERE id = $delete_id");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Process Teacher Update
if (isset($_POST['update_teacher'])) {
    $update_id = $_POST['update_id'];
    $username = $conn->real_escape_string($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);

    $update_sql = "UPDATE teachers SET username = '$username', password = '$password', full_name = '$full_name', email = '$email' WHERE id = $update_id";

    if ($conn->query($update_sql) === TRUE) {
        $success = "Teacher account updated successfully!";
    } else {
        $teacher_error = "Error updating teacher account!";
    }
}

// Fetch teacher data for editing
$edit_teacher = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $edit_teacher = $conn->query("SELECT * FROM teachers WHERE id = $edit_id")->fetch_assoc();
}

// Process Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }
    </style>
</head>
<body>

<?php if (!isset($_SESSION['admin_logged_in'])): ?>
    <!-- Login Form -->
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center">Admin Login</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- Admin Dashboard -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Admin Dashboard</a>
            <div class="navbar-nav ms-auto">
                <a href="?logout=1" class="nav-link">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <!-- Create/Update Teacher Form -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4><?php echo isset($edit_teacher) ? 'Edit' : 'Create'; ?> Teacher Account</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        <?php if (isset($teacher_error)): ?>
                            <div class="alert alert-danger"><?php echo $teacher_error; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <input type="hidden" name="update_id" value="<?php echo $edit_teacher['id'] ?? ''; ?>">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required value="<?php echo $edit_teacher['username'] ?? ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="teacher_password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="teacher_password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required value="<?php echo $edit_teacher['full_name'] ?? ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="teacher_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="teacher_email" name="email" required value="<?php echo $edit_teacher['email'] ?? ''; ?>">
                            </div>
                            <button type="submit" name="<?php echo isset($edit_teacher) ? 'update_teacher' : 'create_teacher'; ?>" class="btn btn-primary">
                                <?php echo isset($edit_teacher) ? 'Update' : 'Create'; ?> Teacher Account
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Recent Teachers List -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Recent Teachers</h4>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT * FROM teachers ORDER BY created_at DESC LIMIT 5";
                                $result = $conn->query($sql);
                                
                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                        echo "<td>
                                                <a href='?edit_id=" . $row['id'] . "' class='btn btn-sm btn-warning'>Edit</a>
                                                <a href='?delete_id=" . $row['id'] . "' class='btn btn-sm btn-danger' onclick=\"return confirm('Are you sure?')\">Delete</a>
                                              </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4'>No teachers found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
</body>
</html>
