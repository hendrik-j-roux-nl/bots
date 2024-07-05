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
</head>
<body>
    <?php if ($viewData['installSuccess']): ?>
        <h1>Installation Successful</h1>
        <div class="success-message">
            <p>The application has been installed successfully.</p>
            <p>Bot ID: <?php echo htmlspecialchars($viewData['botId']); ?></p>
            <p>Open Channel joined successfully.</p>
        </div>
        <script>
            BX24.init(function(){
                BX24.installFinish();
            });
        </script>
    <?php else: ?>
        <h1>Installation Failed</h1>
        <div class="error-message">
            <p>An error occurred during installation:</p>
            <p><?php echo htmlspecialchars($viewData['errorMessage']); ?></p>
        </div>
    <?php endif; ?>
</body>
</html>