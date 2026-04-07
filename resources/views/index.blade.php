<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SustainDex - Academic Indexing System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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
        
        <div class="academic-nav d-none d-md-block">
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
                        <li>{{ $type->document_type ?: 'Unknown' }} <span class="filter-count">{{ $type->total }}</span></li>
                    @endforeach
                    @if($docTypes->isEmpty())
                        <li class="text-muted">No data available</li>
                    @endif
                </ul>
            </div>

            <div class="filter-box">
                <div class="filter-header">Publication Year</div>
                <ul class="filter-list">
                    @foreach($pubYears as $year)
                        <li>{{ $year->pub_year ?: 'N/A' }} <span class="filter-count">{{ $year->total }}</span></li>
                    @endforeach
                    @if($pubYears->isEmpty())
                        <li class="text-muted">No data available</li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="col-md-9">
            
            <div class="input-group input-group-lg mb-4 search-box">
                <input type="text" id="searchInput" class="form-control" placeholder="Search by title, author, abstract, or number..." autocomplete="off">
                <button class="btn btn-primary px-4" type="button">Search</button>
            </div>

            <p class="text-muted small" id="resultCount">Menampilkan dokumen terbaru...</p>

            <div id="resultsContainer">
                </div>

        </div>
    </div>
</div>

<script>
    const searchInput = document.getElementById('searchInput');
    const resultsContainer = document.getElementById('resultsContainer');
    const resultCount = document.getElementById('resultCount');
    let timeoutId;

    // Fungsi untuk memuat data (Bisa kosong untuk load awal, atau ada isi saat ngetik)
    function fetchResults(query = '') {
        resultsContainer.innerHTML = '<div class="text-center my-5"><span class="spinner-border text-primary"></span><p class="text-muted mt-2">Searching database...</p></div>';

        fetch(`/search?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                resultsContainer.innerHTML = ''; 
                resultCount.innerText = query ? `Menampilkan ${data.length} hasil untuk "${query}"` : `Menampilkan ${data.length} dokumen terbaru`;

                if (data.length === 0) {
                    resultsContainer.innerHTML = '<div class="alert alert-light border text-center py-5"><b>No results found.</b><br>Try adjusting your search terms or filters.</div>';
                    return;
                }

                // Looping hasil
                data.forEach(item => {
                    // Gabungkan array nama author jadi string pakai koma
                    let authorsArray = item.authors;
                    if(typeof authorsArray === 'string') {
                        authorsArray = JSON.parse(authorsArray); // Parse kalau formatnya string JSON
                    }
                    const authorText = Array.isArray(authorsArray) ? authorsArray.join('; ') : 'Unknown Author';

                    // Potong abstrak
                    const shortAbstract = item.abstract.length > 300 ? item.abstract.substring(0, 300) + '...' : item.abstract;
                    
                    // Cek DOI
                    const doiHtml = item.doi ? `| <a href="${item.doi}" target="_blank" class="text-success text-decoration-none">Direct Link (DOI)</a>` : '';

                    // Bikin HTML Card (Bentuk list jurnal asli)
                    const card = `
                        <div class="result-card">
                            <a href="/document/${item.document_number}" class="doc-title">${item.title}</a>
                            <div class="doc-authors mt-1">${authorText}</div>
                            <div class="doc-abstract">${shortAbstract}</div>
                            <div class="doc-meta">
                                <span class="badge badge-type rounded-pill px-2 py-1">${item.document_type || 'Journal'}</span>
                                <span class="ms-2">Pub Year: ${item.pub_year || 'N/A'}</span>
                                <span class="ms-2">| ID: ${item.document_number}</span>
                                <span class="ms-2">${doiHtml}</span>
                            </div>
                        </div>
                    `;
                    resultsContainer.insertAdjacentHTML('beforeend', card);
                });
            })
            .catch(error => {
                resultsContainer.innerHTML = '<div class="text-danger">Terjadi kesalahan saat memuat data.</div>';
            });
    }

    // Load data otomatis saat halaman pertama dibuka
    // Tangkap kata kunci dari URL (jika ada dari halaman depan)
    const urlParams = new URLSearchParams(window.location.search);
    const initialQuery = urlParams.get('q') || '';
    
    // Masukkan kata kunci ke dalam input form di atas supaya user tahu dia lagi nyari apa
    searchInput.value = initialQuery;

    // Load data otomatis berdasarkan kata kunci dari halaman depan
    fetchResults(initialQuery);

    // Load data saat ngetik (Debouncing)
    searchInput.addEventListener('input', function() {
        clearTimeout(timeoutId);
        const query = this.value;
        timeoutId = setTimeout(() => {
            fetchResults(query);
        }, 400); // Tunggu 400ms setelah selesai ngetik
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
            &copy; 2026 SustainDex Indexing System. All rights reserved.
        </div>
    </div>
</footer>
</html>