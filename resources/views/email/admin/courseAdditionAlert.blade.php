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
            <p>A new course has been added by a tutor on FindMyGuru. Please find the details below for your review:</p>
            <ul>
                <li><strong>Tutor Name:</strong> {{ $name }} </li>
                <li><strong>Course Title:</strong> {{ $course_title }} </li>
                <li><strong>Course Description:</strong> {{ $course_description }} </li>
                <li><strong>Course Fee:</strong> {{ $course_fee }} </li>
                <li><strong>Duration:</strong> {{ $duration }} ({{ $duration_unit }}) </li>
                <li><strong>Teaching Mode:</strong> {{ $teaching_mode }} </li>
                <li><strong>Date Added:</strong> {{ $date_added }} </li>
            </ul>
            <p>Please log in to the admin portal to review and approve the course listing.</p>
            <p>If you have any questions or need further assistance, feel free to reach out to our support team.</p>
            <p>Best regards,<br>The <i>FindMyGuru</i> Team</p>
        </div>
        <!-- <div class="footer">
            <p>© 2024 Your Company Name. All rights reserved.</p>
        </div> -->
    </div>
</body>

</html>
