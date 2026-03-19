<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= BASE_URL ?>">
    <link href="https://fonts.cdnfonts.com/css/Krungthep, sans-serif" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/colors.css">
    <title>Your Surf Panel</title>
    <style>
        body {
            font-size: 2em;
            background: var(--bg);
            color: var(--text);
            text-align: center;
            font-family: "Krungthep, sans-serif", sans-serif;
        }

        h1 {
            margin-top: 2em;
        }

        h1, h2 {
            text-transform: uppercase;
        }

        a {
            color: var(--text-dark);
        }
    </style>
</head>
    <body>
        <div>
            <h1>404</h1>
            <h2>Oops, wiped out!</h2>
            <p>Looks like this wave doesn’t exist.</p>
            <p>Paddle back to the homepage before the next set rolls in.</p>
            <p>Take me home <a href="<?= BASE_URL ?>">https://www.surfpan.com</a></p>
        </div>
    </body>
</html>