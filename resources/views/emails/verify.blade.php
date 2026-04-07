<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;}
        .header { background: #0d6efd; color: white; padding: 15px; text-align: center; border-radius: 8px 8px 0 0;}
        .content { padding: 20px; }
        .btn { display: inline-block; background: #198754; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 15px; }
        .footer { margin-top: 30px; font-size: 0.85em; color: #777; border-top: 1px solid #eee; padding-top: 15px;}
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin:0;">Verify Your Submission</h2>
        </div>
        <div class="content">
            <p>Dear <strong>{{ $document->submitter_first_name }} {{ $document->submitter_last_name }}</strong>,</p>
            <p>Thank you for submitting your document to SustainDex. To complete the indexing process and publish your document, please verify your submission by clicking the button below:</p>
            
            <div style="background: #f9f9f9; padding: 15px; border-left: 4px solid #0d6efd; margin: 15px 0;">
                <strong>Title:</strong> {{ $document->title }}<br>
                <strong>Tracking ID:</strong> {{ $document->document_number }}
            </div>

            <div style="text-align: center;">
                <a href="{{ url('/verify/' . $document->verification_token) }}" class="btn">Verify & Publish Document</a>
            </div>

            <p style="margin-top: 25px;">If the button doesn't work, copy and paste this link into your browser:<br>
            <a href="{{ url('/verify/' . $document->verification_token) }}">{{ url('/verify/' . $document->verification_token) }}</a></p>
        </div>
        <div class="footer">
            <p>If you did not submit this document, please ignore this email.</p>
            <p>&copy; 2026 SustainDex Indexer.</p>
        </div>
    </div>
</body>
</html>