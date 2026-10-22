<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $institution->name }} - SustaIndex Affiliation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="icon" type="image/png" href="{{ asset('logo/1_Main_Sustaindex_potrait.png') }}">
    <style>
        /* ================= RESPONSIVE & GLOBAL ================= */
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* 🔥 FIX: HEADER SUSTAINDEX KONSISTEN */
        .academic-header { background-color: #003366 !important; padding: 15px 0; border-bottom: 4px solid #cc0000 !important; }
        .academic-header a, .academic-header .dropdown-toggle { color: white !important; font-weight: 500; }
        .academic-header .dropdown-menu a { color: #212529 !important; } /* Teks dropdown tetap gelap */
        .academic-header .dropdown-menu a i { color: inherit; } 
        
        .academic-title { font-family: 'Georgia', serif; font-size: 1.8rem; margin: 0; }
        .academic-nav a { font-size: 0.95rem; font-weight: bold; margin-left: 25px; color: #e0e0e0; border-bottom: 2px solid transparent; transition: 0.2s; }
        .academic-nav a:hover { color: white; border-bottom: 2px solid #cc0000; }
        
        .academic-footer { background-color: #f1f3f5; color: #444; border-top: 1px solid #d5d5d5; padding: 40px 0 20px 0; margin-top: 60px; }
        .academic-footer a { color: #003366; text-decoration: none; font-weight: 500; }
        
        /* INSTITUTION PROFILE HEADER */
        .profile-header { 
            background: linear-gradient(135deg, #003366 0%, #00152b 100%); 
            color: #ffffff; padding: 60px 0; border-bottom: 5px solid #cc0000; margin-bottom: 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .profile-name { font-family: 'Georgia', serif; font-size: 2.8rem; text-shadow: 1px 1px 3px rgba(0,0,0,0.3); }
        .stat-badge { background-color: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.3); padding: 8px 18px; border-radius: 25px; font-weight: 500; }

        /* MAP SECTION */
        #map { height: 350px; width: 100%; border-bottom: 1px solid #ddd; z-index: 1; }

        /* AUTHOR CARD MEWAH */
        .author-card {
            background: white; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px 20px 15px 20px;
            transition: 0.2s; text-align: center; height: 100%; display: flex; flex-direction: column;
        }
        .author-card:hover { transform: translateY(-5px); box-shadow: 0 8px 15px rgba(0,0,0,0.05); border-color: #003366; }
        .author-avatar {
            width: 60px; height: 60px; background: linear-gradient(135deg, #003366, #00152b); color: white; font-size: 1.5rem;
            display: flex; align-items: center; justify-content: center; border-radius: 50%; margin: 0 auto 15px auto; font-family: 'Georgia', serif;
        }
        .author-name { font-weight: 600; color: #1a0dab; text-decoration: none; display: block; }
        
        /* Mobile specific fixes */
        @media (max-width: 768px) {
            .profile-name { font-size: 2rem; }
            .stat-badge { font-size: 0.85rem; padding: 6px 12px; }
            .academic-header .container { flex-direction: column; text-align: center; gap: 10px; }
            .academic-nav { display: flex !important; justify-content: center; gap: 15px; width: 100%; margin: 0; padding: 0; }
            .academic-nav a { margin: 0; font-size: 0.9rem; }
        }
    </style>
</head>
<body>

@include('partials.header')

<div class="profile-header text-center">
    <div class="container">
        <h1 class="profile-name mb-4">{{ $institution->name }}</h1>
        <p class="mb-3" style="color: #d1e0f0; font-size: 1.15rem;">
            <i class="bi bi-geo-alt-fill me-1"></i> 
            🌐 {{ $institution->country ?? 'International' }}
        </p>
        <div class="d-flex justify-content-center gap-3 flex-wrap mt-4">
            <span class="stat-badge"><i class="bi bi-people-fill me-1"></i> <span id="totalAuthors">...</span> Researchers</span>
            <span class="stat-badge"><i class="bi bi-file-earmark-text me-1"></i> {{ $totalDocuments }} Publications</span>
        </div>
    </div>
</div>


@if($institution->lat && $institution->lng)
    <div id="map" style="height: 400px; width: 100%;"></div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
<script>
    document.addEventListener("DOMContentLoaded", function () {
        setTimeout(function () {
            const lat = parseFloat(
                "{{ $institution->lat }}".replace(',', '.')
            );

            const lng = parseFloat(
                "{{ $institution->lng }}".replace(',', '.')
            );

            if (!isNaN(lat) && !isNaN(lng)) {
                console.log("Memuat peta di koordinat:", lat, lng);

                const map = L.map('map').setView([lat, lng], 15);

                // Basemap OpenStreetMap - gratis
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution:
                        '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors',
                    maxZoom: 19
                }).addTo(map);

                // Marker lokasi institusi
                L.marker([lat, lng])
                    .addTo(map)
                    .bindPopup("<b>{{ addslashes($institution->name) }}</b>")
                    .openPopup();

            } else {
                document.getElementById('map').innerHTML =
                    "<h3 style='color:red; text-align:center; margin-top:150px;'>Error: Koordinat bukan angka!</h3>";
            }
        }, 500);
    });
</script>

@else
    <div class="bg-light border-bottom">
        <div class="container py-3 text-center text-muted small">
            <span style="opacity: 0.7;">📍</span> Map is not displayed because coordinates are not available.
        </div>
    </div>
@endif

<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col-12">
            <h4 class="mb-4 fw-bold" style="color: #003366; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px;">
                <i class="bi bi-person-lines-fill text-primary me-2"></i> Affiliated Researchers
            </h4>

            <div id="resultsContainer" class="row g-4 min-vh-50">
                <div class="col-12 text-center my-5">
                    <span class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></span>
                    <p class="text-muted mt-3">Loading researchers data...</p>
                </div>
            </div>

            <nav class="mt-5">
                <ul id="paginationContainer" class="pagination justify-content-center"></ul>
            </nav>
        </div>
    </div>
</div>

@include('partials.footer')

<script>
    const institutionId = {{ $institution->id }};
    const resultsContainer = document.getElementById('resultsContainer');
    const paginationContainer = document.getElementById('paginationContainer');

    function fetchAuthors(page = 1) {
        resultsContainer.innerHTML = '<div class="col-12 text-center my-5"><span class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></span><p class="text-muted mt-3">Loading researchers data...</p></div>';
        
        fetch(`/institution/${institutionId}?page=${page}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(paginator => {
            resultsContainer.innerHTML = ''; 
            document.getElementById('totalAuthors').innerText = paginator.total;

            if (paginator.data.length === 0) {
                resultsContainer.innerHTML = `
                    <div class="col-12">
                        <div class="alert bg-white border text-center py-5 shadow-sm">
                            <h5 class="text-muted mb-0">No authors registered under this institution yet.</h5>
                        </div>
                    </div>`;
                return;
            }

            paginator.data.forEach(author => {
                const initial = author.name.charAt(0).toUpperCase();
                const card = `
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="author-card">
                            <div class="author-avatar shadow-sm">${initial}</div>
                            <a href="/author/${author.id}" class="author-name hover-underline mb-2" style="font-size: 0.95rem; line-height: 1.3; min-height: 40px; display: flex; align-items: center; justify-content: center;">
                                ${author.name}
                            </a>
                            <div class="mt-auto border-top pt-3 w-100">
                                <span class="badge bg-light text-dark border w-100 py-2" style="font-size: 0.75rem;">
                                    <i class="bi bi-file-earmark-text text-primary me-1"></i> ${author.documents_count} Publications
                                </span>
                            </div>
                        </div>
                    </div>`;
                resultsContainer.insertAdjacentHTML('beforeend', card);
            });
            renderPagination(paginator);
        })
        .catch(error => {
            resultsContainer.innerHTML = '<div class="col-12 alert alert-danger">Error connecting to server. Please try again.</div>';
        });
    }

    function renderPagination(paginator) {
        if (paginator.last_page <= 1) return;
        let html = '';
        const current = paginator.current_page;
        html += `<li class="page-item ${current === 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${current-1}">&laquo; Prev</a></li>`;
        
        let startPage = Math.max(1, current - 2);
        let endPage = Math.min(paginator.last_page, current + 2);
        
        for (let i = startPage; i <= endPage; i++) {
            html += `<li class="page-item ${i === current ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
        }
        
        html += `<li class="page-item ${current === paginator.last_page ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${current+1}">Next &raquo;</a></li>`;
        paginationContainer.innerHTML = html;

        document.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                let parentLi = this.parentElement;
                if (parentLi.classList.contains('disabled') || parentLi.classList.contains('active')) return;
                
                fetchAuthors(this.getAttribute('data-page'));
                window.scrollTo({ top: 400, behavior: 'smooth' }); // Scroll otomatis ke tabel
            });
        });
    }

    document.addEventListener("DOMContentLoaded", fetchAuthors);
</script>
</body>
</html>