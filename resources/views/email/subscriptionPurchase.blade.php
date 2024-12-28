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
            <p>Thank you for choosing FindMyGuru! Your subscription has been successfully processed. Here are the details of your plan:</p>
            <ul>
                <li><strong>Subscription Plan:</strong> {{ $package_name }} </li>
                <li><strong>Payment Amount:</strong> {{ $amount_paid }} </li>
                <li><strong>Start Date:</strong> {{ $start_date }} </li>
                <li><strong>End Date:</strong> {{ $end_date }} </li>
            </ul>
            <p>If you have any questions or need assistance, feel free to reach out to our support team.</p>
            <p>Thank you for being a part of the FindMyGuru community!</p>
            <p>Best regards,<br>The <i>FindMyGuru</i> Team</p>
        </div>
        <!-- <div class="footer">
            <p>© 2024 Your Company Name. All rights reserved.</p>
        </div> -->
    </div>
</body>

</html>
