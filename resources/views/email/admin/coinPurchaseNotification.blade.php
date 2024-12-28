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
            <p>Dear Admin,</p>
            <p>A tutor has made a purchase of coins on FindMyGuru. Below are the details of the transaction:</p>
            <ul>
                <li><strong>User Name:</strong> {{ $user_name }} </li>
                <li><strong>Email:</strong> {{ $email }} </li>
                <li><strong>Coins Purchased:</strong> {{ $coins_received }} </li>
                <li><strong>Transaction Date:</strong> {{ $purchase_date }} </li>
                <li><strong>Total Amount:</strong> {{ $amount_paid }} </li>
            </ul>
            <p>Please log in to the admin portal for further details and to manage the coin transactions as necessary.</p>
            <p>If you have any questions or need further assistance, feel free to reach out to our support team.</p>
            <p>Best regards,<br>The <i>FindMyGuru</i> Team</p>
        </div>
        <!-- <div class="footer">
            <p>© 2024 Your Company Name. All rights reserved.</p>
        </div> -->
    </div>
</body>

</html>
