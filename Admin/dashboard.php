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
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="add-candidate.php">
                            <i class="fas fa-users me-1"></i>add-candidate
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="vote_time.php">
                            <i class="fas fa-clock me-1"></i>Set Voting Time
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

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Quick Stats</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <!-- <div class="list-group-item d-flex justify-content-between align-items-center">
                            Total Voters
                            <span class="badge bg-primary rounded-pill"><?php echo $total_users; ?></span>
                        </div> -->
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Total Candidates
                            <span class="badge bg-success rounded-pill"><?php echo $total_candidates; ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Total Votes Cast
                            <span class="badge bg-warning rounded-pill"><?php echo $total_votes; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-users fa-3x text-primary mb-3"></i>
                                <h3 class="text-primary"><?php echo $total_users; ?></h3>
                                <p class="text-muted">Registered Voters</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <a href="showTotalCandidate.php" class="text-decoration-none text-dark">
                            <div class="card text-center">
                                <div class="card-body">
                                    
                                    <i class="fas fa-user-tie fa-3x text-success mb-3"></i>
                                    <h3 class="text-success"><?php echo $total_candidates; ?></h3>
                                    <p class="text-muted">Candidates</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="candidates.php" class="text-decoration-none text-dark">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-vote-yea fa-3x text-warning mb-3"></i>
                                <h3 class="text-warning">manage candidate</h3>
                                <p class="text-muted">Edit/Delete</p>
                            </div>
                        </div>
</a>
                    </div>
                    <div class="col-md-3">
                        <a href="vote_time.php" class="text-decoration-none text-dark">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-clock fa-3x text-info mb-3"></i>
                                <h3 class="text-info">Set Voting Time</h3>
                                <p class="text-muted">Time Settings</p>
                            </div>
                        </div>
</a>
                    </div>
                </div>

                <!-- Voting Results -->
                <!-- <div class="card mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>Live Voting Results
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Candidate</th>
                                        <th>Party</th>
                                        <th>Votes</th>
                                        <th>Percentage</th>
                                        <th>Progress</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $rank = 1;
                                    while($row = $results->fetch_assoc()): 
                                        $percentage = $total_votes > 0 ? round(($row['votes'] / $total_votes) * 100, 2) : 0;
                                    ?>
                                    <tr>
                                        <td><?php echo $rank++; ?></td>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nameofparty']); ?></td>
                                        <td><?php echo $row['votes']; ?></td>
                                        <td><?php echo $percentage; ?>%</td>
                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" style="width: <?php echo $percentage; ?>%">
                                                    <?php echo $percentage; ?>%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div> -->

                
                
                </div>

                <!-- Recent Voters -->
                <!-- <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-clock me-2"></i>Recent Voters
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Voter Name</th>
                                        <th>Email</th>
                                        <th>Vote Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($voter = $recent_voters->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($voter['name']); ?></td>
                                        <td><?php echo htmlspecialchars($voter['email']); ?></td>
                                        <td><?php echo date('M d, Y H:i A', strtotime($voter['voted_at'])); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div> -->
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
