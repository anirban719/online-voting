<?php
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = getDBConnection();

// Get statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM user")->fetch_assoc()['count'];
$total_candidates = $conn->query("SELECT COUNT(*) as count FROM candidate")->fetch_assoc()['count'];
$total_votes = $conn->query("SELECT COUNT(*) as count FROM votes")->fetch_assoc()['count'];

// Get voting results
$results = $conn->query("
    SELECT c.name, c.nameofparty, c.symbol, COUNT(v.id) as votes
    FROM candidate c
    LEFT JOIN votes v ON c.id = v.candidate_id
    GROUP BY c.id
    ORDER BY votes DESC
");

// Get recent voters
$recent_voters = $conn->query("
    SELECT u.name, u.email, v.voted_at
    FROM user u
    JOIN votes v ON u.id = v.user_id
    ORDER BY v.voted_at DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - VoteSecure</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
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
                        <a class="nav-link" href="add-candidate.php">
                            <i class="fas fa-users me-1"></i>add-candidate
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
                </ul>
                <!-- <div class="navbar-nav">
                    <span class="navbar-text me-3">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </a>
                </div> -->
            </div>
        </div>
    </nav>
   <br><br>




<!-- All Candidates -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-users me-2"></i>All Candidates
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Candidate</th>
                                        <th>Party</th>
                                        <th>Email</th>
                                        <th>DOB</th>
                                        <th>Symbol</th>
                                        <!-- <th>Votes</th> -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Get all candidates for dashboard display
                                    $all_candidates = $conn->query("
                                        SELECT c.*, COALESCE(v.vote_count, 0) as votes
                                        FROM candidate c
                                        LEFT JOIN (
                                            SELECT candidate_id, COUNT(*) as vote_count
                                            FROM votes
                                            GROUP BY candidate_id
                                        ) v ON c.id = v.candidate_id
                                        ORDER BY c.name ASC
                                    ");
                                    
                                    while($candidate = $all_candidates->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <img src="../uploads/<?php echo htmlspecialchars($candidate['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($candidate['name']); ?>" 
                                                 class="rounded-circle" width="40" height="40">
                                        </td>
                                        <td><strong><?php echo htmlspecialchars($candidate['name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($candidate['nameofparty']); ?></td>
                                        <td><?php echo htmlspecialchars($candidate['email']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($candidate['dob'])); ?></td>
                                        <td>
                                            <img src="../uploads/<?php echo htmlspecialchars($candidate['symbol']); ?>" 
                                                 alt="Symbol" width="30" height="30">
                                        </td>
                                        <!-- <td>
                                            <span class="badge bg-info"><?php echo $candidate['votes']; ?> votes</span>
                                        </td> -->
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- <div class="text-center mt-3">
                            <a href="candidates.php" class="btn btn-success">
                                <i class="fas fa-external-link-alt me-2"></i>Manage All Candidates
                            </a>
                        </div> -->
                    </div>
                </div>
