<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Official Registration Approval</title>
    <style>
        @keyframes fadeInUp {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }

            100% {
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
            animation: fadeInUp 1s ease-in-out;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .letter-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .letter-header h2 {
            margin: 0;
            font-size: 24px;
            color: #007BFF;
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

        .signature {
            margin-top: 50px;
            font-size: 16px;
        }

        .signature p {
            margin: 5px 0;
        }

        .footer {
            margin-top: 40px;
            font-size: 14px;
            color: #999;
            text-align: center;
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
                {{-- {{ $email }} --}}
            </p>

            <p class="subject-line">Subject: Membership Registration Approval</p>

            <p>Dear {{ $fullName }},</p>

            <p>We are pleased to inform you that your membership registration with <strong>Chochangi Samaj</strong> has
                been officially approved.</p>

            <p>Your member number is: <strong>{{ $memberNumber }}</strong>. Please retain this number for your records
                and for all future correspondence with the organization.</p>

            <p>We warmly welcome you as a valued member of our community. Your dedication and interest in contributing
                to our shared heritage and goals are deeply appreciated.</p>

            <p>Should you have any questions or require further assistance, please do not hesitate to contact our
                office.</p>

            <p>We look forward to your active participation.</p>

            <div class="signature">
                <p>Sincerely,</p>
                <p><strong>Executive Board</strong><br>
                    Chochangi Samaj</p>
            </div>
        </div>

        {{-- <div class="footer">
            This is a system-generated letter. Please do not reply directly to this email.
        </div> --}}
    </div>
</body>

</html>
