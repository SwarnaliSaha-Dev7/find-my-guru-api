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
            <p>A tutor has made modifications to an existing course on FindMyGuru. Below are the updated details for your review:</p>
            <ul>
                <li><strong>User Name:</strong> {{ $user_name }} </li>
                <li><strong>Course Title:</strong> {{ $course_name }} </li>
                <li><strong>Previous Details:</strong> 
                    <ul>
                        <li><strong>Course Description:</strong> {{ $p_course_content }} </li>
                        <li><strong>Course Fee:</strong> {{ $p_fee }} </li>
                        <li><strong>Duration:</strong> {{ $p_duration_value }} ({{ $p_duration_unit }}) </li>
                        <li><strong>Teaching Mode:</strong> {{ $p_teaching_mode }} </li>
                    </ul>
                </li>
                <li><strong>Updated Details:</strong> 
                    <ul>
                        <li><strong>Course Description:</strong> {{ $course_content }} </li>
                        <li><strong>Course Fee:</strong> {{ $fee }} </li>
                        <li><strong>Duration:</strong> {{ $duration_value }} ({{ $duration_unit }}) </li>
                        <li><strong>Teaching Mode:</strong> {{ $teaching_mode }} </li>
                    </ul>
                </li>
                <li><strong>Date Modified:</strong> {{ $date }} </li>
            </ul>
            <p>Please log in to the admin portal to review and approve the course modifications.</p>
            <p>If you have any questions or need further assistance, feel free to reach out to our support team.</p>
            <p>Best regards,<br>The <i>FindMyGuru</i> Team</p>
        </div>
        <!-- <div class="footer">
            <p>© 2024 Your Company Name. All rights reserved.</p>
        </div> -->
    </div>
</body>

</html>
