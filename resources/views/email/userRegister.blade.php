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
        <!--<div class="header">-->
        <!--    <h2>OTP Verification</h2>-->
        <!--</div>-->
        <div class="content">
            <p>Hello {{ $name }},</p>
            <p> Thank you for joining <i>FindMyGuru!</i> We’re excited to have you on board. Your profile is currently
                under review by our team to ensure it meets our quality standards. This process may take up to 24-48
                hours.</p>
            <p><strong>What’s Next?</strong><br>
                You can start listing your courses while your profile is under review. Once approved, your profile and
                course listings will be visible to students on the platform.</p>
            <p>If you have any questions, feel free to reach out to our support team.</p>
            <p>Best regards,<br>The FindMyGuru Team</p>
        </div>
        <!-- <div class="footer">
            <p>© 2024 Your Company Name. All rights reserved.</p>
        </div> -->
    </div>
</body>

</html>