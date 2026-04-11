<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile {{ $author->name }} - SustainDex</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* ================= RESPONSIVE (MOBILE & TABLET) ================= */
        @media (max-width: 768px) {
            .academic-title { font-size: 1.4rem; }
            .academic-header .container { flex-direction: column; text-align: center; gap: 10px; }
            .academic-nav { display: flex !important; justify-content: center; gap: 15px; width: 100%; margin: 0; padding: 0; }
            .academic-nav a { margin: 0; font-size: 0.9rem; }
            .academic-footer .text-md-end { text-align: left !important; margin-top: 20px; }
            .academic-footer .btn { width: auto; display: inline-block; }
            .profile-header { padding: 40px 0; }
        }
        

        /* --- HEADER & FOOTER SUSTAINDEX --- */
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; }
        .academic-header { background-color: #003366; color: white; padding: 15px 0; border-bottom: 4px solid #cc0000; }
        .academic-header a { color: white; text-decoration: none; }
        .academic-title { font-family: 'Georgia', serif; font-size: 1.8rem; font-weight: normal; margin: 0; }
        .academic-nav a { font-size: 0.95rem; font-weight: bold; margin-left: 25px; color: #e0e0e0; padding-bottom: 5px; border-bottom: 2px solid transparent; transition: 0.2s; }
        .academic-nav a:hover, .academic-nav a.active { color: white; border-bottom: 2px solid #cc0000; }
        
        .academic-footer { background-color: #f1f3f5; color: #444; border-top: 1px solid #d5d5d5; padding: 40px 0 20px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin-top: 60px; }
        .academic-footer a { color: #003366; text-decoration: none; font-weight: 500; }
        .academic-footer a:hover { text-decoration: underline; }
        .footer-logo { font-family: 'Georgia', serif; font-size: 1.4rem; font-weight: bold; color: #003366; }

        /* --- PROFILE STYLES --- */
        .profile-header { background: white; padding: 50px 0; border-bottom: 1px solid #e0e0e0; box-shadow: 0 4px 6px rgba(0,0,0,0.02); margin-bottom: 40px; }
        .profile-name { font-family: 'Georgia', serif; font-size: 2.5rem; color: #003366; font-weight: normal; }
        .institution-tag { color: #555; font-size: 1.1rem; margin-top: 10px; }
        
        .doc-card { background: white; border: 1px solid #e0e0e0; padding: 20px; border-radius: 6px; margin-bottom: 15px; transition: 0.2s; }
        .doc-card:hover { box-shadow: 0 4px 10px rgba(0,0,0,0.05); transform: translateY(-2px); border-color: #c6dafc; }
        .doc-title { font-size: 1.25rem; font-weight: 600; color: #1a0dab; text-decoration: none; }
        .doc-title:hover { text-decoration: underline; }

        /* DESAIN BACKGROUND HEADER BARU */
    .profile-header { 
        /* Menggunakan gradien biru tua khas SustainDex agar tidak flat */
        background: linear-gradient(135deg, #003366 0%, #00152b 100%); 
        color: #ffffff; /* Semua teks jadi putih */
        padding: 70px 0; 
        border-bottom: 5px solid #cc0000; /* Aksen merah tetap dipertahankan */
        margin-bottom: 40px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .profile-name { 
        font-family: 'Georgia', serif; 
        font-size: 2.8rem; 
        color: #ffffff; /* Wajib putih */
        font-weight: normal; 
        margin-bottom: 10px;
        text-shadow: 1px 1px 3px rgba(0,0,0,0.3); /* Sedikit bayangan biar teksnya "nongol" */
    }
    .institution-tag { 
        color: #d1e0f0; /* Abu-abu kebiruan terang, bukan putih mati */
        font-size: 1.15rem; 
        margin-bottom: 15px;
    }
    .country-badge {
        background-color: rgba(255, 255, 255, 0.15); /* Efek kaca transparan (Glassmorphism) */
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 8px 18px;
        border-radius: 25px;
        font-weight: 500;
        letter-spacing: 0.5px;
    }
    </style>
</head>
<body>

<header class="academic-header shadow-sm">
    <div class="container d-flex justify-content-between align-items-center">
        <h1 class="academic-title"><a href="/">📚 SustainDex</a></h1>
        <div class="academic-nav">
            <a href="/submit">Submit Document</a>
        </div>
    </div>
</header>

<div class="profile-header text-center">
    <div class="container">
        <h1 class="profile-name">{{ $author->name }}</h1>
        <p class="institution-tag">
            <span style="opacity: 0.8;">🏢</span> 
            @if($author->institution)
                <a href="/institution/{{ $author->institution->id }}" style="color: inherit; text-decoration: none;" class="hover-underline">
                    {{ $author->institution->name }}
                </a>
            @else
                Independent Researcher
            @endif
        </p>
        @if($author->country)
            <span class="badge country-badge">
                📍 {{ $author->country }}
            </span>
        @endif
        @if($author->email)
            <div class="mt-3">
                <span class="text-light" style="background: rgba(0,0,0,0.15); border-radius: 20px; padding: 6px 18px; font-size: 1rem;">
                    <i class="bi bi-envelope"></i> {{ $author->email }}
                </span>
            </div>
        @endif
    </div>
</div>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-md-9">
            
            <h4 class="mb-4 fw-bold" style="color: #003366; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px;">
                Publications <span id="totalDocs" class="text-secondary fw-normal fs-5">...</span>
            </h4>

            <div id="resultsContainer" class="min-vh-50">
                <div class="text-center my-5">
                    <span class="spinner-border text-primary"></span>
                    <p class="text-muted mt-2">Loading publications...</p>
                </div>
            </div>

            <nav aria-label="Author publications pagination" class="mt-5">
                <ul id="paginationContainer" class="pagination justify-content-center"></ul>
            </nav>

        </div>
    </div>
</div>

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

<script>
    const authorId = {{ $author->id }};
    const resultsContainer = document.getElementById('resultsContainer');
    const paginationContainer = document.getElementById('paginationContainer');

    function fetchDocuments(page = 1) {
        // Tampilkan loading state
        resultsContainer.innerHTML = '<div class="text-center my-5"><span class="spinner-border text-primary"></span><p class="text-muted mt-2">Loading publications...</p></div>';
        paginationContainer.innerHTML = '';

        // Tembak API Controller
        fetch(`/author/${authorId}?page=${page}`, {
            headers: { 
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest' // Pastikan Laravel tau ini request AJAX
            }
        })
        .then(response => response.json())
        .then(paginator => {
            resultsContainer.innerHTML = ''; 
            const data = paginator.data; 
            
            // Update total dokumen
            document.getElementById('totalDocs').innerText = `(${paginator.total})`;

            if (data.length === 0) {
                resultsContainer.innerHTML = `
                    <div class="alert bg-white border text-center py-5 shadow-sm">
                        <h5 class="text-muted mb-0">No verified publications found for this author.</h5>
                    </div>`;
                return;
            }

            // Render ulang kartu jurnal
            data.forEach(doc => {
                const abstract = doc.abstract ? doc.abstract.substring(0, 250) + '...' : '';
                const card = `
                    <div class="doc-card">
                        <span class="badge bg-light text-dark border mb-2">${doc.document_type || 'Journal Article'}</span>
                        <span class="text-muted small ms-2">${doc.pub_year || 'N/A'}</span>
                        
                        <div>
                            <a href="/document/${doc.document_number}" class="doc-title">${doc.title}</a>
                        </div>
                        
                        <p class="text-muted small mt-2 mb-0" style="line-height: 1.6;">${abstract}</p>
                    </div>
                `;
                resultsContainer.insertAdjacentHTML('beforeend', card);
            });

            // Jalankan fungsi pagination
            renderPagination(paginator);
        })
        .catch(error => {
            resultsContainer.innerHTML = '<div class="alert alert-danger">Error connecting to server. Please try again.</div>';
        });
    }

    // Fungsi menggambar tombol navigasi
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

        // Pasang event listener ke tombol
        document.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                let parentLi = this.parentElement;
                if (parentLi.classList.contains('disabled') || parentLi.classList.contains('active')) return;
                
                fetchDocuments(this.getAttribute('data-page'));
                window.scrollTo({ top: 0, behavior: 'smooth' }); // Scroll ke atas otomatis
            });
        });
    }

    // Panggil saat halaman pertama dibuka
    document.addEventListener("DOMContentLoaded", function() {
        fetchDocuments();
    });
</script>

</body>
</html>