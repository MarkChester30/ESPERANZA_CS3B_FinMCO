<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require_once 'config/database.php';

header('Content-Type: application/json');

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form data
    $name = sanitize_input($_POST['name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $subject = sanitize_input($_POST['subject'] ?? '');
    $message = sanitize_input($_POST['message'] ?? '');

    // Validate inputs
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required'
        ]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email format'
        ]);
        exit;
    }

    try {
        // Store in database
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "INSERT INTO messages (name, email, subject, message) VALUES (:name, :email, :subject, :message)";
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":subject", $subject);
        $stmt->bindParam(":message", $message);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to store message in database");
        }

        // Send email
        $mail = new PHPMailer(true);

        // Basic server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'markchesteresperanza321@gmail.com';
        $mail->Password = 'gnkx wcdl xspr qcpt';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Disable SSL verification (only for testing)
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Set sender and recipient
        $mail->setFrom('markchesteresperanza321@gmail.com', 'Contact Form');
        $mail->addAddress('markchesteresperanza321@gmail.com');
        $mail->addReplyTo($email, $name);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = "Important Notice: " . $subject;
        $mail->Body = "
            <h3>IMPORTANT NOTICE</h3>
            <p><strong>Name:</strong> {$name}</p>
            <p><strong>Email:</strong> {$email}</p>
            <p><strong>Subject:</strong> {$subject}</p>
            <p><strong>Message:</strong></p>
            <p>" . nl2br($message) . "</p>
        ";

        // Try to send the email
        if (!$mail->send()) {
            throw new Exception($mail->ErrorInfo);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Message has been sent successfully!'
        ]);

    } catch (Exception $e) {
        // Log the error
        error_log("Error: " . $e->getMessage());
        
        // Return a more specific error message
        echo json_encode([
            'success' => false,
            'message' => 'Failed to process message: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}