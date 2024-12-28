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
            <p>Hello {{ $student_name }},</p>
            <p>Thank you for using FindMyGuru! Here are the contact details for the tutor/Institute you showed interest in:</p>
            <ul>
                <li><strong>Name:</strong> {{ $name }} </li>
                <li><strong>Phone Number:</strong> {{ $phone }} </li>
                <li><strong>Email:</strong> {{ $email }} </li>
                <li><strong>Course Title:</strong> {{ $course_name }} </li>
            </ul>
            <p>Feel free to reach out to the tutor for more information or to schedule a session.</p>
            <p>If you have any questions or need assistance, don't hesitate to contact our support team.</p>
            <p>Best regards,<br>The <i>FindMyGuru</i> Team</p>
        </div>
        <!-- <div class="footer">
            <p>© 2024 Your Company Name. All rights reserved.</p>
        </div> -->
    </div>
</body>

</html>
