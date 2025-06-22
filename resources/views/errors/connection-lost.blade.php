<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connection Lost</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #ff6b6b;
            color: white;
            text-align: center;
            padding: 50px;
            margin: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
        }
        h1 {
            font-size: 3em;
            margin-bottom: 20px;
        }
        .message {
            font-size: 1.2em;
            margin-bottom: 30px;
        }
        .retry-btn {
            background: #ffd700;
            color: #333;
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            font-size: 1.1em;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 10px;
        }
        .retry-btn:hover {
            background: #ffed4e;
        }
        .countdown {
            margin-top: 20px;
            font-size: 1.1em;
            color: #ffd700;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚠️ Connection Lost</h1>
        <div class="message">
            <p><strong>Unable to connect to server</strong></p>
            <p>The database server is currently unavailable. This could be due to maintenance or connectivity issues.</p>
        </div>

        <button onclick="location.reload()" class="retry-btn">🔄 Try Again</button>
        <a href="/" class="retry-btn">🏠 Go Home</a>

        <div class="countdown">
            Auto-retry in: <span id="timer">30</span> seconds
        </div>
    </div>

    <script>
        let timeLeft = 30;
        const timer = document.getElementById('timer');

        const countdown = setInterval(() => {
            timeLeft--;
            timer.textContent = timeLeft;

            if (timeLeft <= 0) {
                clearInterval(countdown);
                location.reload();
            }
        }, 1000);
    </script>
</body>
</html>
