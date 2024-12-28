<!DOCTYPE html>
<html>
<head>
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
        .content {
            font-size: 16px;
            line-height: 1.5;
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
            <p>This is your final reminder that your subscription to FindMyGuru will expire tomorrow.</p>
            <ul>
                <li><strong>Subscription Plan:</strong> {{ $package_name }} </li>
                <li><strong>Expiration Date:</strong> {{ $end_date }} </li>
            </ul>
            <p>To avoid any interruption in your services, please renew your subscription as soon as possible.</p>
            <p>Thank you for being a valued member of the FindMyGuru community!</p>
            <p>Best regards,<br>The <i>FindMyGuru</i> Team</p>
        </div>
        <!-- <div class="footer">
            <p>© 2024 Your Company Name. All rights reserved.</p>
        </div> -->
    </div>
</body>
</html>
