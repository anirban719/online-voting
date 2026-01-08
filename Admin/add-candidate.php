<?php
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = getDBConnection();
$errors = [];
$success = false;

// Create upload directories if they don't exist
$uploadDirs = ['../uploads/candidates', '../uploads/symbols'];
foreach ($uploadDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $name = trim($_POST['name']);
    $dob = $_POST['dob'];
    $email = trim($_POST['email']);
    $nameofparty = trim($_POST['nameofparty']);
    
    // Validate required fields
    if (empty($name)) $errors[] = "Candidate name is required";
    if (empty($dob)) $errors[] = "Date of birth is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($nameofparty)) $errors[] = "Party name is required";
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM candidate WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $errors[] = "Email already exists";
    }
    
    // Handle image uploads
    $imagePath = '';
    $symbolPath = '';
    
    // Upload candidate image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = $_FILES['image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($image['type'], $allowedTypes)) {
            $errors[] = "Invalid image format. Only JPG, JPEG, PNG allowed";
        } elseif ($image['size'] > $maxSize) {
            $errors[] = "Image size must be less than 2MB";
        } else {
            $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
            $imageName = uniqid('candidate_') . '.' . $ext;
            $imagePath = 'candidates/' . $imageName;
            
            if (!move_uploaded_file($image['tmp_name'], '../uploads/' . $imagePath)) {
                $errors[] = "Failed to upload candidate image";
            }
        }
    } else {
        $errors[] = "Candidate image is required";
    }
    
    // Upload party symbol
    if (isset($_FILES['symbol']) && $_FILES['symbol']['error'] === UPLOAD_ERR_OK) {
        $symbol = $_FILES['symbol'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        $maxSize = 1 * 1024 * 1024; // 1MB
        
        if (!in_array($symbol['type'], $allowedTypes)) {
            $errors[] = "Invalid symbol format. Only JPG, JPEG, PNG, GIF allowed";
        } elseif ($symbol['size'] > $maxSize) {
            $errors[] = "Symbol size must be less than 1MB";
        } else {
            $ext = pathinfo($symbol['name'], PATHINFO_EXTENSION);
            $symbolName = uniqid('symbol_') . '.' . $ext;
            $symbolPath = 'symbols/' . $symbolName;
            
            if (!move_uploaded_file($symbol['tmp_name'], '../uploads/' . $symbolPath)) {
                $errors[] = "Failed to upload party symbol";
            }
        }
    } else {
        $errors[] = "Party symbol is required";
    }
    
    // Insert into database if no errors
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO candidate (name, dob, email, nameofparty, symbol, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $dob, $email, $nameofparty, $symbolPath, $imagePath);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Candidate added successfully!";
            header("Location: candidates.php");
            exit();
        } else {
            $errors[] = "Database error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Candidate - VoteSecure Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin: 2rem 0;
        }
        .form-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            margin: -2rem -2rem 2rem -2rem;
            border-radius: 15px 15px 0 0;
        }
        .image-preview {
            width: 150px;
            height: 150px;
            border: 2px dashed #ddd;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 10px 0;
            overflow: hidden;
        }
        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .upload-zone {
            border: 2px dashed #ccc;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
        }
        .upload-zone:hover {
            border-color: #667eea;
            background-color: #f8f9ff;
        }
        .upload-zone.dragover {
            border-color: #667eea;
            background-color: #f0f2ff;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">
                <i class="fas fa-vote-yea me-2"></i>Admin Dashboard
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="candidates.php">
                            <i class="fas fa-users me-1"></i>Candidates
                        </a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link" href="voters.php">
                            <i class="fas fa-user-check me-1"></i>Voters
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="results.php">
                            <i class="fas fa-chart-bar me-1"></i>Results
                        </a>
                    </li> -->
                </ul>
                <div class="navbar-nav">
                    <span class="navbar-text me-3">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-container">
                    <div class="form-header">
                        <h3 class="mb-0">
                            <i class="fas fa-user-plus me-2"></i>Add New Candidate
                        </h3>
                        <p class="mb-0 mt-2">Fill in the details below to add a new election candidate</p>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" id="candidateForm">
                        <!-- Personal Information -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-user-circle me-2"></i>Personal Information
                                </h5>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">
                                    <i class="fas fa-user me-1"></i>Full Name *
                                </label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" 
                                       required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="dob" class="form-label">
                                    <i class="fas fa-calendar me-1"></i>*
                                     Date of Birth 
                                </label>
                                <input type="date" class="form-control" id="dob" name="dob" 
                                       value="<?php echo isset($_POST['dob']) ? htmlspecialchars($_POST['dob']) : ''; ?>" 
                                       required>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label for="nameofparty" class="form-label">
                                    <i class="fas fa-building me-1"></i>Party Name *
                                </label>
                                <input type="text" class="form-control" id="nameofparty" name="nameofparty" 
                                       value="<?php echo isset($_POST['nameofparty']) ? htmlspecialchars($_POST['nameofparty']) : ''; ?>" 
                                       required>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-envelope me-2"></i>Contact Information
                                </h5>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>Email Address *
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                       required>
                            </div>
                        </div>

                        <!-- Image Uploads -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-images me-2"></i>Image Uploads
                                </h5>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-portrait me-1"></i>Candidate Photo *
                                </label>
                                <div class="upload-zone" onclick="document.getElementById('image').click()">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-2"></i>
                                    <p class="mb-0">Click to upload candidate photo</p>
                                    <small class="text-muted">JPG, JPEG, PNG (Max 2MB)</small>
                                </div>
                                <input type="file" class="form-control d-none" id="image" name="image" 
                                       accept="image/jpeg,image/jpg,image/png" required>
                                <div class="image-preview mt-2" id="imagePreview" style="display: none;">
                                    <img id="imagePreviewImg" src="" alt="Preview">
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-flag me-1"></i>Party Symbol *
                                </label>
                                <div class="upload-zone" onclick="document.getElementById('symbol').click()">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-2"></i>
                                    <p class="mb-0">Click to upload party symbol</p>
                                    <small class="text-muted">JPG, JPEG, PNG, GIF (Max 1MB)</small>
                                </div>
                                <input type="file" class="form-control d-none" id="symbol" name="symbol" 
                                       accept="image/jpeg,image/jpg,image/png,image/gif" required>
                                <div class="image-preview mt-2" id="symbolPreview" style="display: none;">
                                    <img id="symbolPreviewImg" src="" alt="Preview">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-plus-circle me-2"></i>Add Candidate
                                </button>
                                <a href="candidates.php" class="btn btn-secondary btn-lg w-100 mt-2">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Candidates
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Initialize date picker
        flatpickr("#dob", {
            dateFormat: "Y-m-d",
            maxDate: "today"
        });

        // Image preview functionality
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreviewImg').src = e.target.result;
                    document.getElementById('imagePreview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('symbol').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('symbolPreviewImg').src = e.target.result;
                    document.getElementById('symbolPreview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        // Form validation
        document.getElementById('candidateForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const dob = document.getElementById('dob').value;
            const party = document.getElementById('nameofparty').value.trim();
            const image = document.getElementById('image').files.length;
            const symbol = document.getElementById('symbol').files.length;

            if (!name || !email || !dob || !party || !image || !symbol) {
                e.preventDefault();
                alert('Please fill in all required fields and upload both images.');
            }
        });
    </script>
</body>
</html>
