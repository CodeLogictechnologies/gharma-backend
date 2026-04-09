<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Registration Status</title>
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        body {
            font-family: 'Georgia', serif;
            background-color: #f4f4f4;
            padding: 40px 20px;
            color: #333;
        }

        .letter {
            max-width: 700px;
            margin: auto;
            background: #fff;
            padding: 40px;
            border: 1px solid #ccc;
            border-radius: 8px;
            animation: fadeIn 1s ease-in-out;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .letter-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .letter-header h2 {
            margin: 0;
            font-size: 24px;
            color: #C0392B;
        }

        .letter-header p {
            font-size: 14px;
            color: #777;
        }

        .letter-body {
            font-size: 16px;
            line-height: 1.6;
        }

        .letter-body p {
            margin: 15px 0;
        }

        .subject-line {
            font-weight: bold;
            margin: 25px 0 10px;
            font-size: 17px;
            text-decoration: underline;
        }

        .footer {
            margin-top: 50px;
            font-size: 14px;
            color: #999;
            text-align: center;
        }

        .signature {
            margin-top: 40px;
        }
    </style>
</head>

<body>
    <div class="letter">
        <div class="letter-header">
            <h2>Chochangi Samaj</h2>
            <p>Kathmandu, Nepal | contact@chochangisamaj.org</p>
        </div>

        <div class="letter-body">
            <p>Date: {{ \Carbon\Carbon::now()->format('F d, Y') }}</p>

            <p>To,<br>
                <strong>{{ $fullName }}</strong><br>
            </p>

            <p class="subject-line">Subject: Registration Application Status</p>

            <p>Dear {{ $fullName }},</p>

            <p>We regret to inform you that after careful review, your registration application with <strong>Chochangi
                    Samaj</strong> has not been approved at this time.</p>

            <p>We understand that this news may be disappointing. Please be assured that all applications are reviewed
                with the utmost fairness and consideration. The decision was based on criteria outlined in our
                membership policy.</p>

            <p>If you believe there has been a misunderstanding or if you would like to discuss this matter further,
                please feel free to reach out to our office via email or phone.</p>

            <p>We thank you for your interest in joining our community and wish you all the best in your future
                endeavors.</p>

            <div class="signature">
                <p>Sincerely,</p>
                <p><strong>Membership Committee</strong><br>
                    Chochangi Samaj</p>
            </div>
        </div>
    </div>
</body>

</html>
