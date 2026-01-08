<?php
require_once '../config.php';
require_once '../includes/voting_functions.php'; // Include the voting time logic

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $candidate_id = intval($_POST['candidate_id']);
    
    $conn = getDBConnection();
    
    // Check if user has already voted
    $stmt = $conn->prepare("SELECT id FROM votes WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = "You have already voted!";
        header("Location: dashboard.php");
        exit();
    }
                        
    // Check if voting is open
    $votingTime = getVotingTime();
    
    if (!isVotingOpen($votingTime)) {
        $_SESSION['error'] = "Voting is currently closed! Please try again during the voting period.";
        header("Location: dashboard.php");
        exit();
    }
    
    // Record the vote
    $stmt = $conn->prepare("INSERT INTO votes (user_id, candidate_id, voted_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("ii", $user_id, $candidate_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Your vote has been recorded successfully!";
    } else {
        $_SESSION['error'] = "Failed to record vote. Please try again.";
    }
    
    header("Location: dashboard.php");
    exit();
}
?>
