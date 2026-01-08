<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim(sanitizeInput($_POST['email']));
$password = trim($_POST['password']);


    $conn = getDBConnection();
    
$stmt = $conn->prepare("SELECT id, name, password FROM admin WHERE LOWER(email) = LOWER(?)");

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
echo "Entered Email: '$email'<br>";


if ($result->num_rows == 1) {
    $admin = $result->fetch_assoc();

    if ($password == $admin['password']) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid password! Please check your credentials.";
    }
} else {
    $error = "Admin not found!";
}

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - VoteSecure</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .login-image {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            padding: 2rem;
        }
        .login-form {
            padding: 3rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="login-container">
                    <div class="row g-0">
                        <div class="col-md-6 login-image">
                            <div class="text-center">
                                <i class="fas fa-user-shield fa-5x mb-3"></i>
                                <h3>Admin Portal</h3>
                                <p>Secure access to election management</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="login-form">
                                <h2 class="text-center mb-4">Admin Login</h2>
                                
                                <?php if (isset($error)): ?>
                                    <div class="alert alert-danger"><?php echo $error; ?></div>
                                <?php endif; ?>
                                
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-envelope"></i>
                                            </span>
                                            <input type="email" class="form-control" id="email" name="email" required>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <input type="password" class="form-control" id="password" name="password" required>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary w-100 mb-3">
                                        <i class="fas fa-sign-in-alt me-2"></i>Login
                                    </button>
                                    
                                    <div class="text-center">
                                        <a href="../index.php" class="text-decoration-none">
                                            <i class="fas fa-arrow-left me-1"></i>Back to Home
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
