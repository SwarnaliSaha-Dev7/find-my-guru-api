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
            <p>A student has submitted a review/rating for a tutor on FindMyGuru that requires your approval. Below are the details:</p>
            <ul>
                <li><strong>Tutor Name:</strong> {{ $name }} </li>
                <li><strong>Tutor Email:</strong> {{ $email }} </li>
                <li><strong>Student Name:</strong> {{ $student_name }} </li>
                <li><strong>Rating Given:</strong> {{ $rating }} </li>
                <li><strong>Review Comments:</strong> {{ $review }}  </li>
                <li><strong>Submission Date:</strong> {{ $date }} </li>
            </ul>
            <p>Please log in to the admin portal to review and approve the submitted rating and review.</p>
            <p>If you have any questions or require further assistance, feel free to reach out.</p>
            <p>Best regards,<br>The <i>FindMyGuru</i> Team</p>
        </div>
        <!-- <div class="footer">
            <p>© 2024 Your Company Name. All rights reserved.</p>
        </div> -->
    </div>
</body>

</html>
