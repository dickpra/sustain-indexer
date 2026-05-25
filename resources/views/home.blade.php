<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SustaIndex - Academic Index</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        /* Base Styling */
        body { background-color: #ffffff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; flex-direction: column; min-height: 100vh; }
        .main-wrapper { flex: 1; } /* Memastikan footer selalu di bawah */
        
        /* Navigasi Atas */
        .nav-top { position: absolute; top: 20px; right: 30px; }
        
        /* Area Pencarian Utama */
        .search-section { padding: 100px 0 40px 0; text-align: center; }
        .brand-logo { font-size: 4.5rem; font-weight: 800; color: #0d6efd; letter-spacing: -1.5px; margin-bottom: 30px; }
        .brand-logo span { color: #333; }
        .search-box { width: 100%; max-width: 650px; margin: 0 auto; position: relative; }
        .search-input { border-radius: 30px; padding: 15px 25px; font-size: 1.1rem; box-shadow: 0 1px 6px rgba(32,33,36,.28); border: 1px solid #dfe1e5; transition: 0.2s; }
        .search-input:hover, .search-input:focus { box-shadow: 0 1px 8px rgba(32,33,36,.4); outline: none; border-color: rgba(223,225,229,0); }
        .btn-action { margin: 15px 8px; padding: 10px 25px; border-radius: 4px; font-weight: 500; }
        
        /* Footer SustaIndex */
        .academic-footer { background-color: #f1f3f5; color: #444; border-top: 1px solid #d5d5d5; padding: 40px 0 20px 0; margin-top: 50px; }
        .academic-footer a { color: #003366; text-decoration: none; font-weight: 500; }
        .academic-footer a:hover { text-decoration: underline; }
        .footer-logo { font-family: 'Georgia', serif; font-size: 1.4rem; font-weight: bold; color: #003366; }

        /* ================= RESPONSIVE (MOBILE FIX) ================= */
        @media (max-width: 768px) {
            .nav-top { position: static; text-align: center; padding-top: 20px; margin-bottom: 10px; }
            .nav-top a { display: inline-block; padding: 8px 20px; background-color: #f8f9fa; border: 1px solid #ddd; border-radius: 20px; }
            .search-section { padding: 20px 0; }
            .brand-logo { font-size: 3rem; margin-bottom: 20px; }
            .search-box { padding: 0 20px; }
            .search-input { font-size: 1rem; padding: 12px 20px; }
            /* Tombol turun ke bawah (stack) di HP */
            .btn-action { display: block; width: calc(100% - 40px); margin: 10px auto; }
            /* Jarak Papan Klasemen di HP */
            .stats-container { margin-top: 30px !important; }
            .stats-col { margin-bottom: 30px; }
            .academic-footer .text-md-end { text-align: left !important; margin-top: 20px; }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<div class="main-wrapper">

    <div class="nav-top dropdown">
        <a href="#" class="dropdown-toggle text-decoration-none d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false" style="padding-bottom: 5px;">
                Submit Document
            </a>
            
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-3" style="border-top: 3px solid #003366 !important; min-width: 200px;">
                <li>
                    <a class="dropdown-item py-2 fw-medium text-dark" href="/submit" style="margin-left: 0; border: none;">
                        <i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>Upload PDF
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item py-2 fw-medium text-dark" href="/submit-xml" style="margin-left: 0; border: none;">
                        <i class="bi bi-filetype-xml text-success me-2"></i>Upload OJS XML
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                        <a class="dropdown-item py-2" href="/submit-beta">
                            <i class="bi bi-robot text-primary me-2"></i>
                            <div>
                                <span class="fw-bold d-block">Upload PDF</span>
                                <small class="text-muted" style="font-size: 11px;">Automatic extraction using Groq AI & Crossref</small>
                        </div>
                    </a>
                </li>
            </ul>
    </div>

    <div class="container search-section">
        <div class="brand-logo">📚SustaIndex<span>Search</span></div>
        
        <div class="search-box">
            <form action="/results" method="GET">
                <input type="text" name="q" class="form-control search-input" placeholder="Search by title, author, abstract, or ID..." autocomplete="off" autofocus required>
                
                <div class="text-center mt-3 d-inline-block position-relative">
                    <button type="submit" class="btn btn-light border btn-action text-dark">Search Database</button>
                    <div class="btn-group">
                        <button type="button" class="btn btn-light border btn-action text-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            Submit to Index
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-3" style="border-top: 3px solid #003366 !important; min-width: 200px;">
                            <li>
                                <a class="dropdown-item py-2 fw-medium text-dark" href="/submit" style="margin-left: 0; border: none;">
                                    <i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>Upload PDF
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item py-2 fw-medium text-dark" href="/submit-xml" style="margin-left: 0; border: none;">
                                    <i class="bi bi-filetype-xml text-success me-2"></i>Upload OJS XML
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item py-2" href="/submit-beta">
                                    <i class="bi bi-robot text-primary me-2"></i>
                                    <div>
                                        <span class="fw-bold d-block">Upload PDF</span>
                                        <small class="text-muted" style="font-size: 11px;">Automatic extraction using Groq AI & Crossref</small>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="mt-4 text-muted small">
            <p>A Peer-Reviewed Academic Indexing System</p>
        </div>
    </div>

    <div class="container stats-container" style="margin-top: 50px; margin-bottom: 50px;">
        <div class="row justify-content-center text-start">
            
            <div class="col-md-5 pe-md-3 stats-col">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-fire text-danger fs-5 me-2"></i>
                    <h5 class="fw-bold text-dark mb-0">Most Popular</h5>
                </div>
                
                <div class="list-group list-group-flush shadow-sm rounded border">
                    @forelse($mostPopular as $doc)
                    <a href="/document/{{ $doc->document_number }}" class="list-group-item list-group-item-action p-3">
                        <h6 class="mb-1 fw-bold text-primary" style="font-size: 0.95rem; line-height: 1.4;">{{ $doc->title }}</h6>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-muted" style="font-size: 0.8rem;">{{ $doc->document_type ?: 'Journal' }}</small>
                            <span class="badge bg-light text-dark border rounded-pill" style="font-size: 0.75rem;">
                                <i class="bi bi-eye me-1"></i>{{ number_format($doc->views) }} Views
                            </span>
                        </div>
                    </a>
                    @empty
                    <div class="list-group-item p-4 text-center text-muted small">No popular documents found yet.</div>
                    @endforelse
                </div>
            </div>

            <div class="col-md-5 ps-md-3 stats-col">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-trophy-fill text-warning fs-5 me-2"></i>
                    <h5 class="fw-bold text-dark mb-0">Most Cited</h5>
                </div>
                
                <div class="list-group list-group-flush shadow-sm rounded border">
                    @forelse($mostCited as $doc)
                    <a href="/document/{{ $doc->document_number }}" class="list-group-item list-group-item-action p-3">
                        <h6 class="mb-1 fw-bold text-primary" style="font-size: 0.95rem; line-height: 1.4;">{{ $doc->title }}</h6>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-muted" style="font-size: 0.8rem;">{{ $doc->document_type ?: 'Journal' }}</small>
                            <span class="badge bg-primary rounded-pill" style="font-size: 0.75rem;">
                                <i class="bi bi-chat-quote-fill me-1"></i>{{ number_format($doc->citation_count) }} Citations
                            </span>
                        </div>
                    </a>
                    @empty
                    <div class="list-group-item p-4 text-center text-muted small">No most cited documents found yet.</div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

</div> <footer class="academic-footer">
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
                <div class="mt-3 dropdown d-inline-block">
                    <button class="btn btn-sm btn-outline-secondary rounded-0 fw-bold dropdown-toggle" type="button" id="footerSubmitDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        Index Your Work
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-3" aria-labelledby="footerSubmitDropdown" style="border-top: 3px solid #003366 !important; min-width: 200px;">
                        <li>
                            <a class="dropdown-item py-2 fw-medium text-dark" href="/submit" style="margin-left: 0; border: none;">
                                <i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>Upload PDF
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item py-2 fw-medium text-dark" href="/submit-xml" style="margin-left: 0; border: none;">
                                <i class="bi bi-filetype-xml text-success me-2"></i>Upload OJS XML
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item py-2" href="/submit-beta">
                                <i class="bi bi-robot text-primary me-2"></i>
                                <div>
                                    <span class="fw-bold d-block">Upload PDF</span>
                                    <small class="text-muted" style="font-size: 11px;">Automatic extraction using Groq AI & Crossref</small>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4 pt-3 border-top small text-muted">
            &copy; {{ date('Y') }} SustaIndex System. All rights reserved.
        </div>
    </div>
</footer>

</body>
</html>