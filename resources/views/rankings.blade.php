<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SustaIndex Rankings & Inclusive Awards</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="{{ asset('logo/1_Main_Sustaindex_potrait.png') }}">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* 🔥 FIX: HEADER SUSTAINDEX (Sesuai Halaman Journal) */
        .academic-header { background-color: #003366 !important; padding: 15px 0; border-bottom: 4px solid #cc0000 !important; }
        .academic-header a, .academic-header .dropdown-toggle { color: white !important; font-weight: 500; }
        .academic-header .dropdown-menu a { color: #212529 !important; } /* Teks dropdown tetap gelap */
        .academic-header .dropdown-menu a i { color: inherit; } 
        
        /* Hero Section Rankings */
        .hero-rank {
            background: linear-gradient(to bottom, #003366, #002244); /* Menyatu dengan header biru */
            color: white;
            padding: 70px 0 60px 0;
            text-align: center;
        }

        /* Tier Cards */
        .tier-card {
            background: white; border-radius: 12px; padding: 25px 20px; height: 100%;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05); transition: transform 0.3s;
        }
        .tier-card:hover { transform: translateY(-5px); }
        .tier-badge { padding: 6px 15px; border-radius: 20px; font-weight: bold; font-size: 0.85rem; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px; }
        .tier-1 { background: linear-gradient(45deg, #FFD700, #FFA500); color: white; box-shadow: 0 4px 10px rgba(255, 215, 0, 0.3); }
        .tier-5 { background: linear-gradient(45deg, #C0C0C0, #808080); color: white; }
        .tier-10 { background: linear-gradient(45deg, #cd7f32, #a0522d); color: white; }

        /* Rank Sections (Grid Box) */
        .rank-section { 
            background: #ffffff; border-radius: 12px; border: 1px solid #e0e0e0; 
            box-shadow: 0 3px 10px rgba(0,0,0,0.02); overflow: hidden; 
            height: 100%; display: flex; flex-direction: column;
        }
        .rank-header { padding: 12px 15px; font-weight: bold; font-size: 0.95rem; color: white; }
        .rank-desc { font-size: 0.75rem; padding: 8px; background: #f8f9fa; color: #6c757d; text-align: center; border-bottom: 1px solid #f1f1f1; }
        
        .rank-list { list-style: none; padding: 0; margin: 0; flex-grow: 1; }
        .rank-list li { padding: 12px 15px; border-bottom: 1px solid #f1f1f1; display: flex; align-items: center; transition: 0.2s; }
        .rank-list li:hover { background-color: #f8f9fc; }
        .rank-list li:last-child { border-bottom: none; }
        
        .badge-rank { width: 30px; height: 30px; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: bold; font-size: 0.9rem; margin-right: 12px; flex-shrink: 0; }
        .badge-rank-1 { background: #FFD700; color: #856404; box-shadow: 0 2px 5px rgba(255, 215, 0, 0.5); }
        .badge-rank-2 { background: #E0E0E0; color: #424242; box-shadow: 0 2px 5px rgba(224, 224, 224, 0.5); }
        .badge-rank-3 { background: #CD7F32; color: #5C3A21; box-shadow: 0 2px 5px rgba(205, 127, 50, 0.5); }
        .badge-rank-other { background: #f1f3f5; color: #6c757d; font-size: 0.8rem; }
    </style>
</head>
<body>

@include('partials.header')

<!-- HERO SECTION -->
<div class="hero-rank">
    <div class="container">
        <h1 class="fw-bold mb-2" style="font-family: 'Georgia', serif; font-size: 2.5rem;">SustaIndex Rankings Directory</h1>
        <p class="lead mb-0 mx-auto" style="max-width: 800px; color: #d0e1f9; font-size: 1rem;">
            The definitive global index recognizing excellence in sustainable development research, inclusivity, and academic impact.
        </p>
    </div>
</div>

<div class="container my-5">
    
    <!-- TIER AWARDS -->
    <div class="row g-3 mb-5 text-center justify-content-center">
        <div class="col-md-4">
            <div class="tier-card" style="border-top: 4px solid #FFD700;">
                <div class="tier-badge tier-1">🏅 Top 1% Percentile</div>
                <h6 class="fw-bold text-dark">Elite Institutions</h6>
                <p class="text-muted small mb-0">Highest collective research contribution to the 17 SDGs globally.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="tier-card" style="border-top: 4px solid #C0C0C0;">
                <div class="tier-badge tier-5">🥈 Top 5% Percentile</div>
                <h6 class="fw-bold text-dark">Leading Institutions</h6>
                <p class="text-muted small mb-0">Consistent focus on global sustainability and inclusivity challenges.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="tier-card" style="border-top: 4px solid #cd7f32;">
                <div class="tier-badge tier-10">🥉 Top 10% Percentile</div>
                <h6 class="fw-bold text-dark">Outstanding Contributors</h6>
                <p class="text-muted small mb-0">Significant volume of inclusive and impactful academic work.</p>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- GRID 3 KOLOM (100% DATA DATABASE)          -->
    <!-- ========================================== -->
    <div class="row g-4">
        
        <!-- 1. Top Sustainable (Score = Badges x 0.1) -->
        <div class="col-lg-4 col-md-6">
            <div class="rank-section border-top border-4 border-success">
                <div class="rank-header bg-success"><i class="bi bi-globe-americas me-2"></i> Top Sustainable</div>
                <div class="rank-desc">Based on overall SDG contribution score (0.1 pt/badge)</div>
                <ul class="rank-list">
                    @forelse($topSustainable as $index => $author)
                    <li>
                        <div class="badge-rank {{ $index < 3 ? 'badge-rank-'.($index+1) : 'badge-rank-other' }}">{{ $index + 1 }}</div>
                        <div class="flex-grow-1">
                            <a href="/author/{{ $author->id }}" class="fw-bold text-dark text-decoration-none hover-underline" style="font-size: 0.85rem;">{{ Str::limit($author->name, 20) }}</a>
                            <div class="text-muted" style="font-size: 0.7rem;">{{ Str::limit($author->institution->name ?? 'Independent', 25) }}</div>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success">Score: {{ number_format($author->sdg_count * 0.1, 1) }}</span>
                    </li>
                    @empty
                    <li class="justify-content-center text-muted small py-4">No data available yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- 2. Most Productive -->
        <div class="col-lg-4 col-md-6">
            <div class="rank-section border-top border-4 border-primary">
                <div class="rank-header bg-primary"><i class="bi bi-person-workspace me-2"></i> Most Productive</div>
                <div class="rank-desc">Highest volume of indexed publications</div>
                <ul class="rank-list">
                    @forelse($topResearchers as $index => $author)
                    <li>
                        <div class="badge-rank {{ $index < 3 ? 'badge-rank-'.($index+1) : 'badge-rank-other' }}">{{ $index + 1 }}</div>
                        <div class="flex-grow-1">
                            <a href="/author/{{ $author->id }}" class="fw-bold text-dark text-decoration-none hover-underline" style="font-size: 0.85rem;">{{ Str::limit($author->name, 20) }}</a>
                            <div class="text-muted" style="font-size: 0.7rem;">{{ Str::limit($author->institution->name ?? 'Independent', 25) }}</div>
                        </div>
                        <span class="badge bg-light text-dark border">{{ $author->documents_count }} Papers</span>
                    </li>
                    @empty
                    <li class="justify-content-center text-muted small py-4">No data available yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- 3. Most Impactful (Total Citations) -->
        <div class="col-lg-4 col-md-6">
            <div class="rank-section border-top border-4" style="border-color: #6f42c1;">
                <div class="rank-header" style="background-color: #6f42c1;"><i class="bi bi-graph-up-arrow me-2"></i> Most Impactful</div>
                <div class="rank-desc">Total citations across all indexed articles</div>
                <ul class="rank-list">
                    @forelse($mostImpactful as $index => $author)
                    <li>
                        <div class="badge-rank {{ $index < 3 ? 'badge-rank-'.($index+1) : 'badge-rank-other' }}">{{ $index + 1 }}</div>
                        <div class="flex-grow-1">
                            <a href="/author/{{ $author->id }}" class="fw-bold text-dark text-decoration-none hover-underline" style="font-size: 0.85rem;">{{ Str::limit($author->name, 20) }}</a>
                            <div class="text-muted" style="font-size: 0.7rem;">{{ Str::limit($author->institution->name ?? 'Independent', 25) }}</div>
                        </div>
                        <span class="badge bg-light text-dark border"><i class="bi bi-chat-quote-fill text-primary"></i> {{ number_format($author->total_citations ?? 0) }}</span>
                    </li>
                    @empty
                    <li class="justify-content-center text-muted small py-4">No data available yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- 4. Interdisciplinary Leaders -->
        <div class="col-lg-4 col-md-6">
            <div class="rank-section border-top border-4 border-info">
                <div class="rank-header bg-info text-dark"><i class="bi bi-intersect me-2"></i> Interdisciplinary</div>
                <div class="rank-desc">Contributions across multiple diverse SDGs</div>
                <ul class="rank-list">
                    @forelse($interdisciplinary as $index => $author)
                    <li>
                        <div class="badge-rank {{ $index < 3 ? 'badge-rank-'.($index+1) : 'badge-rank-other' }}">{{ $index + 1 }}</div>
                        <div class="flex-grow-1">
                            <a href="/author/{{ $author->id }}" class="fw-bold text-dark text-decoration-none hover-underline" style="font-size: 0.85rem;">{{ Str::limit($author->name, 20) }}</a>
                        </div>
                        <span class="badge bg-info text-dark rounded-pill">{{ $author->distinct_sdgs }} SDGs</span>
                    </li>
                    @empty
                    <li class="justify-content-center text-muted small py-4">No data available yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- 5. Rising Stars -->
        <div class="col-lg-4 col-md-6">
            <div class="rank-section border-top border-4 border-warning">
                <div class="rank-header text-dark" style="background-color: #ffc107;"><i class="bi bi-rocket-takeoff-fill me-2"></i> Rising Stars</div>
                <div class="rank-desc">Highest output in the last 2 years</div>
                <ul class="rank-list">
                    @forelse($risingStars as $index => $author)
                    <li>
                        <div class="badge-rank {{ $index < 3 ? 'badge-rank-'.($index+1) : 'badge-rank-other' }}">{{ $index + 1 }}</div>
                        <div class="flex-grow-1">
                            <a href="/author/{{ $author->id }}" class="fw-bold text-dark text-decoration-none hover-underline" style="font-size: 0.85rem;">{{ Str::limit($author->name, 20) }}</a>
                        </div>
                        <span class="text-success fw-bold" style="font-size: 0.8rem;"><i class="bi bi-arrow-up"></i> {{ $author->recent_docs }} Recent</span>
                    </li>
                    @empty
                    <li class="justify-content-center text-muted small py-4">No data available yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- 6. Most Collaborative -->
        <div class="col-lg-4 col-md-6">
            <div class="rank-section border-top border-4" style="border-color: #e83e8c;">
                <div class="rank-header" style="background-color: #e83e8c;"><i class="bi bi-people-fill me-2"></i> Most Collaborative</div>
                <div class="rank-desc">Highest number of distinct co-authors</div>
                <ul class="rank-list">
                    @forelse($collaborative as $index => $author)
                    <li>
                        <div class="badge-rank {{ $index < 3 ? 'badge-rank-'.($index+1) : 'badge-rank-other' }}">{{ $index + 1 }}</div>
                        <div class="flex-grow-1">
                            <a href="/author/{{ $author->id }}" class="fw-bold text-dark text-decoration-none hover-underline" style="font-size: 0.85rem;">{{ Str::limit($author->name, 20) }}</a>
                        </div>
                        <span class="badge bg-light text-dark border">{{ $author->collab_count }} Partners</span>
                    </li>
                    @empty
                    <li class="justify-content-center text-muted small py-4">No data available yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- 7. Top Institutions -->
        <div class="col-lg-4 col-md-6">
            <div class="rank-section border-top border-4" style="border-color: #003366;">
                <div class="rank-header" style="background-color: #003366;"><i class="bi bi-bank2 me-2"></i> Top Institutions</div>
                <div class="rank-desc">Institutions with the most indexed authors</div>
                <ul class="rank-list">
                    @forelse($topInstitutions as $index => $inst)
                    <li>
                        <div class="badge-rank {{ $index < 3 ? 'badge-rank-'.($index+1) : 'badge-rank-other' }}">{{ $index + 1 }}</div>
                        <div class="flex-grow-1">
                            <a href="/institution/{{ $inst->id }}" class="fw-bold text-dark text-decoration-none hover-underline" style="font-size: 0.85rem;">{{ Str::limit($inst->name, 25) }}</a>
                        </div> 
                        <span class="badge bg-light text-dark border rounded-pill">{{ $inst->authors_count }}</span>
                    </li>
                    @empty
                    <li class="justify-content-center text-muted small py-4">No data available yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- 8. Top Countries -->
        <div class="col-lg-4 col-md-6">
            <div class="rank-section border-top border-4 border-secondary">
                <div class="rank-header bg-secondary"><i class="bi bi-flag-fill me-2"></i> Top Countries</div>
                <div class="rank-desc">Ranking based on national institutional presence</div>
                <ul class="rank-list">
                    @forelse($topCountries as $index => $country)
                    <li>
                        <div class="badge-rank {{ $index < 3 ? 'badge-rank-'.($index+1) : 'badge-rank-other' }}">{{ $index + 1 }}</div>
                        <div class="flex-grow-1 fw-bold text-dark" style="font-size: 0.85rem;">
                            {{ $country->country }}
                        </div> 
                        <span class="badge bg-secondary text-white rounded-pill">{{ $country->total }} Inst.</span>
                    </li>
                    @empty
                    <li class="justify-content-center text-muted small py-4">No data available yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- 9. Most Impactful Articles -->
        <div class="col-lg-4 col-md-12">
            <div class="rank-section border-top border-4 border-danger">
                <div class="rank-header bg-danger"><i class="bi bi-journal-bookmark-fill me-2"></i> Most Impactful Articles</div>
                <div class="rank-desc">Documents with the highest citation counts</div>
                <ul class="rank-list flex-column align-items-stretch">
                    @forelse($mostCited as $index => $doc)
                    <li class="align-items-start">
                        <div class="badge-rank {{ $index < 3 ? 'badge-rank-'.($index+1) : 'badge-rank-other' }} mt-1">{{ $index + 1 }}</div>
                        <div class="flex-grow-1">
                            <a href="/document/{{ $doc->document_number }}" class="fw-bold text-dark text-decoration-none d-block mb-1 hover-underline" style="font-size: 0.8rem; line-height: 1.3;">
                                {{ Str::limit($doc->title, 55) }}
                            </a>
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger rounded-pill" style="font-size: 0.65rem;">
                                <i class="bi bi-chat-quote-fill"></i> {{ number_format($doc->citation_count) }} Citations
                            </span>
                        </div>
                    </li>
                    @empty
                    <li class="justify-content-center text-muted small py-4">No data available yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>

    </div>
</div>

@include('partials.footer')
</body>
</html>