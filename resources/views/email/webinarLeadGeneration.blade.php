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
            <p>Great news! A student has shown interest in your webinar, {{ $webinar_title }}. Here are the details of the
                lead:</p>
            <ul>
                <li><strong>Student Name:</strong> {{ $student_name }} </li>
                <li><strong>Phone Number:</strong> {{ $student_phone }} </li>
                <li><strong>Email Address:</strong> {{ $student_email }} </li>
            </ul>
            <p>Please reach out to the student at your earliest convenience to answer any questions and provide
                additional information.</p>
            <p>Thank you for using <i>FindMyGuru</i> to connect with eager learners!</p>
            <p>Best regards,<br>The <i>FindMyGuru</i> Team</p>
        </div>
        <!-- <div class="footer">
            <p>© 2024 Your Company Name. All rights reserved.</p>
        </div> -->
    </div>
</body>

</html>
