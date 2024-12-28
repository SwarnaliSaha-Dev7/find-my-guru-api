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
            <p>Thank you for your purchase! We're pleased to inform you that your coin purchase on FindMyGuru has been successfully completed.</p>
            <ul>
                <li><strong>Purchase Date:</strong> {{ $purchase_date }} </li>
                <li><strong>Coins Purchased:</strong> {{ $coins_received }} </li>
                <li><strong>Total Amount:</strong> {{ $amount_paid }} </li>
            </ul>
            <p>Your current coin balance is now {{ $remainingCoins }} coins. You can use these coins to unlock leads and enhance your visibility on our platform.</p>
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
