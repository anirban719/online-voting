<?php
require_once '../config.php';
require_once '../includes/voting_functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = getDBConnection();

// Get user details
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Check if user has already voted
$stmt = $conn->prepare("SELECT id FROM votes WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$has_voted = $stmt->get_result()->num_rows > 0;

// Get voting time information
$votingTime = getVotingTime();
$isVotingOpen = isVotingOpen($votingTime);

// Get all candidates
$candidates = $conn->query("SELECT * FROM candidate ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Voting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Voting System</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Welcome, <?php echo htmlspecialchars($user['name']); ?></span>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h5>User Profile</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p><strong>Mobile:</strong> <?php echo htmlspecialchars($user['mobile']); ?></p>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($user['villege'] . ', ' . $user['dis'] . ', ' . $user['state']); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h5>Vote for Your Candidate</h5>
                    </div>
                    <div class="card-body">
                        <!-- Voting Time Status -->
                        <div class="alert <?php echo $isVotingOpen ? 'alert-success' : 'alert-warning'; ?> mb-4">
                            <h5 class="alert-heading">
                                <i class="fas fa-clock me-2"></i>Voting Status
                            </h5>
                            <?php if ($isVotingOpen): ?>
                                <p class="mb-1"><strong>Voting is currently OPEN!</strong></p>
                                <p class="mb-0">You can cast your vote now. Voting period ends on <?php echo date('F j, Y g:i A', strtotime($votingTime['end'])); ?></p>
                                <div class="mt-2">
                                    <strong>Time remaining:</strong>
                                    <div id="countdown-timer" class="fw-bold text-success"></div>
                                </div>
                            <?php else: ?>
                                <p class="mb-1"><strong>Voting is currently CLOSED.</strong></p>
                                <p class="mb-0">
                                    <?php if (new DateTime() < new DateTime($votingTime['start'])): ?>
                                        Voting will open on <?php echo date('F j, Y g:i A', strtotime($votingTime['start'])); ?>
                                    <?php else: ?>
                                        Voting period ended on <?php echo date('F j, Y g:i A', strtotime($votingTime['end'])); ?>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <?php if ($has_voted): ?>
                            <div class="alert alert-info">
                                <h4>Thank you for voting!</h4>
                                <p>You have already cast your vote. Your participation is appreciated.</p>
                            </div>
                        <?php elseif (!$isVotingOpen): ?>
                            <div class="alert alert-danger">
                                <h4>Voting Not Available</h4>
                                <p>You cannot vote at this time. Please check the voting schedule above.</p>
                            </div>
                        <?php else: ?>
                            <form action="vote_process.php" method="POST" class="needs-validation" novalidate>
                                <div class="row">
                                    <?php while($candidate = $candidates->fetch_assoc()): ?>
                                        <div class="col-md-6 mb-4">
                                            <div class="card candidate-card">
                                                <div class="row g-0">
                                                    <div class="col-md-4">
                                                        <img src="../uploads/<?php echo $candidate['image']; ?>" 
                                                             alt="<?php echo htmlspecialchars($candidate['name']); ?>" 
                                                             class="img-fluid rounded-start" style="height: 150px; object-fit: cover;">
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div class="card-body">
                                                            <h5 class="card-title"><?php echo htmlspecialchars($candidate['name']); ?></h5>
                                                            <p class="card-text">
                                                                <strong>Party:</strong> <?php echo htmlspecialchars($candidate['nameofparty']); ?><br>
                                                                <strong>Symbol:</strong> <img src="../uploads/<?php echo $candidate['symbol']; ?>" alt="Symbol" style="width: 50px; height: 50px; object-fit: contain;">
                                                            </p>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" 
                                                                       name="candidate_id" 
                                                                       value="<?php echo $candidate['id']; ?>" 
                                                                       id="candidate<?php echo $candidate['id']; ?>" required>
                                                                <label class="form-check-label" for="candidate<?php echo $candidate['id']; ?>">
                                                                    Vote for <?php echo htmlspecialchars($candidate['name']); ?>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                                
                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('Are you sure you want to vote for this candidate?')">
                                        Give Vote
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container text-center">
            <p>&copy; 2024 Online Voting System. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Countdown timer for voting period
        <?php if ($isVotingOpen): ?>
        function updateCountdown() {
            const endTime = new Date('<?php echo $votingTime['end']; ?>').getTime();
            const now = new Date().getTime();
            const distance = endTime - now;
            
            if (distance < 0) {
                document.getElementById('countdown-timer').innerHTML = "Voting period has ended!";
                return;
            }
            
            // Calculate time components
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            // Display the result
            document.getElementById('countdown-timer').innerHTML = 
                hours.toString().padStart(2, '0') + ":" + 
                minutes.toString().padStart(2, '0') + ":" + 
                seconds.toString().padStart(2, '0');
        }
        
        // Update countdown every second
        updateCountdown();
        setInterval(updateCountdown, 1000);
        <?php endif; ?>
    </script>
<!-- // show total voter -->
 <?php
require_once '../config.php'; // include your DB connection

$conn = getDBConnection();
$sql = "SELECT COUNT(*) AS total_voters FROM user";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$totalVoters = $row['total_voters'];
?>

    <h1>Total Registered Voters: <?= htmlspecialchars($totalVoters); ?></h1>
</body>
</html>
