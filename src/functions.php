<?php

/**
 * Generate a 6-digit numeric verification code.
 */
function generateVerificationCode(): string {
    return str_pad(strval(rand(0, 999999)), 6, '0', STR_PAD_LEFT);       //Generate number of 6 digits
}

/**
 * Send a verification code to an email.
 */
function sendVerificationEmail(string $email, string $code): bool {
    $subject = "Your Verification Code";         //Email subject
    $message = "<p>Your verification code is: <strong>$code</strong></p>";    //Email body in HTML

    $headers = array(
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',       // Specify content type as HTML
        'From: no-reply@example.com',      // Sender email
        'X-Mailer: PHP/' . phpversion()
    );

    return mail($email, $subject, $message, implode("\r\n", $headers));      //Sending email using mail() function
}

/**
 * Register an email by storing it in a file.
 */
function registerEmail(string $email): bool {
    $file = __DIR__ . '/registered_emails.txt';
    $emails = file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES) : [];      // Reads existing emails
    if (!in_array($email, $emails)) {                   //It Checks if email already registered or not
        file_put_contents($file, $email . PHP_EOL, FILE_APPEND);           //It Appends email to registered_emails.txt file
        return true;
    }
    return false;
}

/**
 * Unsubscribe an email by removing it from the list.
 */
function unsubscribeEmail(string $email): bool {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return false;            // If file doesn't exist it return false
    $emails = file($file, FILE_IGNORE_NEW_LINES);     //It Reads all emails
    $emails = array_filter($emails, fn($e) => trim($e) !== trim($email));     //This Removes target email
    file_put_contents($file, implode(PHP_EOL, $emails) . PHP_EOL);      //It writes the updated list again after deleting the unsubscribed email
    return true;
}

/**
 * Fetch random XKCD comic and format data as HTML.
 */
function fetchAndFormatXKCDData(): string {
    $id = rand(1, 2800);     //It Generates random XKCD comic ID
    $data = json_decode(file_get_contents("https://xkcd.com/$id/info.0.json"), true);   // Fetching comic in JSON

    if (!$data) {
        throw new Exception("Failed to fetch XKCD comic data"); // It Handles the failure during fetching the data
    }

    $img = htmlspecialchars($data['img']);     // image URL is stored in the img

    return "<h2>XKCD Comic</h2>
        <img src=\"$img\" alt=\"XKCD Comic\">
        <p>
            <a href=\"http://localhost:8000/unsubscribe.php\"
                style=\"
                display: inline-block;
                padding: 10px 20px;
                background-color: #3498db;
                color: white;
                text-decoration: none;
                border-radius: 4px;
                font-weight: bold;\"
                id=\"unsubscribe-button\"
            >
                Unsubscribe
            </a>
        </p>";
}

/**
 * Send the formatted XKCD updates to registered emails.
 */
function sendXKCDUpdatesToSubscribers(): void {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return;      //Break or exit if file is empty or file does not exists
    
    $emails = file($file, FILE_IGNORE_NEW_LINES);    //IT gets the list of emails
    if (empty($emails)) return;

    $subject = 'Your Daily XKCD Comic';
    $message = fetchAndFormatXKCDData();    //It Gets comic in HTML content and stores it in message
    
    $headers = array(
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: no-reply@example.com',
        'X-Mailer: PHP/' . phpversion()
    );

    foreach ($emails as $email) {
        mail($email, $subject, $message, implode("\r\n", $headers));       //Send email to each subscriber with subject and message in it
    }
}
