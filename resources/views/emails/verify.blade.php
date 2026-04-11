<!DOCTYPE html>
<html>
<head>
    <style>
        /* Desain Akademik Khas SustainDex */
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #ccc; }
        .header { background: #003366; color: white; padding: 25px 20px; text-align: center; border-top: 5px solid #cc0000; }
        .header h2 { margin: 0; font-family: 'Georgia', serif; font-weight: normal; font-size: 26px; letter-spacing: 0.5px; }
        .content { padding: 35px 30px; }
        .btn { display: inline-block; background: #003366; color: #ffffff; padding: 12px 25px; text-decoration: none; font-weight: bold; margin-top: 25px; border: 1px solid #002244; }
        .info-box { background: #f9f9f9; padding: 15px 20px; border-left: 4px solid #003366; margin: 25px 0; font-size: 0.95em; border-top: 1px solid #eee; border-right: 1px solid #eee; border-bottom: 1px solid #eee; }
        .footer { background: #f1f3f5; padding: 20px; text-align: center; font-size: 0.85em; color: #666; border-top: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>📚 SustainDex</h2>
        </div>
        
        <div class="content">
            <p>Dear <strong>{{ $document->submitter_first_name }} {{ $document->submitter_last_name }}</strong>,</p>
            
            <p>Thank you for submitting your research material to the SustainDex Academic Indexing System. To complete the indexing process and securely publish your document, please verify your submission.</p>
            
            <div class="info-box">
                <strong>Title:</strong> {{ $document->title }}<br><br>
                <strong>Tracking ID:</strong> <span style="color: #003366; font-weight: bold;">{{ $document->document_number }}</span><br>
                <strong>Document Type:</strong> {{ $document->document_type ?: 'Unspecified' }}
            </div>

            <div style="text-align: center;">
                <a href="{{ url('/verify/' . $document->verification_token) }}" class="btn">Verify & Publish Document</a>
            </div>

            <p style="margin-top: 35px; font-size: 0.9em; color: #555; border-top: 1px dashed #ccc; padding-top: 15px;">
                If the button above does not work, please copy and paste the following URL into your web browser:<br>
                <a href="{{ url('/verify/' . $document->verification_token) }}" style="color: #003366; word-break: break-all; margin-top: 5px; display: inline-block;">{{ url('/verify/' . $document->verification_token) }}</a>
            </p>
        </div>
        
        <div class="footer">
            <p style="margin: 0;">This is an automated message from the indexing system. If you did not submit this document, please disregard this email.</p>
            <p style="margin: 10px 0 0 0; font-weight: bold;">&copy; {{ date('Y') }} SustainDex System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>