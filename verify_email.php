<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "skyline";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $message = ""; 
    if (isset($_GET['token'])) {
        $email = $_GET['key'];
        $token = $_GET['token'];
        $sql = "SELECT * FROM verification_tokens WHERE token = :token AND email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Token exists, perform verification
            $email = $result['email'];

            // Verify the token and update user's verification status
            $updateSql = "UPDATE users SET verified = 'verified' WHERE email = :email";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bindParam(':email', $email);
            if ($updateStmt->execute()) {
                // Delete the verification token from the database
                $deleteSql = "DELETE FROM verification_tokens WHERE email = :email";
                $deleteStmt = $conn->prepare($deleteSql);
                $deleteStmt->bindParam(':email', $email);
                if ($deleteStmt->execute()) {
                    $message = "Email verification successful. You can now log in to your account.";
                } else {
                    $message = "Error deleting verification token: " . $deleteStmt->errorInfo()[2];
                }
            }
        } else {
            $message = "Invalid verification token.";
        }
    } else {
        $message = "Error: Token not provided.";
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Email Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f1f1f1;
            padding: 20px;
        }

        .verification-message {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .verification-message h2 {
            color: #333;
        }

        .verification-message p {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="verification-message">
        <h2>Email Verification</h2>
        <p><?php echo $message; ?></p>
    </div>
</body>
</html>
