<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $viewData['installSuccess'] ? 'Installation Successful' : 'Installation Failed'; ?></title>
    <link rel="stylesheet" href="styles/normalize.css">
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/error.css">
    <script src="//api.bitrix24.com/api/v1/"></script>
    <script>
        // B24 SDK provides a set of functions to interact with Bitrix24 API.
        // 'B24.init' is a function that initializes the SDK and allows us to use these functions.
        // It takes a callback function as a parameter, which will be called when the SDK is ready to use.
        B24.init(function(){
            // 'B24.installFinish' is a function provided by the SDK that notifies Bitrix24 that the installation is completed.
            // It is necessary to call this function to let Bitrix24 know that the installation was successful.
            B24.installFinish();
        });
    </script>
</head>
<body>
    <?php if ($viewData['installSuccess']): ?>
        <h1>Installation Successful</h1>
        <div class="success-message">
            <p>The application has been installed successfully.</p>
            <p>Bot ID: <?php echo htmlspecialchars($viewData['botId']); ?></p>
            <p>Open Channel joined successfully.</p>
        </div>
    <?php else: ?>
        <h1>Installation Failed</h1>
        <div class="error-message">
            <p>An error occurred during installation:</p>
            <p><?php echo htmlspecialchars($viewData['errorMessage']); ?></p>
        </div>
    <?php endif; ?>
</body>
</html>
