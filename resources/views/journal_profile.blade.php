<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $journalName }} - SustaIndex</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        /* ========================================================= */
        /* 1. CSS GLOBAL SUSTAINDEX (HEADER, FOOTER, RESPONSIVE)     */
        /* ========================================================= */
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; }
        .navbar-brand { font-size: 1.5rem; letter-spacing: 0.5px; }

        @media (max-width: 768px) {
            .academic-title { font-size: 1.4rem; }
            .academic-header .container { flex-direction: column; text-align: center; gap: 10px; }
            .academic-nav { display: flex !important; justify-content: center; gap: 15px; width: 100%; margin: 0; padding: 0; }
            .academic-nav a { margin: 0; font-size: 0.9rem; }
            .academic-footer .text-md-end { text-align: left !important; margin-top: 20px; }
            .academic-footer .btn { width: auto; display: inline-block; }
        }

        /* --- HEADER & FOOTER SUSTAINDEX --- */
        .academic-header { background-color: #003366; color: white; padding: 15px 0; border-bottom: 4px solid #cc0000; }
        .academic-header a { color: white; text-decoration: none; }
        .academic-title { font-family: 'Georgia', serif; font-size: 1.8rem; font-weight: normal; margin: 0; }
        .academic-nav a { font-size: 0.95rem; font-weight: bold; margin-left: 25px; color: #e0e0e0; padding-bottom: 5px; border-bottom: 2px solid transparent; transition: 0.2s; }
        .academic-nav a:hover, .academic-nav a.active { color: white; border-bottom: 2px solid #cc0000; }
        
        .academic-footer { background-color: #f1f3f5; color: #444; border-top: 1px solid #d5d5d5; padding: 40px 0 20px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin-top: 60px; }
        .academic-footer a { color: #003366; text-decoration: none; font-weight: 500; }
        .academic-footer a:hover { text-decoration: underline; }
        .footer-logo { font-family: 'Georgia', serif; font-size: 1.4rem; font-weight: bold; color: #003366; }


        /* ========================================================= */
        /* 2. CSS KHUSUS HALAMAN JURNAL (BANNER & FLOATING CARD)     */
        /* ========================================================= */
        
        /* --- HEADER JURNAL --- */
        .journal-header { 
            background: linear-gradient(135deg, #003366 0%, #001a33 100%); 
            color: white; 
            padding: 60px 0 120px 0; /* Padding bawah dilebarkan agar tidak mencekik card */
        }
        .journal-name { 
            font-family: 'Georgia', serif; 
            font-size: 2.5rem; 
            font-weight: normal; 
            text-shadow: 2px 2px 4px rgba(0,0,0,0.4);
        }
        
        /* --- FLOATING STATS CARD --- */
        .stats-card {
            margin-top: -80px; /* Ditarik lebih tinggi membelah header dan body */
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border: 1px solid #e0e0e0;
            border-top: 5px solid #cc0000; /* Garis merah yang elegan di atas card */
            position: relative;
            z-index: 10;
        }

        /* --- CARD DOKUMEN --- */
        .doc-card { background: white; border: 1px solid #e0e0e0; padding: 20px; border-radius: 6px; margin-bottom: 15px; transition: 0.2s; }
        .doc-card:hover { box-shadow: 0 4px 10px rgba(0,0,0,0.05); transform: translateY(-2px); border-color: #c6dafc; }
        .doc-title { font-size: 1.25rem; font-weight: 600; color: #1a0dab; text-decoration: none; }
        .doc-title:hover { text-decoration: underline; }
        .doc-authors a { color: #006621; text-decoration: none; font-size: 0.95em; }
        .doc-authors a:hover { text-decoration: underline; }
        .doc-abstract { color: #4d5156; font-size: 0.95em; line-height: 1.6; }
        .doc-meta { font-size: 0.85em; color: #70757a; }
    </style>
</head>
<body>

<!-- PANGGIL NAVBAR ASLI -->
@include('partials.header')

<!-- ========================================== -->
<!-- 1. HEADER JURNAL -->
<!-- ========================================== -->
<div class="journal-header text-center">
    <div class="container">
        <span class="badge bg-danger mb-3 px-3 py-2" style="font-size: 0.9em; letter-spacing: 1px;">
            <i class="bi bi-journal-text me-1"></i> INDEXED JOURNAL SOURCE
        </span>
        <h1 class="journal-name">
            {{ $journalName }} 
            <br>
            <span style="font-size: 0.5em; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #ffcccc; letter-spacing: 1px; text-transform: uppercase;">
                PUBLISHED BY {{ $publisherName }}
            </span>
        </h1>
    </div>
</div>

<!-- ========================================== -->
<!-- 2. FLOATING STATS DASHBOARD (DENGAN S-FACTOR) -->
<!-- ========================================== -->
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10"> <!-- Diperlebar jadi col-md-10 -->
            <div class="stats-card p-4">
                <div class="row text-center">
                    
                    <!-- S-FACTOR (BINTANG UTAMANYA) -->
                    <div class="col-3 border-end">
                        <div class="text-muted small fw-bold text-uppercase mb-1" style="color: #cc0000 !important;">🔥 S-Factor</div>
                        <div class="fs-2 fw-bold text-danger">{{ $sFactor }}</div>
                        <div class="small text-muted mt-1">Impact Score</div>
                    </div>

                    <div class="col-3 border-end">
                        <div class="text-muted small fw-bold text-uppercase mb-1">Total Indexed</div>
                        <div class="fs-2 fw-bold text-dark">{{ $totalDocs }}</div>
                        <div class="small text-muted mt-1">Articles</div>
                    </div>
                    
                    <div class="col-3 border-end">
                        <div class="text-muted small fw-bold text-uppercase mb-1">Total Citations</div>
                        <div class="fs-2 fw-bold text-primary">{{ number_format($totalCitations) }}</div>
                        <div class="small text-muted mt-1">Across all papers</div>
                    </div>
                    
                    <div class="col-3">
                        <div class="text-muted small fw-bold text-uppercase mb-1">Total Views</div>
                        <div class="fs-2 fw-bold text-success">{{ number_format($totalViews) }}</div>
                        <div class="small text-muted mt-1">SustainDex Engine</div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 3. DAFTAR ARTIKEL JURNAL -->
<!-- ========================================== -->
<div class="container mt-5 mb-5 min-vh-100">
    <div class="row justify-content-center">
        <div class="col-md-9">
            
            <h4 class="mb-4 fw-bold" style="color: #003366; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px;">
                <i class="bi bi-file-earmark-text text-secondary me-2"></i> Publications from this source
            </h4>

            @forelse($documents as $doc)
                <div class="doc-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <a href="/document/{{ $doc->document_number }}" class="doc-title">{{ $doc->title }}</a>
                        
                        <div class="ms-3 flex-shrink-0">
                            <span class="badge rounded-pill bg-white text-primary border border-primary px-2 py-1 shadow-sm" title="Data from Crossref">
                                <i class="bi bi-chat-quote-fill me-1"></i> Cited by {{ $doc->citation_count ?? 0 }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="doc-authors mt-2">
                        @if($doc->authors && $doc->authors->count() > 0)
                            @foreach($doc->authors as $author)
                                <a href="/author/{{ $author->id }}">{{ $author->name }}</a>{{ !$loop->last ? '; ' : '' }}
                            @endforeach
                        @else
                            <span class="text-muted fst-italic">Unknown Author</span>
                        @endif
                    </div>
                    
                    <div class="doc-abstract mt-2">
                        {{ \Illuminate\Support\Str::limit(strip_tags($doc->abstract), 300, '...') }}
                    </div>
                    
                    <div class="doc-meta mt-3">
                        <span class="badge bg-secondary rounded-pill">{{ $doc->document_type ?: 'Journal Article' }}</span>
                        <span class="ms-2">Pub Year: {{ $doc->pub_year ?: 'N/A' }}</span>
                        @if($doc->doi)
                            <span class="ms-2">| <a href="{{ $doc->doi }}" target="_blank" class="text-success text-decoration-none fw-bold">DOI</a></span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="alert bg-white border text-center py-5 shadow-sm rounded-4">
                    <i class="bi bi-journal-x text-muted" style="font-size: 3rem;"></i>
                    <h5 class="text-muted mt-3 mb-0">No indexed documents found for this journal.</h5>
                </div>
            @endforelse

            <!-- Paginasi -->
            <div class="d-flex justify-content-center mt-5">
                {{ $documents->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>
</div>

<!-- PANGGIL FOOTER ASLI -->
@include('partials.footer')
</body>
</html>