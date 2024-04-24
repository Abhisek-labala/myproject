<?php
include 'db/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $upass = $_POST['upassword'];
    $ucpass = $_POST['ucpassword'];

    if ($upass == $ucpass) {
        $phash = password_hash($upass, PASSWORD_DEFAULT);
        
        // Prepare SQL statement
        $sql = "UPDATE regforms SET `password` = ?, `reset_token_hash` = NULL email = ?";
        $success=pg_query($conn,$sql);

        if ($success) {
            
            echo '<script>alert("Password Updated successfull.You can Login now");';
            echo 'window.location.href = "login.php?token=$token";</script>';

        } else {
            echo "Error updating password: " . mysqli_error($con);
        }

        // Close statement and connection
       pg_close($conn);
    } else {
        echo '<script>alert("Password do not match.Kindly check the password you entered.");';
        echo 'window.location.href = "updatepassword.php";</script>';
    }
}
?>
