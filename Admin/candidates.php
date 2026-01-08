<?php
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = getDBConnection();

// Handle delete action
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Get candidate details to delete images
    $stmt = $conn->prepare("SELECT symbol, image FROM candidate WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $candidate = $result->fetch_assoc();
    
    if ($candidate) {
        // Delete images from server
        if (file_exists('../uploads/' . $candidate['symbol'])) {
            unlink('../uploads/' . $candidate['symbol']);
        }
        if (file_exists('../uploads/' . $candidate['image'])) {
            unlink('../uploads/' . $candidate['image']);
        }
        
        // Delete from database
        $stmt = $conn->prepare("DELETE FROM candidate WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $_SESSION['success'] = "Candidate deleted successfully!";
    }
    header("Location: candidates.php");
    exit();
}

// Get all candidates
$candidates = $conn->query("
    SELECT c.*, 
           COALESCE(v.vote_count, 0) as votes
    FROM candidate c
    LEFT JOIN (
        SELECT candidate_id, COUNT(*) as vote_count
        FROM votes
        GROUP BY candidate_id
    ) v ON c.id = v.candidate_id
    ORDER BY c.name ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Candidates - VoteSecure Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .candidate-card {
            transition: transform 0.3s;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .candidate-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }
        .candidate-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .symbol-img {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }
        .action-btn {
            margin: 0 2px;
        }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
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
                    <li class="nav-item">
                        <a class="nav-link" href="voters.php">
                            <i class="fas fa-user-check me-1"></i>Voters
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="results.php">
                            <i class="fas fa-chart-bar me-1"></i>Results
                        </a>
                    </li>
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

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">
                        <i class="fas fa-users me-3"></i>Manage Candidates
                    </h1>
                    <p class="mb-0">Add, edit, and manage election candidates with their party symbols and images</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="add-candidate.php" class="btn btn-light btn-lg">
                        <i class="fas fa-plus me-2"></i>Add New Candidate
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>All Candidates
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="candidatesTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Candidate Name</th>
                                <th>Party</th>
                                <th>Email</th>
                                <th>Date of Birth</th>
                                <th>Symbol</th>
                                <th>Votes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($candidate = $candidates->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <img src="../uploads/<?php echo htmlspecialchars($candidate['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($candidate['name']); ?>" 
                                         class="candidate-img">
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($candidate['name']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($candidate['nameofparty']); ?></td>
                                <td><?php echo htmlspecialchars($candidate['email']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($candidate['dob'])); ?></td>
                                <td>
                                    <img src="../uploads/<?php echo htmlspecialchars($candidate['symbol']); ?>" 
                                         alt="Symbol" 
                                         class="symbol-img">
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo $candidate['votes']; ?> votes</span>
                                </td>
                                <td>
                                    <a href="edit-candidate.php?id=<?php echo $candidate['id']; ?>" 
                                       class="btn btn-sm btn-success action-btn" 
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="confirmDelete(<?php echo $candidate['id']; ?>)" 
                                            class="btn btn-sm btn-danger action-btn" 
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#candidatesTable').DataTable({
                responsive: true,
                order: [[6, 'desc']],
                pageLength: 10,
                language: {
                    search: "Search candidates:",
                    lengthMenu: "Show _MENU_ candidates per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ candidates"
                }
            });
        });

        function confirmDelete(id) {
            if (confirm('Are you sure you want to delete this candidate? This action cannot be undone.')) {
                window.location.href = 'candidates.php?delete=' + id;
            }
        }
    </script>
</body>
</html>
