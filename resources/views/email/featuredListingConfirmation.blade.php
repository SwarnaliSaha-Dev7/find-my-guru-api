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
        <div class="content">
            <p>Hello {{ $name }},</p>
            <p> We are excited to inform you that your course has been successfully upgraded to a Featured Listing on FindMyGuru!</p>
            <ul>
                <li><strong>Course Title:</strong> {{ $course_name }} </li>
                {{-- <li><strong>Featured Listing Duration:</strong>  </li> --}}
            </ul>
            <p>Your course will now receive enhanced visibility, making it easier for students to find you. Thank you for choosing to promote your course with us!</p>
            <p>If you have any questions or need assistance, feel free to reach out to our support team.</p>
            <p>Best regards,<br>The FindMyGuru Team</p>
        </div>
        <!-- <div class="footer">
            <p>© 2024 Your Company Name. All rights reserved.</p>
        </div> -->
    </div>
</body>

</html>
