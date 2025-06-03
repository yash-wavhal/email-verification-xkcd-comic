<?php
require_once 'functions.php';

// Log the start of the process
error_log("Starting XKCD comic distribution at " . date('Y-m-d H:i:s'));

try {
    // Send comics to all subscribers using the sendXKCDUpdatesToSubscribers from functions.php
    sendXKCDUpdatesToSubscribers();
    error_log("Successfully sent XKCD comics to subscribers");
} catch (Exception $e) {
    error_log("Error sending XKCD comics: " . $e->getMessage());
} 