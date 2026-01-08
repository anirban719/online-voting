<?php
// Database setup script for VoteSecure system
// Run this file to create necessary tables

require_once 'config.php';

$conn = getDBConnection();

// Create user table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    email VARCHAR(50) NOT NULL UNIQUE,
    dob DATE NOT NULL,
    gender VARCHAR(50) NOT NULL,
    mobile INT(50) NOT NULL,
    villege VARCHAR(50) NOT NULL,
    po VARCHAR(50) NOT NULL,
    ps VARCHAR(50) NOT NULL,
    dis VARCHAR(50) NOT NULL,
    state VARCHAR(50) NOT NULL,
    pin INT(50) NOT NULL,
    password INT(50) NOT NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "User table created successfully<br>";
} else {
    echo "Error creating user table: " . $conn->error . "<br>";
}

// Create candidate table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS candidate (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    dob DATE NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    nameofparty VARCHAR(255) NOT NULL,
    symbol VARCHAR(255) NOT NULL,
    image VARCHAR(255) NOT NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "Candidate table created successfully<br>";
} else {
    echo "Error creating candidate table: " . $conn->error . "<br>";
}

// Create votes table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    candidate_id INT NOT NULL,
    voted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES candidate(id) ON DELETE CASCADE,
    UNIQUE KEY unique_vote (user_id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Votes table created successfully<br>";
} else {
    echo "Error creating votes table: " . $conn->error . "<br>";
}

// Insert sample data for testing
// Sample admin
// $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
// $conn->query("INSERT IGNORE INTO admin (name, email, password, phno) VALUES 
// ('Super Admin', 'admin@votesecure.com', '$admin_password', 1234567890)");

// Sample candidates
$conn->query("INSERT IGNORE INTO candidate (name, dob, email, nameofparty, symbol, image) VALUES 
('John Smith', '1980-05-15', 'john@democratic.com', 'Democratic Party', 'Donkey', 'john_smith.jpg'),
('Sarah Johnson', '1975-08-22', 'sarah@republican.com', 'Republican Party', 'Elephant', 'sarah_johnson.jpg'),
('Michael Brown', '1982-03-10', 'michael@independent.com', 'Independent Party', 'Eagle', 'michael_brown.jpg')");

echo "Database setup completed successfully!";
?>
