<?php
// Include settings.php
require_once(dirname(__FILE__) . '/settings.php');
require_once(BOTS_PATH . '/bot.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $properties = [
        'CODE' => $_POST['code'],
        'NAME' => $_POST['name'],
        'WORK_POSITION' => $_POST['work_position'],
        'COLOR' => $_POST['color'],
    ];

    $result = OLBot::registerBot($properties);

    if ($result) {
        header('Location: bots_list.php');
        exit;
    } else {
        $error = "Failed to register bot.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register New Bot</title>
</head>
<body>
    <h1>Register New Bot</h1>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="POST">
        <label for="code">Code:</label>
        <input type="text" id="code" name="code" required><br><br>

        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required><br><br>

        <label for="work_position">Work Position:</label>
        <input type="text" id="work_position" name="work_position" required><br><br>

        <label for="color">Color:</label>
        <input type="text" id="color" name="color" required><br><br>

        <input type="submit" value="Register Bot">
    </form>
</body>
</html>