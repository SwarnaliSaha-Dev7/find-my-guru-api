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
            <p>This is a friendly reminder that your subscription to FindMyGuru will expire in 15 days. Here are your subscription details:</p>
            <ul>
                <li><strong>Subscription Plan:</strong> {{ $package_name }} </li>
                <li><strong>Expiration Date:</strong> {{ $end_date }} </li>
            </ul>
            <p>To continue enjoying uninterrupted access to our platform and all the benefits of your subscription, we encourage you to renew your plan.</p>
            <p>If you have any questions or need assistance, please don’t hesitate to reach out.</p>
            <p>Best regards,<br>The <i>FindMyGuru</i> Team</p>
        </div>
        <!-- <div class="footer">
            <p>© 2024 Your Company Name. All rights reserved.</p>
        </div> -->
    </div>
</body>
</html>
