<?php
function getVotingTime() {
    $conn = getDBConnection();
    $result = $conn->query("SELECT start_time, end_time FROM voting_time ORDER BY id DESC LIMIT 1");
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return [
            'start' => $row['start_time'],
            'end' => $row['end_time']
        ];
    } else {
        
        return [
            'start' => '2023-01-01 00:00:00',
            'end' => '2023-01-01 00:00:00'
        ];
    }
}


function isVotingOpen($votingTime) {
    
    date_default_timezone_set('Asia/Kolkata'); 
    
    $currentTime = new DateTime();
    $startTime = new DateTime($votingTime['start']);
    $endTime = new DateTime($votingTime['end']);

    
    error_log("Current Time: " . $currentTime->format('Y-m-d H:i:s'));
    error_log("Start Time: " . $startTime->format('Y-m-d H:i:s'));
    error_log("End Time: " . $endTime->format('Y-m-d H:i:s'));
    error_log("Is Open: " . ($currentTime >= $startTime && $currentTime <= $endTime ? 'Yes' : 'No'));

    return $currentTime >= $startTime && $currentTime <= $endTime;
}
?>
