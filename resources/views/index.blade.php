<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SustaIndex - Academic Indexing System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        /* Efek saat filter diklik (aktif) */
        .filter-list li.active { 
            background-color: #e8f0fe; 
            font-weight: bold; 
            color: #003366; 
            border-left: 4px solid #cc0000; 
        }
        .filter-list li.active:hover { text-decoration: none; }
        /* ================= RESPONSIVE (MOBILE & TABLET) ================= */
        @media (max-width: 768px) {
            .main-container { padding: 20px 15px; margin-top: 20px; margin-bottom: 30px; }
            .academic-title { font-size: 1.4rem; }
            .section-title { font-size: 1.1em; margin-top: 20px; }
            .academic-header .container { flex-direction: column; text-align: center; gap: 10px; }
            .academic-nav { display: flex !important; justify-content: center; gap: 15px; width: 100%; margin: 0; padding: 0; }
            .academic-nav a { margin: 0; font-size: 0.9rem; }
            .btn-academic, .btn-secondary-academic { width: 100%; margin-bottom: 10px; display: block; }
            .d-flex.justify-content-between.mt-5 { flex-direction: column-reverse; gap: 10px; }
            .d-flex.justify-content-between.mt-5 button { width: 100%; }
            .academic-footer .text-md-end { text-align: left !important; margin-top: 20px; }
            .academic-footer .btn { width: auto; display: inline-block; }
        }
        /* --- HEADER & FOOTER SustaIndex --- */
        .academic-header { background-color: #003366; color: white; padding: 15px 0; border-bottom: 4px solid #cc0000; }
        .academic-header a { color: white; text-decoration: none; }
        .academic-title { font-family: 'Georgia', serif; font-size: 1.8rem; font-weight: normal; margin: 0; }
        .academic-nav a { font-size: 0.95rem; font-weight: bold; margin-left: 25px; color: #e0e0e0; padding-bottom: 5px; border-bottom: 2px solid transparent; transition: 0.2s; }
        .academic-nav a:hover, .academic-nav a.active { color: white; border-bottom: 2px solid #cc0000; }
        .academic-footer { background-color: #f1f3f5; color: #444; border-top: 1px solid #d5d5d5; padding: 40px 0 20px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin-top: 60px; }
        .academic-footer a { color: #003366; text-decoration: none; font-weight: 500; }
        .academic-footer a:hover { text-decoration: underline; }
        .footer-logo { font-family: 'Georgia', serif; font-size: 1.4rem; font-weight: bold; color: #003366; }
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar-brand { font-size: 1.5rem; letter-spacing: 0.5px; }
        
        /* Gaya Sidebar Filter */
        .filter-box { background: white; border: 1px solid #e0e0e0; border-radius: 5px; margin-bottom: 20px; }
        .filter-header { background: #f1f3f5; padding: 10px 15px; font-weight: bold; color: #333; font-size: 0.9em; text-transform: uppercase; border-bottom: 1px solid #e0e0e0; }
        .filter-list { list-style: none; padding: 0; margin: 0; }
        .filter-list li { padding: 8px 15px; border-bottom: 1px solid #f1f1f1; font-size: 0.9em; display: flex; justify-content: space-between; color: #0056b3; cursor: pointer;}
        .filter-list li:hover { background-color: #f8f9fa; text-decoration: underline; }
        .filter-count { color: #6c757d; font-size: 0.85em; background: #e9ecef; padding: 2px 8px; border-radius: 10px;}

        /* Gaya Hasil Pencarian */
        .search-box { box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .result-card { background: white; border: none; border-bottom: 1px solid #e0e0e0; padding: 20px 0; border-radius: 0; }
        .doc-title { color: #1a0dab; font-size: 1.25rem; font-weight: 600; text-decoration: none; }
        .doc-title:hover { text-decoration: underline; }
        .doc-authors { color: #006621; font-size: 0.95em; margin-bottom: 8px; }
        .doc-abstract { color: #4d5156; font-size: 0.95em; line-height: 1.6; }
        .doc-meta { font-size: 0.85em; color: #70757a; margin-top: 10px; }
        .badge-type { background-color: #e8f0fe; color: #1967d2; border: 1px solid #c6dafc; }

        /* SDG Badge Aktif */
        .btn-outline-primary.active-sdg {
            background-color: #0d6efd !important;
            color: white !important;
            border-color: #0d6efd !important;
        }
        .btn-outline-primary.active-sdg .sdg-count {
            background-color: white !important;
            color: #0d6efd !important;
        }
    </style>
</head>
<body>

@include('partials.header')

<div class="container mt-5 mb-5">
    <div class="row">
        
        <div class="col-md-3" id="filterSidebar">
            <h5 class="mb-3 fw-bold text-dark" style="font-family: 'Georgia', serif;">Filter Results</h5>
            
            <div class="filter-box mb-3 border rounded shadow-sm bg-white">
                <div class="filter-header bg-light border-bottom p-2 fw-bold text-secondary small text-uppercase" style="letter-spacing: 0.5px;">Author / Contributor</div>
                <div class="p-3">
                    <input type="text" id="authorFilterInput" class="form-control form-control-sm" placeholder="e.g. John Doe">
                </div>
            </div>

           <!-- FILTER SDG (FULL 17 GOALS) -->
           <div class="filter-box mb-3 border rounded shadow-sm bg-white">
                <div class="filter-header bg-light border-bottom p-2 fw-bold text-secondary small text-uppercase" style="letter-spacing: 0.5px;">Sustainable Goals</div>
                <ul class="filter-list list-unstyled p-2 mb-0 small">
                    @forelse($sdgFacets as $sdgCode => $data)
                        <li class="filter-item filter-sdg p-1 d-flex justify-content-between align-items-center {{ request('sdg') == $sdgCode ? 'active' : '' }}" 
                            data-sdg="{{ $sdgCode }}" 
                            style="cursor:pointer;"
                            title="{{ $sdgCode }}: {{ $data['name'] }}"> <span class="text-truncate" style="max-width: 85%;">
                                <strong class="text-primary">{{ $sdgCode }}:</strong> <span class="text-muted">{{ $data['name'] }}</span>
                            </span>
                            <span class="badge {{ request('sdg') == $sdgCode ? 'bg-primary' : 'bg-secondary' }} rounded-pill">{{ $data['count'] }}</span>
                        </li>
                    @empty
                        <li class="p-1 text-muted fst-italic small text-center">No SDGs mapped yet.</li>
                    @endforelse
                </ul>
            </div>

            <div class="filter-box mb-3 border rounded shadow-sm bg-white">
                <div class="filter-header bg-light border-bottom p-2 fw-bold text-secondary small text-uppercase" style="letter-spacing: 0.5px;">Publication Type</div>
                <ul class="filter-list list-unstyled p-2 mb-0 small">
                    @foreach($docTypes as $type)
                        <li class="filter-item type-item p-1 d-flex justify-content-between align-items-center {{ $loop->iteration > 5 ? 'd-none extra-type' : '' }}" data-filter="type" data-value="{{ $type->document_type }}" style="cursor:pointer;">
                            <span>{{ $type->document_type ?: 'Unknown' }}</span>
                            <span class="badge bg-secondary rounded-pill">{{ $type->total }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="filter-box mb-3 border rounded shadow-sm bg-white">
                <div class="filter-header bg-light border-bottom p-2 fw-bold text-secondary small text-uppercase" style="letter-spacing: 0.5px;">Top Publishers</div>
                <ul class="filter-list list-unstyled p-2 mb-0 small">
                    @foreach($topPublishers as $pub)
                        <li class="filter-item pub-item p-1 d-flex justify-content-between align-items-center" data-filter="publisher" data-value="{{ $pub->publisher }}" style="cursor:pointer;">
                            <span class="text-truncate" style="max-width: 80%;">{{ $pub->publisher }}</span>
                            <span class="badge bg-secondary rounded-pill">{{ $pub->total }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="filter-box mb-3 border rounded shadow-sm bg-white">
                <div class="filter-header bg-light border-bottom p-2 fw-bold text-secondary small text-uppercase" style="letter-spacing: 0.5px;">Publication Year</div>
                <ul class="filter-list list-unstyled p-2 mb-0 small">
                    <li class="filter-item year-item p-1 d-flex justify-content-between align-items-center" data-filter="year" data-value="exact_{{ $yearStats['current_year'] }}" style="cursor:pointer;">
                        <span>In {{ $yearStats['current_year'] }}</span> <span class="badge bg-secondary rounded-pill" id="count_current">{{ $yearStats['count_current'] }}</span>
                    </li>
                    <li class="filter-item year-item p-1 d-flex justify-content-between align-items-center" data-filter="year" data-value="since_{{ $yearStats['last_year'] }}" style="cursor:pointer;">
                        <span>Since {{ $yearStats['last_year'] }}</span> <span class="badge bg-secondary rounded-pill" id="count_last">{{ $yearStats['count_last'] }}</span>
                    </li>
                    <li class="filter-item year-item p-1 d-flex justify-content-between align-items-center" data-filter="year" data-value="since_{{ $yearStats['year_5'] }}" style="cursor:pointer;">
                        <span>Since {{ $yearStats['year_5'] }} (5 Yrs)</span> <span class="badge bg-secondary rounded-pill" id="count_5">{{ $yearStats['count_5'] }}</span>
                    </li>
                </ul>
            </div>

            <button id="btnResetFilter" class="btn btn-sm btn-outline-danger w-100 mb-4 fw-bold shadow-sm d-none"><i class="bi bi-x-circle me-1"></i> Reset All Filters</button>

            <div class="trending-widget mt-4">
                <h6 class="fw-bold text-dark mb-3" style="font-family: 'Georgia', serif;">
                    <i class="bi bi-graph-up-arrow text-primary me-2"></i>Trending Now
                </h6>
                <div class="list-group shadow-sm border-0">
                    @forelse($mostPopular as $doc)
                        <a href="/document/{{ $doc->document_number }}" class="list-group-item list-group-item-action p-3 border-0 border-bottom">
                            <div class="fw-bold text-primary mb-1" style="font-size: 0.85rem; line-height: 1.4;">
                                {{ Str::limit($doc->title, 55) }}
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="badge bg-light text-dark border" style="font-size: 0.7rem;">
                                    <i class="bi bi-eye text-primary"></i> {{ number_format($doc->views) }}
                                </span>
                                <small class="text-muted" style="font-size: 0.7rem;">{{ $doc->pub_year }}</small>
                            </div>
                        </a>
                    @empty
                        <div class="list-group-item p-3 text-muted small text-center">No trending articles.</div>
                    @endforelse
                </div>
            </div>
            </div>

        <div class="col-md-9" id="mainContentColumn">
                        
                <form action="{{ route('search.results') }}" method="GET" class="input-group input-group-lg mb-4 search-box">
                    @if(request('type')) <input type="hidden" name="type" value="{{ request('type') }}"> @endif
                    @if(request('year')) <input type="hidden" name="year" value="{{ request('year') }}"> @endif
                    @if(request('publisher')) <input type="hidden" name="publisher" value="{{ request('publisher') }}"> @endif
                    @if(request('sdg')) <input type="hidden" name="sdg" value="{{ request('sdg') }}"> @endif

                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search by title, author, university, journal, or publisher..." autocomplete="off">
                    <button class="btn btn-primary px-4" type="submit">Search</button>
                </form>

                @if(isset($featuredInstitutions) && $featuredInstitutions->count() > 0)
                <div class="mb-3 p-3 bg-white rounded-3 border shadow-sm" style="border-left: 4px solid #198754 !important;">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-bank2 text-success me-2"></i>
                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.9rem;">
                            Matching Institutions 
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill ms-1" style="font-size: 0.65rem;">{{ $featuredInstitutions->count() }}</span>
                        </h6>
                    </div>
                    <div class="row g-2">
                        @foreach($featuredInstitutions as $inst)
                            <div class="{{ $featuredInstitutions->count() > 1 ? 'col-md-6' : 'col-12' }}">
                                <div class="p-2 border rounded bg-light d-flex justify-content-between align-items-center">
                                    <div class="text-truncate me-2">
                                        <a href="/institution/{{ $inst->id }}" class="fw-bold text-decoration-none text-dark hover-underline" style="font-size: 0.85rem;">
                                            {{ $inst->name }}
                                        </a>
                                        <small class="text-muted d-block" style="font-size: 0.75rem;">
                                            📍 {{ $inst->country ?? 'Global' }} • 👥 {{ $inst->authors_count }} Researchers
                                        </small>
                                    </div>
                                    <a href="/institution/{{ $inst->id }}" class="btn btn-sm btn-outline-success rounded-pill px-2 py-0 flex-shrink-0" style="font-size: 0.7rem;">
                                        Profile
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(isset($featuredAuthors) && $featuredAuthors->count() > 0)
                <div class="mb-3 p-3 bg-white rounded-3 border shadow-sm" style="border-left: 4px solid #0d6efd !important;">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-person-workspace text-primary me-2"></i>
                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.9rem;">
                            Matching Researchers 
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill ms-1" style="font-size: 0.65rem;">{{ $featuredAuthors->count() }}</span>
                        </h6>
                    </div>
                    <div class="row g-2">
                        @foreach($featuredAuthors as $auth)
                            <div class="{{ $featuredAuthors->count() > 1 ? 'col-md-6' : 'col-12' }}">
                                <div class="p-2 border rounded bg-light d-flex justify-content-between align-items-center">
                                    <div class="text-truncate me-2">
                                        <a href="/author/{{ $auth->id }}" class="fw-bold text-decoration-none text-primary hover-underline" style="font-size: 0.85rem;">
                                            {{ $auth->name }}
                                        </a>
                                        <small class="text-muted d-block text-truncate" style="font-size: 0.75rem;">
                                            🏛️ {{ $auth->institution->name ?? 'Independent' }} • 📄 {{ $auth->documents_count }} Papers
                                        </small>
                                    </div>
                                    <a href="/author/{{ $auth->id }}" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0 flex-shrink-0" style="font-size: 0.7rem;">
                                        Profile
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
                <p class="text-muted small">
                    Showing {{ $results->firstItem() ?? 0 }} to {{ $results->lastItem() ?? 0 }} of {{ $results->total() }} results
                    @if(request('q')) for "<strong>{{ request('q') }}</strong>" @endif
                </p>

                    @forelse($results as $item)
                        <div class="result-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <a href="/document/{{ $item->document_number }}" class="doc-title">{{ $item->title }}</a>
                                
                                <div class="ms-3 flex-shrink-0">
                                    <span class="badge rounded-pill bg-white text-primary border border-primary px-2 py-1 shadow-sm" title="Data from Crossref">
                                        <i class="bi bi-chat-quote-fill me-1"></i> Cited by {{ $item->citation_count ?? 0 }}
                                    </span>
                                </div>
                            </div>
                            
                            @if($item->journal_title && $item->publisher)
                            <div class="small text-muted mb-1" style="font-size: 0.9em;">
                                <i class="bi bi-journal-bookmark-fill text-secondary me-1"></i> Published in: 
                                <a href="/journal/{{ urlencode($item->journal_title) }}" class="fw-bold text-decoration-none hover-underline" style="color: #003366;">
                                    {{ $item->journal_title }} <span class="text-secondary">by {{ $item->publisher }}</span>
                                </a>
                            </div>
                            @endif

                            <div class="doc-authors mt-1">
                                @forelse($item->authors as $author)
                                    <a href="/author/{{ $author->id }}" class="text-decoration-none" style="color: inherit;">{{ $author->name }}</a>
                                    @if($author->institution)
                                        <a href="/institution/{{ $author->institution->id }}" class="text-secondary small text-decoration-none">({{ $author->institution->name }})</a>
                                    @endif
                                    @if(!$loop->last); @endif
                                @empty
                                    <span class="text-muted fst-italic">Unknown Author</span>
                                @endforelse
                            </div>
                            
                            <div class="doc-abstract mt-2">{{ Str::limit($item->abstract, 300) }}</div>
                            
                            <div class="doc-meta mt-3">
                                <span class="badge bg-secondary rounded-pill">{{ $item->document_type ?: 'Journal' }}</span>
                                <span class="ms-2">Pub Year: {{ $item->pub_year ?: 'N/A' }}</span>
                                <span class="ms-2">| ID: {{ $item->document_number }}</span>
                                @if($item->doi)
                                <span class="ms-2">| <a href="{{ $item->doi }}" target="_blank" class="text-success text-decoration-none fw-bold">DOI</a></span>
                                @endif
                                
                                @if($item->keywords)
                                <div class="mt-3">
                                    @foreach(explode(',', $item->keywords) as $kw)
                                        @if(trim($kw))
                                        <a href="/results?q={{ urlencode(trim($kw)) }}" class="badge bg-light text-secondary border text-decoration-none me-2 mb-1 hover-keyword" style="transition: 0.2s;"># {{ trim($kw) }}</a>
                                        @endif
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="mt-5 text-center alert bg-white border py-5 shadow-sm rounded-4">
                            <h3 class="fw-bold" style="color: #003366;">🔍 No Results Found</h3>
                            <p class="text-muted">We couldn't find any documents matching your criteria.</p>
                            <a href="{{ route('search.results') }}" class="btn btn-outline-danger mt-3 fw-bold">Reset All Filters</a>
                        </div>
                    @endforelse

                    <div class="mt-4 d-flex justify-content-center">
                        {{ $results->links('pagination::bootstrap-5') }}
                    </div>

                    @if(!request('q') && !request('type') && !request('year') && !request('publisher') && !request('sdg') && !request('author'))
                    <div id="dashboardStats" class="container my-5 pt-5 border-top">
                        <div class="row g-4">
                            </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
<script>
    // Fungsi untuk memperbarui URL saat filter diklik
    function updateUrlFilter(key, value) {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Toggle: Jika filter yang sama diklik lagi, hapus filter tersebut
        if (urlParams.get(key) === value) {
            urlParams.delete(key);
        } else {
            urlParams.set(key, value);
        }
        
        urlParams.delete('page'); // Reset ke halaman 1
        window.location.search = urlParams.toString(); // Reload halaman dengan URL baru
    }

    // 1. Klik Item Filter List (Type, Year, Publisher)
    document.querySelectorAll('.filter-item').forEach(item => {
        item.addEventListener('click', function() {
            updateUrlFilter(this.getAttribute('data-filter'), this.getAttribute('data-value'));
        });
    });

    // 2. Klik Tombol Badge SDG
    document.querySelectorAll('.filter-sdg').forEach(btn => {
        btn.addEventListener('click', function() {
            updateUrlFilter('sdg', this.getAttribute('data-sdg'));
        });
    });

    // 3. Input Author via Tombol Enter
    const authorInput = document.getElementById('authorFilterInput');
    if (authorInput) {
        // Isi nilai dari URL jika ada
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('author')) {
            authorInput.value = urlParams.get('author');
        }

        authorInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                updateUrlFilter('author', this.value.trim());
            }
        });
    }

    // 4. Tombol Reset All Filters
    const btnReset = document.getElementById('btnResetFilter');
    if (btnReset) {
        // Munculkan tombol reset jika ada query di URL
        if (window.location.search.length > 1) {
            btnReset.classList.remove('d-none');
        }
        btnReset.addEventListener('click', function() {
            window.location.href = "{{ route('search.results') }}";
        });
    }

    // 5. Tandai Filter Aktif di Sidebar
    const currentParams = new URLSearchParams(window.location.search);
    document.querySelectorAll('.filter-item').forEach(item => {
        const filterType = item.getAttribute('data-filter');
        const filterVal = item.getAttribute('data-value');
        if (currentParams.get(filterType) === filterVal) {
            item.classList.add('active');
        }
    });
</script>

@include('partials.footer')
</body>
</html>