<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Application Status Update</title>
    <style>
        /* General styles */
        body {
            margin: 0;
            padding: 0;
            font-family: Verdana, sans-serif;
            background-color: #f5f7fa;
        }

        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
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
            padding: 8px 15px;
            border-radius: 4px;
            font-weight: bold;
            color: #fff;
            font-size: 14px;
            text-align: center;
        }

        .status-review { background-color: #f0ad4e; }   /* Orange */
        .status-approved { background-color: #28a745; } /* Green */
        .status-rejected { background-color: #dc3545; } /* Red */

        .footer {
            margin-top: 25px;
            font-size: 13px;
            color: #777;
            text-align: center;
        }

        /* Responsive styles */
        @media screen and (max-width: 480px) {
            .email-container {
                padding: 15px;
            }
            h3 {
                font-size: 18px;
            }
            p {
                font-size: 14px;
            }
            .status-badge {
                padding: 6px 12px;
                font-size: 13px;
            }
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
                @elseif(strtolower($status) == 'reject') status-rejected
                @endif
            ">
                {{ ucfirst($status) }}
            </span>
        </p>

        <p>
            @if(strtolower($status) == 'review')
                Your application is currently under review. We will notify you once the evaluation is complete.
            @elseif(strtolower($status) == 'approved')
                Congratulations! Your application has been approved. Our team will contact you with the next steps.
            @elseif(strtolower($status) == 'reject')
                We regret to inform you that your application has not been successful. We encourage you to apply for future opportunities.
            @else
                Your application status has been updated to <strong>{{ $status }}</strong>.
            @endif
        </p>

        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }} – All rights reserved.
        </div>
    </div>
</body>
</html>
