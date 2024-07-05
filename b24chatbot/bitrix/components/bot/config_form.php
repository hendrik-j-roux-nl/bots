<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bot Configuration Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            width: 60%;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        .group {
            margin-bottom: 15px;
        }
        .group label {
            display: block;
            margin-bottom: 5px;
        }
        .group input[type="text"],
        .group input[type="url"],
        .group input[type="email"],
        .group select,
        .group input[type="file"] {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .group input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .group input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Bot Configuration Form</h2>
    <form action="save_config.php" method="post" enctype="multipart/form-data">
        <div class="group">
            <label for="code">Bot Code <span title="Mandatory field. Max length 150 characters.">[?]</span></label>
            <input type="text" id="code" name="CODE" maxlength="150" required>
        </div>
        <div class="group">
            <label for="type">Bot Type <span title="Select the bot type: H for standard, O for Open Channels, S for Supervisor.">[?]</span></label>
            <select id="type" name="TYPE" required>
                <option value="H">Standard Chatbot</option>
                <option value="O">Open Channels Chatbot</option>
                <option value="S">Supervisor Chatbot</option>
            </select>
        </div>
        <div class="group">
            <label for="event_handler">Event Handler URL <span title="Mandatory field. Max length 256 characters. Only URLs allowed.">[?]</span></label>
            <input type="url" id="event_handler" name="EVENT_HANDLER" maxlength="256" pattern="https?://.+" required>
        </div>
        <div class="group">
            <label for="openline">Openline <span title="Mandatory field. Max length 5 characters. Values: Y or N. Default is Y. Optional if TYPE=O.">[?]</span></label>
            <select id="openline" name="OPENLINE">
                <option value="Y">Y</option>
                <option value="N">N</option>
            </select>
        </div>
        <div class="group">
            <label for="client_id">Client ID <span title="Optional field. Max length 128 characters. Mandatory if CLIENT_ID is specified.">[?]</span></label>
            <input type="text" id="client_id" name="CLIENT_ID" maxlength="128">
        </div>
        <h3>Bot Properties</h3>
        <div class="group">
            <label for="name">Name <span title="Mandatory field. Max length 50 characters.">[?]</span></label>
            <input type="text" id="name" name="PROPERTIES[NAME]" maxlength="50" required>
        </div>
        <div class="group">
            <label for="color">Color <span title="Mandatory field. Max length 10 characters. Select from predefined colors.">[?]</span></label>
            <select id="color" name="PROPERTIES[COLOR]" required>
                <option value="GREEN">Green</option>
                <option value="RED">Red</option>
                <option value="BLUE">Blue</option>
                <option value="YELLOW">Yellow</option>
                <option value="PURPLE">Purple</option>
                <option value="AQUA">Aqua</option>
                <option value="PINK">Pink</option>
                <option value="LIME">Lime</option>
                <option value="BROWN">Brown</option>
                <option value="ORANGE">Orange</option>
            </select>
        </div>
        <div class="group">
            <label for="email">Email <span title="Mandatory field. Max length 50 characters. Only valid email addresses allowed.">[?]</span></label>
            <input type="email" id="email" name="PROPERTIES[EMAIL]" maxlength="50" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" required>
        </div>
        <div class="group">
            <label for="work_position">Work Position <span title="Mandatory field. Max length 50 characters. Select from predefined positions.">[?]</span></label>
            <select id="work_position" name="PROPERTIES[WORK_POSITION]" required>
                <option value="Sales Advisor">Sales Advisor</option>
                <option value="Student Success Coordinator">Student Success Coordinator</option>
                <option value="Academic Advisor">Academic Advisor</option>
                <option value="Technical Advisor">Technical Advisor</option>
                <option value="Supervisor">Supervisor</option>
            </select>
        </div>
        <div class="group">
            <label for="personal_photo">Personal Photo <span title="Mandatory field. Upload a Base64 encoded file.">[?]</span></label>
            <input type="file" id="personal_photo" name="PROPERTIES[PERSONAL_PHOTO]" accept="image/*" required>
        </div>
        <div class="group">
            <input type="submit" value="Submit Configuration">
        </div>
    </form>
</div>

</body>
</html>