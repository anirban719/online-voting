<?php
require_once '../config.php';

// Create voting_time table if it doesn't exist
function createVotingTimeTable() {
    $conn = getDBConnection();
    $sql = "CREATE TABLE IF NOT EXISTS voting_time (
        id INT AUTO_INCREMENT PRIMARY KEY,
        start_time DATETIME NOT NULL,
        end_time DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        return true;
    } else {
        return false;
    }
}

// Function to set voting time
function setVotingTime($startTime, $endTime) {
    $conn = getDBConnection();
    
    // First, clear any existing voting time entries
    $conn->query("DELETE FROM voting_time");
    
    // Insert new voting time
    $stmt = $conn->prepare("INSERT INTO voting_time (start_time, end_time) VALUES (?, ?)");
    $stmt->bind_param("ss", $startTime, $endTime);
    
    if ($stmt->execute()) {
        return [
            'start' => $startTime,
            'end' => $endTime
        ];
    } else {
        return false;
    }
}

// Include shared voting functions
require_once '../includes/voting_functions.php';

// Create table if it doesn't exist
createVotingTimeTable();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $startTime = $_POST['start_time'];
    $endTime = $_POST['end_time'];
    
    // Validate that end time is after start time
    if (new DateTime($endTime) <= new DateTime($startTime)) {
        $_SESSION['error'] = "End time must be after start time.";
    } else {
        if (setVotingTime($startTime, $endTime)) {
            $_SESSION['success'] = "Voting time has been set successfully!";
        } else {
            $_SESSION['error'] = "Failed to set voting time. Please try again.";
        }
    }
    
    header("Location: vote_time.php");
    exit();
}

// Get current voting time
$currentVotingTime = getVotingTime();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Voting Time - VoteSecure</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">
                <i class="fas fa-vote-yea me-2"></i>VoteSecure Admin
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
                        <a class="nav-link" href="add-candidate.php">
                            <i class="fas fa-users me-1"></i>Add Candidate
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="vote_time.php">
                            <i class="fas fa-clock me-1"></i>Set Voting Time
                        </a>
                    </li>
                </ul>
                <div class="navbar-nav">
                    <span class="navbar-text me-3">Welcome, <?php echo isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Admin'; ?></span>
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-clock me-2"></i>Set Voting Time
                        </h4>
                    </div>
                    <div class="card-body">
                        <!-- Success/Error Messages -->
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Current Voting Time Info -->
                        <div class="alert alert-info">
                            <h5 class="alert-heading">
                                <i class="fas fa-info-circle me-2"></i>Current Voting Time
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Start:</strong> <?php echo date('F j, Y g:i A', strtotime($currentVotingTime['start'])); ?>
                                </div>
                                <div class="col-md-6">
                                    <strong>End:</strong> <?php echo date('F j, Y g:i A', strtotime($currentVotingTime['end'])); ?>
                                </div>
                            </div>
                            <hr>
                            <strong>Status:</strong> 
                            <?php if (isVotingOpen($currentVotingTime)): ?>
                                <span class="badge bg-success">Voting Open</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Voting Closed</span>
                            <?php endif; ?>
                            
                            <!-- Debug information (remove after testing) -->
                            <div class="mt-2 small text-muted">
                                <strong>Debug Info:</strong><br>
                                Server Time: <?php echo date('Y-m-d H:i:s'); ?><br>
                                Start: <?php echo $currentVotingTime['start']; ?><br>
                                End: <?php echo $currentVotingTime['end']; ?>
                            </div>
                        </div>

                        <!-- Voting Time Form -->
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="start_time" class="form-label">
                                        <i class="fas fa-play-circle me-1"></i>Start Time
                                    </label>
                                    <input type="datetime-local" class="form-control" id="start_time" name="start_time" required>
                                    <div class="invalid-feedback">
                                        Please select a start time.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="end_time" class="form-label">
                                        <i class="fas fa-stop-circle me-1"></i>End Time
                                    </label>
                                    <input type="datetime-local" class="form-control" id="end_time" name="end_time" required>
                                    <div class="invalid-feedback">
                                        Please select an end time.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="dashboard.php" class="btn btn-secondary me-md-2">
                                    <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Set Voting Time
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>
</html>
