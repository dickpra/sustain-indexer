<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SustaIndex - Academic Indexing System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
    </style>
</head>
<body>

@include('partials.header')

<div class="container mt-5 mb-5">
    <div class="row">
        
        <div class="col-md-3" id="filterSidebar">
            <h5 class="mb-3 fw-bold text-secondary">Filter Results</h5>
            
            <div class="filter-box">
                <div class="filter-header">Author / Contributor</div>
                <div class="p-3">
                    <input type="text" id="authorFilterInput" class="form-control form-control-sm" placeholder="e.g. John Doe">
                </div>
            </div>

            <div class="filter-box">
                <div class="filter-header">Publication Type</div>
                <ul class="filter-list">
                    @foreach($docTypes as $type)
                        <li class="filter-item type-item {{ $loop->iteration > 5 ? 'd-none extra-type' : '' }}" data-filter="type" data-value="{{ $type->document_type }}">
                            {{ $type->document_type ?: 'Unknown' }} 
                            <span class="filter-count type-count">{{ $type->total }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="filter-box">
                <div class="filter-header">Publication Year</div>
                <ul class="filter-list">
                    <li class="filter-item year-item" data-filter="year" data-value="exact_{{ $yearStats['current_year'] }}">
                        In {{ $yearStats['current_year'] }} <span class="filter-count" id="count_current">{{ $yearStats['count_current'] }}</span>
                    </li>
                    <li class="filter-item year-item" data-filter="year" data-value="since_{{ $yearStats['last_year'] }}">
                        Since {{ $yearStats['last_year'] }} <span class="filter-count" id="count_last">{{ $yearStats['count_last'] }}</span>
                    </li>
                    <li class="filter-item year-item" data-filter="year" data-value="since_{{ $yearStats['year_5'] }}">
                        Since {{ $yearStats['year_5'] }} (last 5 years) <span class="filter-count" id="count_5">{{ $yearStats['count_5'] }}</span>
                    </li>
                    <li class="filter-item year-item" data-filter="year" data-value="since_{{ $yearStats['year_10'] }}">
                        Since {{ $yearStats['year_10'] }} (last 10 years) <span class="filter-count" id="count_10">{{ $yearStats['count_10'] }}</span>
                    </li>
                    <li class="filter-item year-item" data-filter="year" data-value="since_{{ $yearStats['year_20'] }}">
                        Since {{ $yearStats['year_20'] }} (last 20 years) <span class="filter-count" id="count_20">{{ $yearStats['count_20'] }}</span>
                    </li>
                </ul>
            </div>

            <button id="btnResetFilter" class="btn btn-sm btn-outline-danger w-100 mt-2 d-none">Reset Filters</button>
        </div>

        <div class="col-md-9" id="mainContentColumn">
            
            <div class="input-group input-group-lg mb-4 search-box">
                <input type="text" id="searchInput" class="form-control" placeholder="Search by title, abstract, or number..." autocomplete="off">
                <button class="btn btn-primary px-4" type="button" onclick="fetchResults(document.getElementById('searchInput').value)">Search</button>
            </div>

            <p class="text-muted small" id="resultCount">Showing latest documents...</p>

            <div id="emptyState" class="d-none mt-5 text-center">
                <div class="alert bg-white border py-5 shadow-sm rounded-4">
                    <h3 class="fw-bold" style="color: #003366;">🔍 No Results Found</h3>
                    <p class="text-muted">We couldn't find any documents matching your exact criteria.</p>
                    
                    <div class="card mt-4 mx-auto text-start border-0 bg-light" style="max-width: 500px; border-left: 4px solid #cc0000 !important;">
                        <div class="card-body">
                            <h6 class="fw-bold">💡 Search Tips:</h6>
                            <ul class="small text-muted mb-0" style="line-height: 1.8;">
                                <li>Check your spelling for any typos.</li>
                                <li>Try using broader or fewer keywords.</li>
                                <li><strong>Clear active filters</strong> (Year, Type, or Author) to widen the search.</li>
                            </ul>
                        </div>
                    </div>
                    <button class="btn btn-outline-danger mt-4 px-4 fw-bold" onclick="document.getElementById('btnResetFilter').click()">Reset All Filters</button>
                </div>
            </div>

            <div id="resultsContainer"></div>

            <nav aria-label="Search results pages" class="mt-4">
                <ul id="paginationContainer" class="pagination justify-content-center"></ul>
            </nav>

        </div>
    </div>
</div>

<script>
    const searchInput = document.getElementById('searchInput');
    const authorFilterInput = document.getElementById('authorFilterInput'); // Input Author
    const resultsContainer = document.getElementById('resultsContainer');
    const resultCount = document.getElementById('resultCount');
    const btnResetFilter = document.getElementById('btnResetFilter');
    
    // Variabel Layout Cerdas
    const filterSidebar = document.getElementById('filterSidebar');
    const mainContentColumn = document.getElementById('mainContentColumn');
    const emptyState = document.getElementById('emptyState');

    let timeoutId;
    let authorTimeoutId;
    
    // Objek penyimpan semua filter (Ditambah author)
    let activeFilters = { type: '', year: '', author: '' };
    const paginationContainer = document.getElementById('paginationContainer');

    function fetchResults(query = '', page = 1) {
        resultsContainer.innerHTML = '<div class="text-center my-5"><span class="spinner-border text-primary"></span><p class="text-muted mt-2">Searching database...</p></div>';
        paginationContainer.innerHTML = ''; 
        emptyState.classList.add('d-none'); // Sembunyikan empty state saat loading

        // Rangkai URL beserta semua filter
        let url = `/search?q=${encodeURIComponent(query)}&page=${page}`;
        if (activeFilters.type) url += `&type=${encodeURIComponent(activeFilters.type)}`;
        if (activeFilters.year) url += `&year=${encodeURIComponent(activeFilters.year)}`;
        if (activeFilters.author) url += `&author=${encodeURIComponent(activeFilters.author)}`;

        fetch(url)
            .then(response => response.json())
            .then(paginator => {
                resultsContainer.innerHTML = ''; 
                const data = paginator.data; 

                // ==============================================
                // FITUR BARU: UPDATE ANGKA SIDEBAR (FACETS)
                // ==============================================
                if (paginator.facets) {
                    // 1. Update angka Tipe Jurnal
                    document.querySelectorAll('.type-item').forEach(li => {
                        let typeName = li.getAttribute('data-value');
                        let count = paginator.facets.types[typeName] || 0; // Ambil angka baru, kalau kosong = 0
                        
                        li.querySelector('.type-count').innerText = count;
                        
                        // Sembunyikan filternya kalau angkanya 0 (Biar rapi!)
                        if (count === 0) li.classList.add('d-none');
                        else li.classList.remove('d-none');
                    });

                    // 2. Update angka Tahun
                    const yearKeys = ['count_current', 'count_last', 'count_5', 'count_10', 'count_20'];
                    yearKeys.forEach(key => {
                        let count = paginator.facets.years[key] || 0;
                        let span = document.getElementById(key);
                        
                        if(span) {
                            span.innerText = count;
                            // Sembunyikan filter tahun kalau angkanya 0
                            if (count === 0) span.parentElement.classList.add('d-none');
                            else span.parentElement.classList.remove('d-none');
                        }
                    });
                }
                // ==============================================
                
                let filterText = '';
                if(activeFilters.type) filterText += ` | Type: ${activeFilters.type}`;
                if(activeFilters.year) filterText += ` | Year: ${activeFilters.year}`;
                if(activeFilters.author) filterText += ` | Author: ${activeFilters.author}`;
                
                resultCount.innerText = query 
                    ? `Showing ${data.length} of ${paginator.total} results for "${query}" ${filterText}`
                    : `Showing ${data.length} of ${paginator.total} documents ${filterText}`;

                // ==============================================
                // LOGIKA UI CERDAS: JIKA KOSONG VS ADA DATA
                // ==============================================
                if (data.length === 0) {
                    // Sembunyikan sidebar, perlebar layar, tampilkan Tips
                    filterSidebar.classList.add('d-none');
                    mainContentColumn.classList.replace('col-md-9', 'col-md-12');
                    emptyState.classList.remove('d-none');
                    return; // Stop di sini
                } else {
                    // Tampilkan kembali sidebar, normalkan lebar layar, sembunyikan Tips
                    filterSidebar.classList.remove('d-none');
                    mainContentColumn.classList.replace('col-md-12', 'col-md-9');
                    emptyState.classList.add('d-none');
                }

                // Render Card Jurnal
                data.forEach(item => {
                    // (KODE BARU: LINK PROFIL + MUTED UNKNOWN)
                    let authorText = '<span class="text-muted fst-italic">Unknown Author</span>';
                    if (item.authors && item.authors.length > 0) {
                        authorText = item.authors.map(a => {
                            // Nama institusi bisa diklik jika ada
                            let inst = '';
                            if (a.institution) {
                                inst = ` <a href="/institution/${a.institution.id}" class="text-secondary small text-decoration-none">(${a.institution.name})</a>`;
                            }
                            return `<a href="/author/${a.id}" class="text-decoration-none" style="color: inherit;">${a.name}</a>${inst}`;
                        }).join('; ');
                    }
                    const shortAbstract = item.abstract.length > 300 ? item.abstract.substring(0, 300) + '...' : item.abstract;
                    const doiHtml = item.doi ? `| <a href="${item.doi}" target="_blank" class="text-success text-decoration-none fw-bold">DOI</a>` : '';

                    let keywordsHtml = '';
                    if (item.keywords) {
                        // Pecah string "Violet, Red" jadi array, lalu jadikan tombol link
                        let keywordArray = item.keywords.split(',');
                        keywordsHtml = '<div class="mt-3">' + keywordArray.map(k => {
                            let cleanWord = k.trim(); // Hilangkan spasi berlebih
                            // Tombol link akan otomatis mengarah ke URL pencarian /?q=namakeyword
                        return `<a href="/results?q=${encodeURIComponent(cleanWord)}" class="badge bg-light text-secondary border text-decoration-none me-2 mb-1 hover-keyword" style="transition: 0.2s;"># ${cleanWord}</a>`;                        }).join('') + '</div>';
                    }

                    const card = `
                        <div class="result-card">
                            <a href="/document/${item.document_number}" class="doc-title">${item.title}</a>
                            <div class="doc-authors mt-1">${authorText}</div>
                            <div class="doc-abstract">${shortAbstract}</div>
                            <div class="doc-meta">
                                <span class="badge bg-secondary rounded-pill">${item.document_type || 'Journal'}</span>
                                <span class="ms-2">Pub Year: ${item.pub_year || 'N/A'}</span>
                                <span class="ms-2">| ID: ${item.document_number}</span>
                                <span class="ms-2">${doiHtml}</span>
                                <span class="ms-2">${keywordsHtml}</span>
                            </div>
                        </div>
                    `;
                    resultsContainer.insertAdjacentHTML('beforeend', card);
                });

                renderPagination(paginator);
            })
            .catch(error => {
                resultsContainer.innerHTML = '<div class="text-danger">An error occurred while loading data.</div>';
            });
    }

    // Fungsi Menggambar Tombol Paginasi (SAMA PERSIS DENGAN KODEMU)
    function renderPagination(paginator) {
        if (paginator.last_page <= 1) return; 

        let html = '';
        const currentPage = paginator.current_page;
        const lastPage = paginator.last_page;

        html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currentPage - 1}">&laquo; Prev</a>
                 </li>`;

        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(lastPage, currentPage + 2);

        for (let i = startPage; i <= endPage; i++) {
            html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                     </li>`;
        }

        html += `<li class="page-item ${currentPage === lastPage ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currentPage + 1}">Next &raquo;</a>
                 </li>`;

        paginationContainer.innerHTML = html;

        document.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                let parentLi = this.parentElement;
                
                if (parentLi.classList.contains('disabled') || parentLi.classList.contains('active')) return;
                
                let targetPage = this.getAttribute('data-page');
                fetchResults(searchInput.value, targetPage);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
    }

    // --- LOGIKA KETIKAN FILTER AUTHOR (BARU) ---
    authorFilterInput.addEventListener('input', function() {
        clearTimeout(authorTimeoutId);
        activeFilters.author = this.value;
        
        // Munculkan tombol reset kalau author diketik
        if(this.value.length > 0) btnResetFilter.classList.remove('d-none');
        
        authorTimeoutId = setTimeout(() => {
            fetchResults(searchInput.value);
        }, 500); 
    });

    // --- LOGIKA KLIK FILTER TAHUN & TIPE ---
    document.querySelectorAll('.filter-item').forEach(item => {
        item.addEventListener('click', function() {
            const filterType = this.getAttribute('data-filter'); 
            const filterValue = this.getAttribute('data-value');

            if (this.classList.contains('active')) {
                this.classList.remove('active');
                activeFilters[filterType] = '';
            } else {
                document.querySelectorAll(`.filter-item[data-filter="${filterType}"]`).forEach(el => el.classList.remove('active'));
                this.classList.add('active');
                activeFilters[filterType] = filterValue;
            }

            // Tampilkan tombol Reset
            if (activeFilters.type || activeFilters.year || activeFilters.author) {
                btnResetFilter.classList.remove('d-none');
            } else {
                btnResetFilter.classList.add('d-none');
            }

            fetchResults(searchInput.value);
        });
    });

    // --- TOMBOL RESET (DIPERBARUI) ---
    btnResetFilter.addEventListener('click', function() {
        document.querySelectorAll('.filter-item').forEach(el => el.classList.remove('active'));
        authorFilterInput.value = ''; // Kosongkan input author
        activeFilters = { type: '', year: '', author: '' };
        this.classList.add('d-none');
        fetchResults(searchInput.value);
    });

    // --- LOAD AWAL & KETIKAN PENCARIAN ---
    const urlParams = new URLSearchParams(window.location.search);
    const initialQuery = urlParams.get('q') || '';
    searchInput.value = initialQuery;
    fetchResults(initialQuery);

    searchInput.addEventListener('input', function() {
        clearTimeout(timeoutId);
        const query = this.value;
        timeoutId = setTimeout(() => {
            fetchResults(query);
        }, 400); 
    });

    // --- LOGIKA TOMBOL SHOW MORE / SHOW LESS ---
    document.querySelectorAll('.toggle-more').forEach(btn => {
        btn.addEventListener('click', function() {
            const targetClass = this.getAttribute('data-target');
            const hiddenItems = document.querySelectorAll(targetClass);
            let isHidden = hiddenItems[0].classList.contains('d-none');
            
            if (isHidden) {
                hiddenItems.forEach(item => item.classList.remove('d-none'));
                this.innerText = '- Show Less';
            } else {
                hiddenItems.forEach(item => {
                    item.classList.add('d-none');
                    if(item.classList.contains('active')) {
                        item.classList.remove('active');
                        activeFilters[item.getAttribute('data-filter')] = '';
                        btnResetFilter.classList.add('d-none');
                        fetchResults(searchInput.value); 
                    }
                });
                this.innerText = '+ Show More';
            }
        });
    });
</script>

@include('partials.footer')
</body>
</html>