<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Application Status Update</title>
    <style>
        body {
            font-family: Verdana;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
        }

        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        h3 {
            color: #2a3f54;
            margin-bottom: 10px;
        }

        p {
            font-size: 15px;
            color: #333;
            line-height: 1.6;
            margin-bottom: 12px;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            color: white;
            border-radius: 4px;
            font-weight: bold;
        }

        .status-review {
            background-color: #f0ad4e; /* orange for review */
        }

        .status-approved {
            background-color: #28a745; /* green for approved */
        }

        .status-rejected {
            background-color: #dc3545; /* red for rejected */
        }

        .footer {
            margin-top: 25px;
            font-size: 13px;
            color: #777;
            text-align: center;
        }

        @media screen and (max-width: 480px) {
            .email-container { padding: 15px; }
            h3 { font-size: 20px; }
            p { font-size: 14px; }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <h3>Dear {{ $fullname }},</h3>

        <p>Your application for the position <strong>{{ $jobName }}</strong> has been updated.</p>

        <p>
            Current Status:
            <span class="status-badge
                @if(strtolower($status) == 'review') status-review
                @elseif(strtolower($status) == 'approved') status-approved
                @elseif(strtolower($status) == 'rejected') status-rejected
                @endif
            ">
                {{ $status }}
            </span>
        </p>

        <p>Thank you for your interest in joining our team. We appreciate your time and effort in the application process.</p>

        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }} – All rights reserved.
        </div>
    </div>
</body>
</html>
