<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Access Denied - SustaIndex</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { 
            background-color: #f4f6f9; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            height: 100vh; 
            margin: 0; 
        }
        .error-card { 
            background: white; 
            border-top: 5px solid #cc0000; 
            border-radius: 12px; 
            box-shadow: 0 15px 35px rgba(0,51,102,0.1); 
            padding: 50px 40px; 
            max-width: 600px; 
            text-align: center; 
        }
        .error-code { 
            font-family: 'Georgia', serif; 
            font-size: 7rem; 
            font-weight: bold; 
            color: #003366; 
            line-height: 1; 
            margin-bottom: 10px; 
            text-shadow: 3px 3px 0px rgba(0,51,102,0.1);
        }
        .academic-btn { 
            background-color: #003366; 
            color: white; 
            border-radius: 5px; 
            padding: 12px 30px; 
            font-weight: bold; 
            text-decoration: none; 
            display: inline-block; 
            margin-top: 30px; 
            transition: 0.3s;
        }
        .academic-btn:hover { 
            background-color: #001a33; 
            color: white; 
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>

    <div class="error-card">
        <i class="bi bi-shield-lock-fill text-danger" style="font-size: 4rem;"></i>
        <div class="error-code">403</div>
        <h3 class="fw-bold mb-3" style="color: #333;">Access Denied</h3>
        
        <div class="alert alert-light border text-muted" style="font-size: 1.05rem; line-height: 1.6;">
            {{ $exception->getMessage() ?: 'Sorry, you do not have permission to access this secure page.' }}
        </div>
        
        <a href="/" class="academic-btn">
            <i class="bi bi-house-door-fill me-2"></i> Return to Homepage
        </a>
    </div>

</body>
</html>