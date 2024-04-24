<?php
require_once 'db/db_connection.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Load Composer's autoloader
    require 'phpmailer/Exception.php';
    require 'phpmailer/Phpmailer.php';
    require 'phpmailer/SMTP.php';

    // Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                       // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = 'abhiseklabala143@gmail.com';           // SMTP username
        $mail->Password   = 'ugyq cygu kvnk kaqj';                  // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            // Enable implicit TLS encryption
        $mail->Port       = 465;                                    // TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        // Recipients
        $mail->setFrom('abhiseklabala143@gmail.com', 'User');
        
        // Get recipient email from the form input
        $recipient_email = $_POST['email'];

        // Check if email exists in the database
        $query = "SELECT * FROM regforms WHERE email = ?";
        $result = pg_query($con, $query);
        $user = pg_fetch_assoc($result);

        if ($user) {
            // Email exists in the database, generate a unique token
            $token = bin2hex(random_bytes(32));

            // Save the token and expiry time in the database
            $query2 = "UPDATE regforms SET reset_token_hash = ? WHERE email = ?";
            $result2 =pg_query($result2,$query2);
            $reset_link ="http://localhost/myproject/updatepassword.php";
            // Craft the email
            $mail->addAddress($recipient_email); // Add a recipient
            $mail->isHTML(true);                                   // Set email format to HTML
            $mail->Subject = 'Reset Your Password';
            $mail->Body    ='Click <a href="' . $reset_link . '">here</a> to reset your password. This link will expire in 10 minutes.';

            // Send the email
            $mail->send();
            echo 'Message has been sent';
            
            // Redirect to login.php and show alert message
            echo '<script>alert("Email sent. Check your inbox to reset password.");';
            echo 'window.location.href = "login.php";</script>';
        } else {
            // Email does not exist in the database, show an error message
            echo '<script>alert("Your mail is not register.Kindly enter correct email.");';
            echo 'window.location.href = "forgetpassword.php";</script>';
        }
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    } 
}
?>
