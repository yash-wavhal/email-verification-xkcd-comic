<?php
require_once 'functions.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handles sending of confirmation code
    if (isset($_POST['send_code'])) {
        $email = strtolower(trim($_POST['unsubscribe_email'])); // Must match required name

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {      //checks for valid email format
            $code = generateVerificationCode();
            file_put_contents(__DIR__ . "/unsub_{$email}.txt", $code);   //It saves code in file named by email

            //following is the Email content
            $subject = "Confirm Un-subscription";
            $body = "<p>To confirm un-subscription, use this code: <strong>$code</strong></p>";
            $headers = [
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8',
                'From: no-reply@example.com',
                'X-Mailer: PHP/' . phpversion()
            ];

            if (mail($email, $subject, $body, implode("\r\n", $headers))) {
                $success = "Confirmation code sent to $email. Please check your inbox.";    //Success message if confirmations code is sent successfully
            } else {
                $error = "Failed to send confirmation code email.";   //Otherwise failure message if confirmations code fails to send
            }
        } else {
            $message = "Invalid email format.";
        }
    }

    //Handling confirmation of unsubscribe
    elseif (isset($_POST['confirm_unsubscribe'])) {
        $email = strtolower(trim($_POST['unsubscribe_email_verify']));
        $code = trim($_POST['verification_code']);    //Getting the entered verification code
        $filePath = __DIR__ . "/unsub_{$email}.txt";

        if (!file_exists($filePath)) {
            $message = "No unsubscription request found for this email.";       //It showed when the wrong email entered or if no correct code founds
        } else {
            $storedCode = file_get_contents($filePath);   // It reads stored code
            if ($code === $storedCode) {
                if (unsubscribeEmail($email)) {       // Calling function unsubscribeEmail from functions.php file to remove from subscription list
                    unlink($filePath);             // Deleting code file after successful unsubscribe
                    $success = "You have been unsubscribed successfully.";
                } else {
                    $error = "Email not found in subscription list.";
                }
            } else {
                $error = "Invalid confirmation code.";     //It showed when incorrect code entered
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Unsubscribe</title>
    <link rel="stylesheet" href="assets/unsubscribe.css">
</head>
<body>
    <h2>Unsubscribe from XKCD Comics</h2>

    <!-- Form to send unsubscribe code  -->
    <form method="POST">
        <label for="unsubscribe_email">Enter your email to unsubscribe:</label><br>
        <input type="email" id="unsubscribe_email" name="unsubscribe_email" required><br><br>
        <button type="submit" id="submit-unsubscribe" name="send_code">Unsubscribe</button>
    </form>

    <hr>

    <!-- Form to verify the code and unsubscribe -->
    <form method="POST">
        <label for="unsubscribe_email_verify">Enter your email:</label><br>
        <input type="email" id="unsubscribe_email_verify" name="unsubscribe_email_verify" required><br><br>

        <label for="verification_code">Enter confirmation code:</label><br>
        <input type="text" id="verification_code" name="verification_code" maxlength="6" required><br><br>

        <button type="submit" id="submit-verification" name="confirm_unsubscribe">Verify</button>
    </form>

    <?php if ($success): ?>
        <p style="color: green;"><strong><?= $success ?></strong></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p style="color: red;"><strong><?= $error ?></strong></p>
    <?php endif; ?>
</body>
</html>
