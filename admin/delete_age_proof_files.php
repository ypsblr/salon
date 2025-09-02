<?php
header('Content-type: text/plain');

// Database connection parameters
$host = "localhost";
$dbname = "ypsin_salondb";
$username = "ypsin_salondbadm";
$password = "MCnLOT8045FVzC1Y";

echo getcwd() . "\n";

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Reference date (1st June 2024)
    $referenceDate = new DateTime('2024-06-01');
    
    // Query to fetch all profiles
    $query = "SELECT profile_id, date_of_birth, age_proof_file FROM profile";
    $stmt = $pdo->query($query);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $dob = new DateTime($row['date_of_birth']);
        $age = $referenceDate->diff($dob)->y;
        
        // Check if age is greater than 18
        if ($age > 18) {
            $originalFile = $row['age_proof_file'];
            $fileDirectory = '/home/cwhys6gqg37b/public_html/salon/res/age_proof/'; // Update this with your actual file directory
            
            // Check if file exists
            if ($originalFile !== null && trim($originalFile) !== '') {
                if (file_exists($fileDirectory . $originalFile)) {
                    // Get file info
                    $fileInfo = pathinfo($originalFile);
                    $newFileName = 'toDelete_' . $fileInfo['filename'] . $fileInfo['extension'];
                    
                    // Rename the file
                    if (rename(
                        $fileDirectory . $originalFile, 
                        $fileDirectory . $newFileName
                    )) {
                        echo "Successfully renamed file for ID {$row['profile_id']}: {$originalFile} to {$newFileName}\n";
                    } else {
                        echo "Failed to rename file for ID {$row['profile_id']}: {$originalFile}\n";
                    }
                } else {
                    echo "File not found for ID {$row['profile_id']}: --{$originalFile}--\n";
                }
            }
        }
    }
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>