<?php
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