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
            <p>A student has generated a lead for a tutor on FindMyGuru. Here are the details of the lead for your records:</p>
            <ul>
                <li><strong>User Name:</strong> {{ $name }} </li>
                <li><strong>Email:</strong> {{ $email }} </li>
                <li><strong>Lead Details:</strong> 
                    <ul>
                        <li><strong>Student Name:</strong> {{ $student_name }} </li>
                        <li><strong>Student Phone:</strong> {{ $student_phone }} </li>
                        <li><strong>Student Email:</strong> {{ $student_email }} </li>
                    </ul>
                </li>
                <li><strong>Lead Generation Date:</strong> {{ $date }} </li>
            </ul>
            <p>This notification is for your awareness and monitoring of lead activity on the platform. Please reach out if you have any questions or need further information.</p>
            <p>Best regards,<br>The <i>FindMyGuru</i> Team</p>
        </div>
        <!-- <div class="footer">
            <p>© 2024 Your Company Name. All rights reserved.</p>
        </div> -->
    </div>
</body>

</html>
