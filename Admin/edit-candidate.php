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

// Get candidate ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch candidate details
$stmt = $conn->prepare("SELECT * FROM candidate WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$candidate = $result->fetch_assoc();

if (!$candidate) {
    $_SESSION['error'] = "Candidate not found!";
    header("Location: candidates.php");
    exit();
}

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
    
    // Validate email (check if it's different from current)
    if ($email !== $candidate['email']) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        } else {
            $stmt = $conn->prepare("SELECT id FROM candidate WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $email, $id);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $errors[] = "Email already exists";
            }
        }
    }
    
    // Handle image uploads
    $imagePath = $candidate['image'];
    $symbolPath = $candidate['symbol'];
    
    // Upload new candidate image if provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = $_FILES['image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($image['type'], $allowedTypes)) {
            $errors[] = "Invalid image format. Only JPG, JPEG, PNG allowed";
        } elseif ($image['size'] > $maxSize) {
            $errors[] = "Image size must be less than 2MB";
        } else {
            // Delete old image
            if (file_exists('../uploads/' . $imagePath)) {
                unlink('../uploads/' . $imagePath);
            }
            
            $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
            $imageName = uniqid('candidate_') . '.' . $ext;
            $imagePath = 'candidates/' . $imageName;
            
            if (!move_uploaded_file($image['tmp_name'], '../uploads/' . $imagePath)) {
                $errors[] = "Failed to upload candidate image";
            }
        }
    }
    
    // Upload new party symbol if provided
    if (isset($_FILES['symbol']) && $_FILES['symbol']['error'] === UPLOAD_ERR_OK) {
        $symbol = $_FILES['symbol'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        $maxSize = 1 * 1024 * 1024; // 1MB
        
        if (!in_array($symbol['type'], $allowedTypes)) {
            $errors[] = "Invalid symbol format. Only JPG, JPEG, PNG, GIF allowed";
        } elseif ($symbol['size'] > $maxSize) {
            $errors[] = "Symbol size must be less than 1MB";
        } else {
            // Delete old symbol
            if (file_exists('../uploads/' . $symbolPath)) {
                unlink('../uploads/' . $symbolPath);
            }
            
            $ext = pathinfo($symbol['name'], PATHINFO_EXTENSION);
            $symbolName = uniqid('symbol_') . '.' . $ext;
            $symbolPath = 'symbols/' . $symbolName;
            
            if (!move_uploaded_file($symbol['tmp_name'], '../uploads/' . $symbolPath)) {
                $errors[] = "Failed to upload party symbol";
            }
        }
    }
    
    // Update database if no errors
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE candidate SET name = ?, dob = ?, email = ?, nameofparty = ?, symbol = ?, image = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $name, $dob, $email, $nameofparty, $symbolPath, $imagePath, $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Candidate updated successfully!";
            header("Location: candidates.php");
            exit();
        } else {
            $errors[] = "Database error: " . $conn->error;
        }
    }
} else {
    // Pre-fill form with existing data
    $name = $candidate['name'];
    $dob = $candidate['dob'];
    $email = $candidate['email'];
    $nameofparty = $candidate['nameofparty'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Candidate - VoteSecure Admin</title>
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
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
            position: relative;
        }
        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .current-image {
            border: 2px solid #28a745;
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
            border-color: #f093fb;
            background-color: #fff5f5;
        }
        .upload-zone.dragover {
            border-color: #f093fb;
            background-color: #ffe0e0;
        }
        .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(255, 0, 0, 0.7);
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
            font-size: 12px;
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
                            <i class="fas fa-user-edit me-2"></i>Edit Candidate
                        </h3>
                        <p class="mb-0 mt-2">Update candidate information and images</p>
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
                                       value="<?php echo htmlspecialchars($name); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="dob" class="form-label">
                                    <i class="fas fa-calendar me-1"></i>Date of Birth *
                                </label>
                                <input type="date" class="form-control" id="dob" name="dob" 
                                       value="<?php echo htmlspecialchars($dob); ?>" required>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>Email Address *
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($email); ?>" required>
                            </div>
                        </div>

                        <!-- Party Information -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-flag me-2"></i>Party Information
                                </h5>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label for="nameofparty" class="form-label">
                                    <i class="fas fa-building me-1"></i>Party Name *
                                </label>
                                <input type="text" class="form-control" id="nameofparty" name="nameofparty" 
                                       value="<?php echo htmlspecialchars($nameofparty); ?>" required>
                            </div>
                        </div>

                        <!-- Image Uploads -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-images me-2"></i>Images
                                </h5>
                                <p class="text-muted">Leave empty to keep current images</p>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-portrait me-1"></i>Candidate Photo
                                </label>
                                
                                <!-- Current Image -->
                                <div class="mb-2">
                                    <small class="text-muted">Current:</small>
                                    <div class="image-preview current-image">
                                        <img src="../uploads/<?php echo htmlspecialchars($candidate['image']); ?>" 
                                             alt="Current Photo">
                                    </div>
                                </div>
                                
                                <!-- New Image Upload -->
                                <div class="upload-zone" onclick="document.getElementById('image').click()">
                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                    <p class="mb-1">Click to upload new photo</p>
                                    <small class="text-muted">JPG, PNG, JPEG (Max 2MB)</small>
                                    <input type="file" id="image" name="image" accept="image/*" style="display: none;">
                                </div>
                                <div id="imagePreview" class="image-preview mt-2" style="display: none;">
                                    <img id="imagePreviewImg" src="" alt="New Preview">
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-icons me-1"></i>Party Symbol
                                </label>
                                
                                <!-- Current Symbol -->
                                <div class="mb-2">
                                    <small class="text-muted">Current:</small>
                                    <div class="image-preview current-image">
                                        <img src="../uploads/<?php echo htmlspecialchars($candidate['symbol']); ?>" 
                                             alt="Current Symbol">
                                    </div>
                                </div>
                                
                                <!-- New Symbol Upload -->
                                <div class="upload-zone" onclick="document.getElementById('symbol').click()">
                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                    <p class="mb-1">Click to upload new symbol</p>
                                    <small class="text-muted">JPG, PNG, JPEG, GIF (Max 1MB)</small>
                                    <input type="file" id="symbol" name="symbol" accept="image/*" style="display: none;">
                                </div>
                                <div id="symbolPreview" class="image-preview mt-2" style="display: none;">
                                    <img id="symbolPreviewImg" src="" alt="New Preview">
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between">
                                    <a href="candidates.php" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Update Candidate
                                    </button>
                                </div>
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
            maxDate: "today",
            theme: "material_red"
        });

        // Image preview functionality
        function handleImageUpload(inputId, previewId, imgId) {
            const input = document.getElementById(inputId);
            const preview = document.getElementById(previewId);
            const img = document.getElementById(imgId);
            
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        img.src = e.target.result;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // Initialize image previews
        handleImageUpload('image', 'imagePreview', 'imagePreviewImg');
        handleImageUpload('symbol', 'symbolPreview', 'symbolPreviewImg');

        // Drag and drop functionality
        function setupDragAndDrop(zoneId, inputId) {
            const zone = document.querySelector(`[onclick="document.getElementById('${inputId}').click()"]`);
            const input = document.getElementById(inputId);

            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                zone.classList.add('dragover');
            });

            zone.addEventListener('dragleave', () => {
                zone.classList.remove('dragover');
            });

            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                zone.classList.remove('dragover');
                input.files = e.dataTransfer.files;
                input.dispatchEvent(new Event('change'));
            });
        }

        setupDragAndDrop('upload-zone', 'image');
        setupDragAndDrop('upload-zone', 'symbol');
    </script>
</body>
</html>
