<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SustaIndex - Academic Index</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="{{ asset('logo/1_Main_Sustaindex_potrait.png') }}">
    <style>
        /* Base Styling */
        body { background-color: #ffffff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; flex-direction: column; min-height: 100vh; }
        .main-wrapper { flex: 1; } 
        
        /* Navigasi Atas */
        .nav-top { position: absolute; top: 20px; right: 30px; z-index: 10; }
        
        /* Area Pencarian Utama */
        .search-section { padding: 100px 0 40px 0; text-align: center; }
        
        .brand-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin-bottom: 50px; 
        }
        .brand-icon { width: 150px; height: 150px; flex-shrink: 0; }
        .brand-icon img { width: 100%; height: 100%; object-fit: contain; }
        .brand-text { font-size: 60px; font-weight: 600; line-height: 1; color: #0066ff; letter-spacing: -3px; }
        .brand-text span { color: #198754; font-weight: 400; }

        .search-box { width: 100%; max-width: 650px; margin: 0 auto; position: relative; }
        .search-input { border-radius: 30px; padding: 15px 25px; font-size: 1.1rem; box-shadow: 0 1px 6px rgba(32,33,36,.28); border: 1px solid #dfe1e5; transition: 0.2s; }
        .search-input:hover, .search-input:focus { box-shadow: 0 1px 8px rgba(32,33,36,.4); outline: none; border-color: rgba(223,225,229,0); }
        .btn-action { margin: 15px 8px; padding: 10px 25px; border-radius: 4px; font-weight: 500; }
        
        /* Footer SustaIndex */
        .academic-footer { background-color: #f1f3f5; color: #444; border-top: 1px solid #d5d5d5; padding: 40px 0 20px 0; margin-top: 50px; }
        .academic-footer a { color: #003366; text-decoration: none; font-weight: 500; }
        .academic-footer a:hover { text-decoration: underline; }
        .footer-logo { font-family: 'Georgia', serif; font-size: 1.4rem; font-weight: bold; color: #003366; }

        /* 🔥 GAYA KHUSUS UNTUK LEADERBOARD SUSTAINDEX RANKINGS */
        .rank-section { background: #ffffff; border-radius: 12px; border: 1px solid #e0e0e0; box-shadow: 0 4px 15px rgba(0,0,0,0.03); overflow: hidden; height: 100%; display: flex; flex-direction: column;}
        .rank-header { padding: 12px 15px; font-weight: bold; font-size: 0.95rem; color: white; }
        .rank-desc { font-size: 0.75rem; padding: 8px; background: #f8f9fa; color: #6c757d; text-align: center; border-bottom: 1px solid #f1f1f1; }
        
        .rank-list { list-style: none; padding: 0; margin: 0; flex-grow: 1;}
        .rank-list li { padding: 12px 15px; border-bottom: 1px solid #f1f1f1; display: flex; align-items: center; transition: 0.2s; }
        .rank-list li:hover { background-color: #f8f9fa; }
        .rank-list li:last-child { border-bottom: none; }
        
        .badge-rank { width: 30px; height: 30px; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: bold; font-size: 0.9rem; margin-right: 12px; flex-shrink: 0; }
        .badge-rank-1 { background: #FFD700; color: #856404; box-shadow: 0 2px 5px rgba(255, 215, 0, 0.5); }
        .badge-rank-2 { background: #E0E0E0; color: #424242; box-shadow: 0 2px 5px rgba(224, 224, 224, 0.5); }
        .badge-rank-3 { background: #CD7F32; color: #5C3A21; box-shadow: 0 2px 5px rgba(205, 127, 50, 0.5); }
        .badge-rank-other { background: #f1f3f5; color: #6c757d; font-size: 0.75rem; }
        
        .tier-badge { padding: 8px 15px; border-radius: 20px; font-weight: bold; font-size: 0.9rem; display: inline-flex; align-items: center; justify-content: center; }
        .tier-1 { background: linear-gradient(45deg, #FFD700, #FFA500); color: white; box-shadow: 0 4px 10px rgba(255, 215, 0, 0.3); }
        .tier-5 { background: linear-gradient(45deg, #C0C0C0, #808080); color: white; }
        .tier-10 { background: linear-gradient(45deg, #cd7f32, #a0522d); color: white; }

        /* ================= RESPONSIVE (MOBILE FIX) ================= */
        @media (max-width: 768px) {
            .nav-top { position: static; text-align: center; padding-top: 20px; margin-bottom: 10px; }
            .nav-top a { display: inline-block; padding: 8px 20px; background-color: #f8f9fa; border: 1px solid #ddd; border-radius: 20px; }
            .search-section { padding: 20px 0; }
            
            .brand-logo { flex-direction: column; gap: 10px; margin-bottom: 25px; }
            .brand-icon { width: 90px; height: 90px; }
            .brand-text { font-size: 38px; letter-spacing: -1.5px; }

            .search-box { padding: 0 15px; }
            .search-input { font-size: 1rem; padding: 12px 20px; }
            .btn-action { display: block; width: calc(100% - 30px); margin: 10px auto; }
            
            .stats-container { margin-top: 30px !important; }
            .col-6.col-md-3 { margin-bottom: 20px; }
        }
    </style>
</head>
<body>

<div class="main-wrapper">

    <!-- Top Navigation Menu -->
    <div class="nav-top dropdown">
        <a href="#" class="dropdown-toggle text-decoration-none d-flex align-items-center text-dark" data-bs-toggle="dropdown" aria-expanded="false" style="padding-bottom: 5px; font-weight: 500;">
            Submit Document
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-3" style="border-top: 3px solid #003366 !important; min-width: 200px;">
            <li>
                <a class="dropdown-item py-2 fw-medium text-dark" href="/submit" style="margin-left: 0; border: none;">
                    <i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>Upload PDF
                </a>
            </li>
            {{-- <li><hr class="dropdown-divider"></li> --}}
            {{-- <li>
                <a class="dropdown-item py-2 fw-medium text-dark" href="/publisher" style="margin-left: 0; border: none;">
                    <i class="bi bi-filetype-xml text-success me-2"></i>Upload XML (Publisher)
                </a>
            </li> --}}
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

    <!-- Hero Search Section -->
    <div class="container search-section">
        <div class="brand-logo">
            <div class="brand-icon">
                <img src="{{ asset('logo/1_Main_Sustaindex_potrait.png') }}" alt="SustaIndex Icon">
            </div>
            <div class="brand-text">
                SustaIndex<span>Search</span>
            </div>
        </div>
        
        <div class="search-box">
            <form action="/results" method="GET">
                <input type="text" name="q" class="form-control search-input" placeholder="Search by title, author, abstract, or ID..." autocomplete="off" autofocus required>
                
                <div class="text-center mt-3 d-inline-block position-relative">
                    <button type="submit" class="btn btn-light border btn-action text-dark shadow-sm hover-shadow">Search Database</button>
                    <div class="btn-group">
                        <button type="button" class="btn btn-light border btn-action text-dark shadow-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            Submit to Index
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-3" style="border-top: 3px solid #003366 !important; min-width: 200px;">
                            <li>
                                <a class="dropdown-item py-2 fw-medium text-dark" href="/submit">
                                    <i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>Upload PDF
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            {{-- <li>
                                <a class="dropdown-item py-2 fw-medium text-dark" href="/publisher">
                                    <i class="bi bi-filetype-xml text-success me-2"></i>Upload XML (Publisher)
                                </a> --}}
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

    <!-- Statistik Counter (Live Data) -->
    <div class="container my-4" style="max-width: 1050px;">
        <div class="card border-0 shadow-sm rounded-4 p-4" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border: 1px solid #eaeaea !important;">
            <div class="row row-cols-2 row-cols-md-5 text-center align-items-center g-3 justify-content-center">
                
                <div class="col">
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                        <i class="bi bi-file-earmark-text-fill fs-4" style="color: #003366;"></i>
                        <span class="h3 fw-bold mb-0 counter" data-target="{{ $totalDocuments ?? 0 }}" style="color: #003366; font-family: 'Georgia', serif;">0</span>
                    </div>
                    <span class="text-uppercase text-muted fw-semibold" style="letter-spacing: 0.5px; font-size: 0.72rem;">Indexed Articles</span>
                </div>
                
                <div class="col border-start-md">
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                        <i class="bi bi-people-fill fs-4" style="color: #003366;"></i>
                        <span class="h3 fw-bold mb-0 counter" data-target="{{ $totalAuthors ?? 0 }}" style="color: #003366; font-family: 'Georgia', serif;">0</span>
                    </div>
                    <span class="text-uppercase text-muted fw-semibold" style="letter-spacing: 0.5px; font-size: 0.72rem;">Researchers</span>
                </div>
                
                <div class="col border-start-md">
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                        <i class="bi bi-bank2 fs-4" style="color: #003366;"></i>
                        <span class="h3 fw-bold mb-0 counter" data-target="{{ $totalInstitutions ?? 0 }}" style="color: #003366; font-family: 'Georgia', serif;">0</span>
                    </div>
                    <span class="text-uppercase text-muted fw-semibold" style="letter-spacing: 0.5px; font-size: 0.72rem;">Institutions</span>
                </div>
                
                <div class="col border-start-md">
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                        <i class="bi bi-globe-americas fs-4" style="color: #003366;"></i>
                        <span class="h3 fw-bold mb-0 counter" data-target="{{ $totalCountries ?? 0 }}" style="color: #003366; font-family: 'Georgia', serif;">0</span>
                    </div>
                    <span class="text-uppercase text-muted fw-semibold" style="letter-spacing: 0.5px; font-size: 0.72rem;">Countries</span>
                </div>

                <div class="col border-start-md mt-4 mt-md-0">
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                        <i class="bi bi-eye-fill fs-4" style="color: #003366;"></i>
                        <span class="h3 fw-bold mb-0 counter" data-target="{{ $totalVisitors ?? 0 }}" style="color: #003366; font-family: 'Georgia', serif;">0</span>
                    </div>
                    <span class="text-uppercase text-muted fw-semibold" style="letter-spacing: 0.5px; font-size: 0.72rem;">Global Visitors</span>
                </div>

            </div>
        </div>
    </div>


    <!-- ========================================================= -->
    <!-- 🔥 SUSTAINDEX RANKINGS (MENGGANTIKAN LEADERBOARD LAMA) 🔥 -->
    <!-- ========================================================= -->
    <div class="container stats-container" style="margin-top: 80px; margin-bottom: 60px;">
        
        <!-- Judul Utama -->
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-2" style="font-family: 'Georgia', serif; color: #003366;">
                SustaIndex Rankings & Inclusive Research Awards
            </h2>
            <p class="text-muted mx-auto" style="max-width: 750px; font-size: 0.95rem;">
                Recognizing the world's leading researchers and institutions driving sustainable and inclusive development. Assessment is based on SDG contributions, inclusivity focus, and overall academic impact.
            </p>
        </div>

        <!-- TIER AWARDS: TOP 1%, 5%, 10% -->
        <div class="row g-4 mb-5 text-center justify-content-center">
            <div class="col-md-4">
                <div class="p-4 border rounded-4 bg-white shadow-sm h-100" style="border-top: 4px solid #FFD700 !important;">
                    <div class="tier-badge tier-1 mb-3">🏅 Top 1% Percentile</div>
                    <h6 class="fw-bold text-dark">Elite Sustainability Institutions</h6>
                    <p class="text-muted small mb-0">Institutions with the highest collective research contribution to the 17 SDGs and inclusive development globally.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 border rounded-4 bg-white shadow-sm h-100" style="border-top: 4px solid #C0C0C0 !important;">
                    <div class="tier-badge tier-5 mb-3">🥈 Top 5% Percentile</div>
                    <h6 class="fw-bold text-dark">Leading Institutions</h6>
                    <p class="text-muted small mb-0">Universities and research centers showing exceptional consistency in sustainable output.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 border rounded-4 bg-white shadow-sm h-100" style="border-top: 4px solid #cd7f32 !important;">
                    <div class="tier-badge tier-10 mb-3">🥉 Top 10% Percentile</div>
                    <h6 class="fw-bold text-dark">Outstanding Contributors</h6>
                    <p class="text-muted small mb-0">Institutions demonstrating a strong commitment to addressing global challenges.</p>
                </div>
            </div>
        </div>

        <!-- GRID LEADERBOARDS (100% DATA DATABASE) -->
        <div class="row g-4">
            
            <!-- 1. Top Sustainable Researchers -->
            <div class="col-md-6">
                <div class="rank-section border-top border-4 border-success">
                    <div class="rank-header bg-success text-white">
                        <i class="bi bi-globe-americas me-2"></i> Top Sustainable Researchers
                    </div>
                    <div class="rank-desc">
                        Highest cumulative contribution points to SDGs
                    </div>
                    <ul class="rank-list">
                        @forelse($topSustainable ?? [] as $index => $author)
                        <li>
                            <div class="badge-rank {{ $index == 0 ? 'badge-rank-1' : ($index == 1 ? 'badge-rank-2' : 'badge-rank-3') }}">{{ $index + 1 }}</div>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-dark" style="font-size: 0.9rem;">
                                    <a href="/author/{{ $author->id }}" class="text-dark text-decoration-none hover-underline">{{ $author->name }}</a>
                                </div>
                                <div class="text-muted small">🏛️ {{ $author->institution->name ?? 'Independent' }}</div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success bg-opacity-10 text-success border border-success">Score: {{ number_format($author->sdg_count * 0.1, 1) }}</span>
                            </div>
                        </li>
                        @empty
                        <li class="justify-content-center text-muted small py-4">No data available yet.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- 2. Most Productive Researchers -->
            <div class="col-md-6">
                <div class="rank-section border-top border-4 border-primary">
                    <div class="rank-header bg-primary text-white">
                        <i class="bi bi-person-workspace me-2"></i> Most Productive Researchers
                    </div>
                    <div class="rank-desc">
                        Highest volume of indexed publications
                    </div>
                    <ul class="rank-list">
                        @forelse($topResearchers ?? [] as $index => $author)
                        <li>
                            <div class="badge-rank {{ $index == 0 ? 'badge-rank-1' : ($index == 1 ? 'badge-rank-2' : 'badge-rank-3') }}">{{ $index + 1 }}</div>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-dark" style="font-size: 0.9rem;">
                                    <a href="/author/{{ $author->id }}" class="text-dark text-decoration-none hover-underline">{{ $author->name }}</a>
                                </div>
                                <div class="text-muted small">🏛️ {{ $author->institution->name ?? 'Independent' }}</div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-light text-dark border"><i class="bi bi-file-earmark-text text-primary"></i> {{ $author->documents_count }} Papers</span>
                            </div>
                        </li>
                        @empty
                        <li class="justify-content-center text-muted small py-4">No data available yet.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- 3. Most Impactful Articles -->
            <div class="col-md-4">
                <div class="rank-section border-top border-4 border-danger">
                    <div class="rank-header bg-danger text-white">
                        <i class="bi bi-journal-bookmark-fill me-2"></i> Most Impactful Articles
                    </div>
                    <div class="rank-desc">
                        Highest number of citations
                    </div>
                    <ul class="rank-list flex-column align-items-stretch">
                        @forelse($mostCited ?? [] as $index => $doc)
                        <li class="align-items-start text-start">
                            <div class="badge-rank badge-rank-other mt-1">{{ $index + 1 }}</div>
                            <div class="flex-grow-1">
                                <a href="/document/{{ $doc->document_number }}" class="fw-bold text-dark text-decoration-none d-block mb-1" style="font-size: 0.85rem; line-height: 1.3;">
                                    {{ Str::limit($doc->title, 60) }}
                                </a>
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger rounded-pill" style="font-size: 0.65rem;"><i class="bi bi-chat-quote-fill"></i> {{ number_format($doc->citation_count) }} Citations</span>
                            </div>
                        </li>
                        @empty
                        <li class="justify-content-center text-muted small py-4">No data available yet.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- 4. Rising Researchers -->
            <div class="col-md-4">
                <div class="rank-section border-top border-4 border-warning">
                    <div class="rank-header text-dark" style="background-color: #ffc107;">
                        <i class="bi bi-rocket-takeoff-fill me-2"></i> Rising Researchers
                    </div>
                    <div class="rank-desc">
                        Fastest growth in the last 2 years
                    </div>
                    <ul class="rank-list">
                        @forelse($risingStars ?? [] as $index => $author)
                        <li>
                            <div class="badge-rank {{ $index == 0 ? 'badge-rank-1' : ($index == 1 ? 'badge-rank-2' : 'badge-rank-3') }}">{{ $index + 1 }}</div>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-dark" style="font-size: 0.85rem;">
                                    <a href="/author/{{ $author->id }}" class="text-dark text-decoration-none hover-underline">{{ Str::limit($author->name, 20) }}</a>
                                </div>
                            </div>
                            <span class="text-success small fw-bold"><i class="bi bi-arrow-up"></i> {{ $author->recent_docs }} Recent</span>
                        </li>
                        @empty
                        <li class="justify-content-center text-muted small py-4">No data available yet.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- 5. Top Global Institutions -->
            <div class="col-md-4">
                <div class="rank-section border-top border-4 border-info">
                    <div class="rank-header bg-info text-dark">
                        <i class="bi bi-bank2 me-2"></i> Top Global Institutions
                    </div>
                    <div class="rank-desc">
                        Highest number of indexed authors
                    </div>
                    <ul class="rank-list">
                        @forelse($topInstitutions ?? [] as $index => $inst)
                        <li>
                            <div class="badge-rank {{ $index == 0 ? 'badge-rank-1' : ($index == 1 ? 'badge-rank-2' : 'badge-rank-3') }}">{{ $index + 1 }}</div>
                            <div class="flex-grow-1 fw-bold text-dark" style="font-size: 0.85rem;">
                                <a href="/institution/{{ $inst->id }}" class="text-dark text-decoration-none hover-underline">{{ Str::limit($inst->name, 25) }}</a>
                            </div> 
                            <span class="badge bg-info text-dark rounded-pill">{{ $inst->authors_count }}</span>
                        </li>
                        @empty
                        <li class="justify-content-center text-muted small py-4">No data available yet.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- Tombol Jelajahi Lebih Lanjut -->
            <div class="col-12 text-center mt-4">
                <a href="{{ route('rankings.index') }}" class="btn btn-outline-primary px-5 py-2 fw-bold rounded-pill shadow-sm">View Full SustaIndex Rankings Directory <i class="bi bi-arrow-right ms-2"></i></a>
            </div>

        </div>
    </div>
    <!-- ================= AKHIR BAGIAN SUSTAINDEX RANKINGS ================= -->

</div> <!-- TUTUP DIV MAIN-WRAPPER -->

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const counters = document.querySelectorAll('.counter');
        const speed = 150; 

        const animateCounters = () => {
            counters.forEach(counter => {
                const updateCount = () => {
                    const target = +counter.getAttribute('data-target');
                    const count = +counter.innerText.replace(/,/g, ''); 
                    const inc = target / speed;

                    if (count < target) {
                        counter.innerText = Math.ceil(count + inc).toLocaleString('en-US');
                        setTimeout(updateCount, 15);
                    } else {
                        counter.innerText = target.toLocaleString('en-US');
                    }
                };
                updateCount();
            });
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    observer.disconnect(); 
                }
            });
        }, { threshold: 0.2 }); 

        const card = document.querySelector('.counter')?.closest('.card');
        if (card) observer.observe(card);
    });
</script>

@include('partials.footer')
</body>
</html>