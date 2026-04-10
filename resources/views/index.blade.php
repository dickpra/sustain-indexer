<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SustainDex - Academic Indexing System</title>
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
            /* Mengecilkan jarak kotak putih utama */
            .main-container { padding: 20px 15px; margin-top: 20px; margin-bottom: 30px; }
            
            /* Mengecilkan judul agar tidak pecah */
            .academic-title { font-size: 1.4rem; }
            .section-title { font-size: 1.1em; margin-top: 20px; }
            
            /* Memperbaiki menu Header di HP agar turun ke bawah logo */
            .academic-header .container { flex-direction: column; text-align: center; gap: 10px; }
            .academic-nav { display: flex !important; justify-content: center; gap: 15px; width: 100%; margin: 0; padding: 0; }
            .academic-nav a { margin: 0; font-size: 0.9rem; }

            /* Membuat tombol-tombol utama jadi Full-Width (selebar layar) di HP */
            .btn-academic, .btn-secondary-academic { width: 100%; margin-bottom: 10px; display: block; }
            
            /* Khusus tombol Step 2 (Edit & Submit) agar bertumpuk */
            .d-flex.justify-content-between.mt-5 { flex-direction: column-reverse; gap: 10px; }
            .d-flex.justify-content-between.mt-5 button { width: 100%; }

            /* Menyesuaikan Footer */
            .academic-footer .text-md-end { text-align: left !important; margin-top: 20px; }
            .academic-footer .btn { width: auto; display: inline-block; }
        }
        /* --- HEADER & FOOTER SUSTAINDEX --- */
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
<header class="academic-header shadow-sm">
    <div class="container d-flex justify-content-between align-items-center">
        <h1 class="academic-title"><a href="/">📚 SustainDex</a></h1>
        
        <div class="academic-nav">
            {{-- <a href="/">Search</a> --}}
            <a href="/submit">Submit Document</a>
        </div>
    </div>
</header>
<body>

{{-- <nav class="navbar navbar-dark bg-dark py-3 shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/">📚 SustainDex</a>
        <a href="/submit" class="btn btn-outline-light btn-sm fw-bold">+ Submit Document</a>
    </div>
</nav> --}}

<div class="container mt-5 mb-5">
    <div class="row">
        
        <div class="col-md-3">
            <h5 class="mb-3 fw-bold text-secondary">Filter Results</h5>
            
            <div class="filter-box">
                <div class="filter-header">Publication Type</div>
                <ul class="filter-list">
                    @foreach($docTypes as $type)
                        <li class="filter-item" data-filter="type" data-value="{{ $type->document_type }}">
                            {{ $type->document_type ?: 'Unknown' }} <span class="filter-count">{{ $type->total }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="filter-box">
                <div class="filter-header">Publication Year</div>
                <ul class="filter-list">
                    @foreach($pubYears as $year)
                        <li class="filter-item" data-filter="year" data-value="{{ $year->pub_year }}">
                            {{ $year->pub_year ?: 'N/A' }} <span class="filter-count">{{ $year->total }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            
            <button id="btnResetFilter" class="btn btn-sm btn-outline-danger w-100 mt-2 d-none">Reset Filters</button>
        </div>

        <div class="col-md-9">
            
            <div class="input-group input-group-lg mb-4 search-box">
                <input type="text" id="searchInput" class="form-control" placeholder="Search by title, author, abstract, or number..." autocomplete="off">
                <button class="btn btn-primary px-4" type="button">Search</button>
            </div>

            <p class="text-muted small" id="resultCount">Showing latest documents...</p>

            <div id="resultsContainer">
                </div>
            <nav aria-label="Search results pages" class="mt-4">
                <ul id="paginationContainer" class="pagination justify-content-center">
                    </ul>
            </nav>

        </div>
    </div>
</div>

<script>
    const searchInput = document.getElementById('searchInput');
    const resultsContainer = document.getElementById('resultsContainer');
    const resultCount = document.getElementById('resultCount');
    const btnResetFilter = document.getElementById('btnResetFilter');
    let timeoutId;
    
    // Simpan filter yang sedang aktif
    let activeFilters = { type: '', year: '' };

    const paginationContainer = document.getElementById('paginationContainer');

    // Tambahkan parameter `page` dengan default 1
    function fetchResults(query = '', page = 1) {
        resultsContainer.innerHTML = '<div class="text-center my-5"><span class="spinner-border text-primary"></span><p class="text-muted mt-2">Searching database...</p></div>';
        paginationContainer.innerHTML = ''; // Kosongkan tombol paginasi saat loading

        // Masukkan parameter page ke dalam URL
        let url = `/search?q=${encodeURIComponent(query)}&page=${page}`;
        if (activeFilters.type) url += `&type=${encodeURIComponent(activeFilters.type)}`;
        if (activeFilters.year) url += `&year=${encodeURIComponent(activeFilters.year)}`;

        fetch(url)
            .then(response => response.json())
            .then(paginator => {
                resultsContainer.innerHTML = ''; 
                
                // Karena pakai paginate(), datanya sekarang ada di dalam paginator.data
                const data = paginator.data; 
                
                let filterText = '';
                if(activeFilters.type) filterText += ` | Type: ${activeFilters.type}`;
                if(activeFilters.year) filterText += ` | Year: ${activeFilters.year}`;
                
                // Ubah teks jumlah hasil pakai paginator.total
                resultCount.innerText = query 
                    ? `Showing ${data.length} of ${paginator.total} results for "${query}" ${filterText}`
                    : `Showing ${data.length} of ${paginator.total} documents ${filterText}`;

                if (data.length === 0) {
                    resultsContainer.innerHTML = '<div class="alert alert-light border text-center py-5"><b>No results found.</b><br>Try adjusting your search terms or filters.</div>';
                    return;
                }

                // Render Card Jurnal
                data.forEach(item => {
                    let authorsArray = item.authors;
                    if(typeof authorsArray === 'string') authorsArray = JSON.parse(authorsArray);
                    const authorText = Array.isArray(authorsArray) ? authorsArray.join('; ') : 'Unknown Author';
                    const shortAbstract = item.abstract.length > 300 ? item.abstract.substring(0, 300) + '...' : item.abstract;
                    const doiHtml = item.doi ? `| <a href="${item.doi}" target="_blank" class="text-success text-decoration-none fw-bold">DOI</a>` : '';

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
                            </div>
                        </div>
                    `;
                    resultsContainer.insertAdjacentHTML('beforeend', card);
                });

                // Panggil fungsi untuk menggambar tombol Paginasi
                renderPagination(paginator);
            })
            .catch(error => {
                resultsContainer.innerHTML = '<div class="text-danger">An error occurred while loading data.</div>';
            });
    }

    // Fungsi Menggambar Tombol Paginasi
    function renderPagination(paginator) {
        if (paginator.last_page <= 1) return; // Kalau cuma 1 halaman, gak usah tampilkan tombol

        let html = '';
        const currentPage = paginator.current_page;
        const lastPage = paginator.last_page;

        // Tombol Previous
        html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currentPage - 1}">&laquo; Prev</a>
                 </li>`;

        // Logika sederhana untuk menampilkan tombol angka (batas maksimal 5 tombol)
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(lastPage, currentPage + 2);

        for (let i = startPage; i <= endPage; i++) {
            html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                     </li>`;
        }

        // Tombol Next
        html += `<li class="page-item ${currentPage === lastPage ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currentPage + 1}">Next &raquo;</a>
                 </li>`;

        paginationContainer.innerHTML = html;

        // Pasang Event Listener saat tombol diklik
        document.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                let parentLi = this.parentElement;
                
                // Kalau tombol di-disable atau sedang aktif, abaikan kliknya
                if (parentLi.classList.contains('disabled') || parentLi.classList.contains('active')) return;
                
                let targetPage = this.getAttribute('data-page');
                
                // Panggil ulang data dengan halaman yang baru, dan scroll ke atas otomatis!
                fetchResults(searchInput.value, targetPage);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
    }

    // --- LOGIKA KLIK FILTER ---
    document.querySelectorAll('.filter-item').forEach(item => {
        item.addEventListener('click', function() {
            const filterType = this.getAttribute('data-filter'); // 'type' atau 'year'
            const filterValue = this.getAttribute('data-value');

            // Kalau diklik dua kali (Toggle Off)
            if (this.classList.contains('active')) {
                this.classList.remove('active');
                activeFilters[filterType] = '';
            } else {
                // Matikan dulu class 'active' di kategori yang sama
                document.querySelectorAll(`.filter-item[data-filter="${filterType}"]`).forEach(el => el.classList.remove('active'));
                
                // Aktifkan yang baru diklik
                this.classList.add('active');
                activeFilters[filterType] = filterValue;
            }

            // Tampilkan/Sembunyikan tombol Reset
            if (activeFilters.type || activeFilters.year) {
                btnResetFilter.classList.remove('d-none');
            } else {
                btnResetFilter.classList.add('d-none');
            }

            // Panggil data ulang
            fetchResults(searchInput.value);
        });
    });

    // --- TOMBOL RESET ---
    btnResetFilter.addEventListener('click', function() {
        document.querySelectorAll('.filter-item').forEach(el => el.classList.remove('active'));
        activeFilters = { type: '', year: '' };
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
</script>

</body>
<footer class="academic-footer">
    <div class="container">
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="footer-logo mb-2">📚 SustainDex</div>
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
            &copy; {{ date('Y') }} SustainDex Indexing System. All rights reserved.
        </div>
    </div>
</footer>
</html>