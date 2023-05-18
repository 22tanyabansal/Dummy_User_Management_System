<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "skyline";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    require 'vendor/autoload.php';
    require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_POST["name"];
        $email = $_POST["email"];
        $mobile = $_POST["mobile"];

        // Save user details to the database
        $stmt = $conn->prepare("INSERT INTO users (name, email, mobile) VALUES (:name, :email, :mobile)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':mobile', $mobile);

        if ($stmt->execute()) {
            $token = md5(uniqid());
            // Save verification token to the database
            $tokenStmt = $conn->prepare("INSERT INTO verification_tokens (email, token) VALUES (:email, :token)");
            $tokenStmt->bindParam(':email', $email);
            $tokenStmt->bindParam(':token', $token);
            $tokenStmt->execute();

            // Send verification email
            $verificationLink = "http://localhost//skyline/verify_email.php?key=" . $email . "&token=" . $token;
            $subject = "Verify your email";
            $message = "Click the following link to verify your email: $verificationLink";

            $mail = new PHPMailer();
            $mail->isSMTP();
            $mail->SMTPAuth = true;
            $mail->SMTPDebug = 1;
            $mail->Host = 'smtp.gmail.com';  //gmail SMTP server
            $mail->Username = 'tb0680267@gmail.com';   //email
            $mail->Password = 'qchejzwqlajespcb';   //16 character obtained from app password created
            $mail->Port = 587;                    //SMTP port
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

            //sender information
            $mail->setFrom('tb0680267@gmail.com', 'Tanya Bansal');
            //receiver address and name
            $mail->addAddress($email, $name);
            $mail->Subject = $subject;
            $mail->Body = $message;

            if ($mail->send()) {
                ob_clean();
                echo "User added successfully. Please check your email for verification.";
            } else {
                echo "Error sending email: " . $mail->ErrorInfo;
            }
        } else {
            echo "Error inserting user: " . $stmt->errorInfo();
        }
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add User</title>
</head>
<style>
    body {
    font-family: Arial, sans-serif;
    background-color: #f1f1f1;
    padding: 20px;
}

h2 {
    color: #333;
}

form {
    background-color: #fff;
    padding: 20px;
    border-radius: 5px;
}

label {
    display: block;
    margin-bottom: 10px;
}

input[type="text"],
input[type="email"] {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
    margin-bottom: 15px;
}

input[type="submit"] {
    background-color: #4CAF50;
    color: #fff;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

input[type="submit"]:hover {
    background-color: #45a049;
}

        a {
            margin-left:20px;
            background-color: #333;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
        }

        a:hover {
            background-color: #555;
        }
</style>
<body>
    <h2>Add User</h2>
    <form action="add_user.php" method="POST">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required><br><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>

        <label for="mobile">Mobile:</label>
        <input type="text" id="mobile" name="mobile" required><br><br>

        <input type="submit" value="Add User">

        <a href="added_user.php">View Added Users</a>
    </form>
    
</body>
</html>
