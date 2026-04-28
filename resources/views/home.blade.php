<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SustaIndex - Academic Index</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* ================= RESPONSIVE KHUSUS HOME (MOBILE) ================= */
        @media (max-width: 768px) {
            /* Mengecilkan Logo Raksasa */
            .brand-logo { font-size: 2.8rem; margin-bottom: 20px; text-align: center; }
            
            /* Mengatur menu pojok kanan atas agar ketengah dan tidak melayang berantakan */
            .nav-top { position: static; margin-top: 20px; margin-bottom: 30px; text-align: center; width: 100%; }
            .nav-top a { display: inline-block; padding: 8px 20px; background-color: #f8f9fa; border: 1px solid #ddd; border-radius: 20px; }
            
            /* Memperbaiki Kotak Pencarian */
            .search-box { padding: 0 15px; }
            .search-input { font-size: 1rem; padding: 12px 20px; }
            
            /* Membuat tombol Search & Submit sejajar atas bawah dan full width */
            .btn-action { display: block; width: 100%; margin: 10px 0; }
        }
        /* --- HEADER & FOOTER SustaIndex --- */
        .academic-header { background-color: #003366; color: white; padding: 15px 0; border-bottom: 4px solid #cc0000; }
        .academic-header a { color: white; text-decoration: none; }
        .academic-title { font-family: 'Georgia', serif; font-size: 1.8rem; font-weight: normal; margin: 0; }
        
        /* Menu Navigasi Kanan */
        .academic-nav a { font-size: 0.95rem; font-weight: bold; margin-left: 25px; color: #e0e0e0; padding-bottom: 5px; border-bottom: 2px solid transparent; transition: 0.2s; }
        .academic-nav a:hover, .academic-nav a.active { color: white; border-bottom: 2px solid #cc0000; }

        /* Footer */
        .academic-footer { background-color: #f1f3f5; color: #444; border-top: 1px solid #d5d5d5; padding: 40px 0 20px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin-top: 60px; }
        .academic-footer a { color: #003366; text-decoration: none; font-weight: 500; }
        .academic-footer a:hover { text-decoration: underline; }
        .footer-logo { font-family: 'Georgia', serif; font-size: 1.4rem; font-weight: bold; color: #003366; }
        body { background-color: #ffffff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .center-wrapper { min-height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center; }
        .brand-logo { font-size: 4.5rem; font-weight: 800; color: #0d6efd; letter-spacing: -1.5px; margin-bottom: 30px; }
        .brand-logo span { color: #333; }
        .search-box { width: 100%; max-width: 650px; position: relative; }
        .search-input { border-radius: 30px; padding: 15px 25px; font-size: 1.1rem; box-shadow: 0 1px 6px rgba(32,33,36,.28); border: 1px solid #dfe1e5; transition: 0.2s; }
        .search-input:hover, .search-input:focus { box-shadow: 0 1px 8px rgba(32,33,36,.4); outline: none; border-color: rgba(223,225,229,0); }
        .btn-action { margin: 25px 10px; padding: 10px 25px; border-radius: 4px; font-weight: 500; }
        .nav-top { position: absolute; top: 20px; right: 30px; }
    </style>
</head>
<body>

<div class="nav-top">
    <a href="/submit" class="text-decoration-none text-dark fw-bold">Submit Document</a>
</div>

<div class="container center-wrapper">
    <div class="brand-logo">📚SustaIndex<span>Search</span></div>
    
    <div class="search-box">
        <form action="/results" method="GET">
            <input type="text" name="q" class="form-control search-input" placeholder="Search by title, author, abstract, or ID..." autocomplete="off" autofocus required>
            
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-light border btn-action">Search Database</button>
                <a href="/submit" class="btn btn-light border btn-action">Submit to Index</a>
            </div>
        </form>
    </div>
    
    <div class="mt-5 text-muted small">
        <p>A Peer-Reviewed Academic Indexing System</p>
    </div>
</div>

</body>
<footer class="academic-footer">
    <div class="container">
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="footer-logo mb-2">📚 SustaIndex</div>
                <p class="small text-muted pe-md-5">A Peer-Reviewed Sustainable Academic Indexing System. Dedicated to organizing, preserving, and providing access to quality global research materials.</p>
            </div>
            
            <div class="col-md-6 text-md-end">
                <div class="mb-2">
                    <a href="#" class="small me-3">Selection Policy</a>
                    <a href="#" class="small me-3">Privacy Policy</a>
                    <a href="#" class="small">Contact Us</a>
                </div>
                <div class="mt-3">
                    <a href="/submit" class="btn btn-sm btn-outline-secondary rounded-0 fw-bold">Index Your Work</a>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4 pt-3 border-top small text-muted">
            &copy; {{ date('Y') }} SustaIndex System. All rights reserved.
        </div>
    </div>
</footer>
</html>