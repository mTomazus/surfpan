<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            border-radius: 5px 5px 0 0;
        }
        .content {
            padding: 20px;
            background-color: #f9f9f9;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #666;
        }
        .field {
            margin-bottom: 15px;
        }
        .label {
            font-weight: bold;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>New Contact Form Submission</h2>
        </div>
        <div class="content">
            <div class="field">
                <span class="label">Name:</span> <?= out($name) ?>
            </div>
            <div class="field">
                <span class="label">Organization:</span> <?= out($organization) ?>
            </div>
            <div class="field">
                <span class="label">Email:</span> <?= out($email) ?>
            </div>
            <div class="field">
                <span class="label">Phone:</span> <?= out($phone) ?>
            </div>
            <div class="field">
                <span class="label">Message:</span>
                <p><?= out($message) ?></p>
            </div>
            <div class="field">
                <span class="label">Submitted: </span> <?= date('Y-m-d H:i:s') ?>
            </div>
        </div>
        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>