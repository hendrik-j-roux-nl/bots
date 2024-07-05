<?php
// Include settings.php
require_once(dirname(__FILE__) . '/settings.php');
require_once(BOTS_PATH . '/bot.php');

$code = $_GET['code'] ?? '';
$bots = OLBot::getBotsList();

if (!isset($bots[$code])) {
    header('Location: bots_list.php');
    exit;
}

$bot = $bots[$code];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $properties = [
        'NAME' => $_POST['name'],
        'WORK_POSITION' => $_POST['work_position'],
        'COLOR' => $_POST['color'],
    ];

    $result = OLBot::updateBot($code, $properties);

    if ($result) {
        header('Location: bots_list.php');
        exit;
    } else {
        $error = "Failed to update bot.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Bot</title>
</head>
<body>
    <h1>Edit Bot</h1>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="POST">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($bot['PROPERTIES']['NAME']); ?>" required><br><br>

        <label for="work_position">Work Position:</label>
        <input type="text" id="work_position" name="work_position" value="<?php echo htmlspecialchars($bot['PROPERTIES']['WORK_POSITION']); ?>" required><br><br>

        <label for="color">Color:</label>
        <input type="text" id="color" name="color" value="<?php echo htmlspecialchars($bot['PROPERTIES']['COLOR']); ?>" required><br><br>

        <input type="submit" value="Update Bot">
    </form>
</body>
</html>