<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>404 Not Found</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(to right, #ece9e6, #ffffff);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
        }

        .container {
            text-align: center;
            animation: fadeIn 1s ease-out forwards;
        }

        h1 {
            font-size: 8rem;
            font-weight: 900;
            color: #ff6b6b;
        }

        h2 {
            font-size: 2rem;
            margin-top: 10px;
            color: #444;
        }

        p {
            margin-top: 15px;
            color: #666;
            font-size: 1rem;
        }

        a {
            display: inline-block;
            margin-top: 25px;
            padding: 12px 24px;
            background-color: #ff6b6b;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        a:hover {
            background-color: #e55050;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 id="error-code">404</h1>
        <h2>Page Not Found</h2>
        <p>Sorry, the page you're looking for doesn't exist.</p>
        <a href="/">Go Back Home</a>
    </div>

    <script>
        const errorCode = document.getElementById("error-code");
        setInterval(() => {
            errorCode.style.transform = "scale(1.1)";
            setTimeout(() => errorCode.style.transform = "scale(1)", 200);
        }, 3000);
    </script>
</body>

</html>
