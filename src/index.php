<?php
require_once 'functions.php';
session_start();   //Starting the session to store verification data temporarily

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Email submitted for verification
    if (isset($_POST['submit_email'])) {
        $email = trim($_POST['email']);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {  // Validating email format
            $code = generateVerificationCode();
            $_SESSION['email_verification'] = ['email' => $email, 'code' => $code];    //Storing email and code in session
            if (sendVerificationEmail($email, $code)) {          // Sending the code via email using sendVerificationEmail
                $success = "Verification code sent to $email. Please check your inbox.";
            } else {
                $error = "Failed to send verification email.";
            }
        } else {
            $error = "Invalid email format.";
        }
    }

    // Code submitted for verification
    if (isset($_POST['submit_code'])) {
        $code = trim($_POST['verification_code']);
        if (isset($_SESSION['email_verification']) && $code === $_SESSION['email_verification']['code']) {       //If session has email and the code matches
            $email = $_SESSION['email_verification']['email'];     //Retrieving email from session
            if (registerEmail($email)) {        // Registering the email using registerEmail function
                $success = "Email verified and registered successfully!";
                unset($_SESSION['email_verification']);   //Remove session data after successful registratiion
            } else {
                $error = "Failed to register email. Might already be registered.";
            }
        } else {
            $error = "Invalid verification code.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Email Registration</title>
    <link rel="stylesheet" href="assets/index.css">
</head>
<body>
    <h2>Email Registration</h2>

    <?php if ($success): ?>
        <p style="color: green;"><strong><?= $success ?></strong></p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p style="color: red;"><strong><?= $error ?></strong></p>
    <?php endif; ?>

    <!-- Form for entering the user's email -->
    <form method="POST">
        <label for="email">Enter Email to Subscribe:</label><br>
        <input type="email" name="email" required>
        <button type="submit" name="submit_email" id="submit-email">Submit</button>
    </form>

    <br><hr><br>
    
    <!-- Form for entering the verification code -->
    <form method="POST">
        <label for="verification_code">Enter Verification Code:</label><br>
        <input type="text" name="verification_code" maxlength="6" required>
        <input type="hidden" name="email" value="<?= isset($_SESSION['email_verification']['email']) ? htmlspecialchars($_SESSION['email_verification']['email']) : '' ?>">
        <button type="submit" name="submit_code" id="submit-verification">Verify</button>
    </form>
</body>
</html>
