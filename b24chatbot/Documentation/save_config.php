<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $botData = [
        'CODE' => $_POST['CODE'],
        'TYPE' => $_POST['TYPE'],
        'EVENT_HANDLER' => $_POST['EVENT_HANDLER'],
        'OPENLINE' => $_POST['OPENLINE'],
        'CLIENT_ID' => isset($_POST['CLIENT_ID']) ? $_POST['CLIENT_ID'] : '',
        'PROPERTIES' => [
            'NAME' => $_POST['PROPERTIES']['NAME'],
            'COLOR' => $_POST['PROPERTIES']['COLOR'],
            'EMAIL' => $_POST['PROPERTIES']['EMAIL'],
            'WORK_POSITION' => $_POST['PROPERTIES']['WORK_POSITION'],
            'PERSONAL_PHOTO' => ''
        ]
    ];

    // Handle file upload for PERSONAL_PHOTO
    if (isset($_FILES['PROPERTIES']['tmp_name']['PERSONAL_PHOTO']) && $_FILES['PROPERTIES']['tmp_name']['PERSONAL_PHOTO'] !== '') {
        $fileData = file_get_contents($_FILES['PROPERTIES']['tmp_name']['PERSONAL_PHOTO']);
        $botData['PROPERTIES']['PERSONAL_PHOTO'] = base64_encode($fileData);
    }

    // Load existing configuration
    $configFile = 'configuration.php';
    if (file_exists($configFile)) {
        $existingConfig = include($configFile);
        if (!is_array($existingConfig)) {
            $existingConfig = [];
        }
    } else {
        $existingConfig = [];
    }

    // Append new bot configuration
    $existingConfig[] = $botData;

    // Save configuration back to the file
    $configContent = "<?php\nreturn " . var_export($existingConfig, true) . ";\n";
    file_put_contents($configFile, $configContent);

    // Redirect back to form or to a success page
    header('Location: config_form.php?status=success');
    exit();
}
?>
