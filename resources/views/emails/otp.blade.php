<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Verification Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px solid #f4f4f4;
        }
        .content {
            padding: 20px 0;
        }
        .otp-code {
            text-align: center;
            font-size: 32px;
            letter-spacing: 4px;
            color: #2563eb;
            background: #f0f7ff;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
            font-family: monospace;
        }
        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 2px solid #f4f4f4;
            color: #666;
            font-size: 12px;
        }
        .logo-text {
            font-family: 'Courier New', monospace;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 4px;
            color: #1f2937;
            margin-bottom: 20px;
        }
        .puzzle-grid {
            display: inline-flex;
            flex-wrap: nowrap;
            gap: 2px;
            margin: 20px auto;
            max-width: 100%;
            justify-content: center;
        }
        .puzzle-letter {
            background: #fff;
            border: 2px solid #1f2937;
            padding: 8px;
            text-align: center;
            font-weight: bold;
            min-width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
        .puzzle-letter-dark {
            background: #1f2937;
            color: white;
        }
        @media screen and (max-width: 480px) {
            .puzzle-grid {
                gap: 1px;
            }
            .puzzle-letter {
                padding: 4px;
                min-width: 16px;
                height: 16px;
                font-size: 12px;
            }
            .logo-text {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo-text">C R O S S W O R D</div>
            <div class="puzzle-grid">
                @foreach(str_split('PUZZLEMANIA') as $index => $letter)
                    <div class="puzzle-letter {{ $index % 2 == 0 ? '' : 'puzzle-letter-dark' }}">
                        {{ $letter }}
                    </div>
                @endforeach
            </div>
        </div>
        
        <div class="content">
            <p>Hello!</p>
            
            <p>Thank you for registering with our Crossword Puzzle platform. To complete your registration, please use the following verification code:</p>
            
            <div class="otp-code">
                {{ $otp }}
            </div>
            
            <p>This code will expire in 10 minutes for security purposes.</p>
            
            <p>If you didn't request this code, please ignore this email.</p>
        </div>
        
        <div class="footer">
            <p>This is an automated message, please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} Crossword Puzzle. All rights reserved.</p>
        </div>
    </div>
</body>
</html> 