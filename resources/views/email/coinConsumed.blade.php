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
            <p>We wanted to inform you that your coins have been deducted for viewing a lead on FindMyGuru. Here are the details:</p>
            <ul>
                <li><strong>Lead Name:</strong> {{ $student_name }} </li>
                <li><strong>Lead Phone:</strong> {{ $student_phone }} </li>
                <li><strong>Lead Email:</strong> {{ $student_email }} </li>
                <li><strong>Coins Spent:</strong> {{ $used_coins }} </li>
                <li><strong>Remaining Coin Balance:</strong> {{ $remaining_coin_balance }} </li>
            </ul>
            <p>Thank you for using FindMyGuru! If you have any questions or need assistance, feel free to contact our support team.</p>
            <p>Best regards,<br>The <i>FindMyGuru</i> Team</p>
        </div>
        <!-- <div class="footer">
            <p>© 2024 Your Company Name. All rights reserved.</p>
        </div> -->
    </div>
</body>

</html>
