<!DOCTYPE html>
<html>

<head>
    <title>User Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            background-color: #4CAF50;
            padding: 10px;
            color: white;
            border-radius: 10px 10px 0 0;
        }

        .content {
            font-size: 16px;
            line-height: 1.5;
        }

        .otp {
            font-size: 24px;
            color: #333333;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            color: #777777;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="content">
            <p>Hello {{ $name }},</p>
            <p> Congratulations! Your profile has been successfully approved and is now live on FindMyGuru. Students can now view your profile and course listings, bringing you closer to connecting with learners seeking your expertise.</p>
            <p><strong>What’s Next?</strong><br>
                Make sure your course listings are up-to-date, and consider adding any additional information that will help students understand your offerings better.</p>
            <p>Thank you for choosing FindMyGuru as your platform to reach and teach new students!</p>
            <p>Best regards,<br>The FindMyGuru Team</p>
        </div>
        <!-- <div class="footer">
            <p>© 2024 Your Company Name. All rights reserved.</p>
        </div> -->
    </div>
</body>

</html>
