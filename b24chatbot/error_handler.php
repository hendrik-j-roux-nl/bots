<?php
// error_handler.php

function handleError($errorCode, $errorMessage, $errorDetails = null) {
    http_response_code($errorCode);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Installation Error</title>
        <link rel="stylesheet" href="styles/normalize.css">
        <link rel="stylesheet" href="styles/main.css">
        <link rel="stylesheet" href="styles/error.css">
    </head>
    <body>
        <h1>Installation Error</h1>
        <div class="error-code">Error Code: <?php echo $errorCode; ?></div>
        <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php if ($errorDetails): ?>
            <h2>Error Details:</h2>
            <div class="error-details"><?php echo htmlspecialchars($errorDetails); ?></div>
        <?php endif; ?>
        <p>Please correct the error and try installing the application again. If the problem persists, contact support.</p>
    </body>
    </html>
    <?php
    exit;
}

function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $errorType = [
        E_ERROR => 'Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse Error',
        E_NOTICE => 'Notice',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Strict',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED => 'Deprecated',
        E_USER_DEPRECATED => 'User Deprecated',
    ];

    $errorMessage = isset($errorType[$errno]) ? $errorType[$errno] : 'Unknown Error';
    
    $logMessage = date('[Y-m-d H:i:s]') . " $errorMessage: $errstr in $errfile on line $errline\n";
    
    // Log the error
    error_log($logMessage, 3, LOG_PATH . '/error.log');
    
    // If it's a fatal error, we might want to display an error page
    if ($errno == E_ERROR || $errno == E_USER_ERROR) {
        handleError(500, $errorMessage, "$errstr in $errfile on line $errline");
    }
    
    // Don't execute PHP's internal error handler
    return true;
}

// Set the custom error handler
set_error_handler("customErrorHandler");

// Function to handle uncaught exceptions
function exceptionHandler($exception) {
    $logMessage = date('[Y-m-d H:i:s]') . ' Uncaught Exception: ' . $exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' . $exception->getLine() . "\n";
    error_log($logMessage, 3, LOG_PATH . '/error.log');
    
    handleError(500, 'Uncaught Exception', $exception->getMessage());
}

// Set the custom exception handler
set_exception_handler("exceptionHandler");