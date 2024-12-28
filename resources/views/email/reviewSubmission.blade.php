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
            <p>Congratulations! A new review/rating has been submitted for your profile on FindMyGuru. Here are the details:</p>
            <ul>
                <li><strong>Rating Given:</strong> {{ $rating }} </li>
                <li><strong>Reviewer Name:</strong> {{ $student_name }} </li>
                <li><strong>Review Comments:</strong> {{ $review }} </li>
                <li><strong>Submission Date:</strong> {{ $date }} </li>
            </ul>
            <p>Your commitment to teaching is being recognized, and we appreciate the positive impact you have on your students.</p>
            <p>If you would like to respond to the review or have any questions, please feel free to reach out.</p>
            <p>Best regards,<br>The <i>FindMyGuru</i> Team</p>
        </div>
        <!-- <div class="footer">
            <p>© 2024 Your Company Name. All rights reserved.</p>
        </div> -->
    </div>
</body>

</html>
