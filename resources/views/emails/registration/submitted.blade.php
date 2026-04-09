@component('mail::message')
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .thank-you {
            animation: fadeIn 1s ease-in-out;
            background-color: #f0f8ff;
            padding: 20px;
            border-radius: 10px;
        }

        h1 {
            color: #2c3e50;
        }

        p {
            color: #34495e;
        }
    </style>

    # 🎉 Thank You, {{ $full_name }}!

    Your registration form has been **successfully submitted**.

    We appreciate you taking the time to complete the form. Our team will review your information shortly.

    If you have any questions, feel free to reply to this email.

    Thanks again!
    — Chochangi Samaj Team
@endcomponent
