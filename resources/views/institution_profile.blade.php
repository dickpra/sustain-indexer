<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $institution->name }} - SustainDex Affiliation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        /* ================= RESPONSIVE & GLOBAL ================= */
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* HEADER & FOOTER SUSTAINDEX (Sesuai kode sebelumnya) */
        .academic-header { background-color: #003366; color: white; padding: 15px 0; border-bottom: 4px solid #cc0000; }
        .academic-header a { color: white; text-decoration: none; }
        .academic-title { font-family: 'Georgia', serif; font-size: 1.8rem; margin: 0; }
        .academic-nav a { font-size: 0.95rem; font-weight: bold; margin-left: 25px; color: #e0e0e0; border-bottom: 2px solid transparent; transition: 0.2s; }
        .academic-nav a:hover { color: white; border-bottom: 2px solid #cc0000; }
        
        .academic-footer { background-color: #f1f3f5; color: #444; border-top: 1px solid #d5d5d5; padding: 40px 0 20px 0; margin-top: 60px; }
        .academic-footer a { color: #003366; text-decoration: none; font-weight: 500; }
        
        /* INSTITUTION PROFILE HEADER */
        .profile-header { 
            background: linear-gradient(135deg, #003366 0%, #00152b 100%); 
            color: #ffffff; padding: 60px 0; border-bottom: 5px solid #cc0000; margin-bottom: 0;
        }
        .profile-name { font-family: 'Georgia', serif; font-size: 2.5rem; }
        .stat-badge { background-color: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.3); padding: 8px 18px; border-radius: 25px; }

        /* MAP SECTION */
        #map { height: 350px; width: 100%; border-bottom: 1px solid #ddd; z-index: 1; }

        /* AUTHOR CARD */
        .author-card {
            background: white; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px;
            transition: 0.2s; text-align: center; height: 100%;
        }
        .author-card:hover { transform: translateY(-5px); box-shadow: 0 8px 15px rgba(0,0,0,0.05); border-color: #003366; }
        .author-avatar {
            width: 60px; height: 60px; background: #003366; color: white; font-size: 1.5rem;
            display: flex; align-items: center; justify-content: center; border-radius: 50%; margin: 0 auto 15px auto;
        }
        .author-name { font-weight: 600; color: #1a0dab; text-decoration: none; display: block; }
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
        <h1 class="profile-name mb-4">{{ $institution->name }}</h1>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <span class="stat-badge">👥 <span id="totalAuthors">...</span> Researchers</span>
            <span class="stat-badge">📄 {{ $totalDocuments }} Publications</span>
        </div>
    </div>
</div>


@if($institution->lat && $institution->lng)
    <div id="map" style="height: 400px; width: 100%;"></div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Kita kasih delay 0.5 detik biar HTML-nya selesai di-render dulu
            setTimeout(function() {
                const lat = parseFloat("{{ $institution->lat }}".replace(',', '.')); // Otomatis ubah koma jadi titik
                const lng = parseFloat("{{ $institution->lng }}".replace(',', '.'));

                if (!isNaN(lat) && !isNaN(lng)) {
                    console.log("Memuat peta di koordinat:", lat, lng);
                    
                    const map = L.map('map').setView([lat, lng], 15);
                    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                        attribution: '&copy; OpenStreetMap'
                    }).addTo(map);

                    L.marker([lat, lng]).addTo(map).bindPopup("<b>{{ $institution->name }}</b>").openPopup();
                } else {
                    document.getElementById('map').innerHTML = "<h3 style='color:red; text-align:center; margin-top:150px;'>Error: Koordinat bukan angka!</h3>";
                }
            }, 500);
        });
    </script>
@else
    <div class="bg-light border-bottom">
        <div class="container py-3 text-center text-muted small">
            <span style="opacity: 0.7;">📍</span> Map is not displayed because not available.
        </div>
    </div>
@endif

<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col-12">
            <h4 class="mb-4 fw-bold" style="color: #003366; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px;">
                Affiliated Researchers
            </h4>

            <div id="resultsContainer" class="row g-4 min-vh-50">
                <div class="col-12 text-center my-5">
                    <span class="spinner-border text-primary"></span>
                </div>
            </div>

            <nav class="mt-5">
                <ul id="paginationContainer" class="pagination justify-content-center"></ul>
            </nav>
        </div>
    </div>
</div>

<footer class="academic-footer">
    <div class="container">
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="footer-logo mb-2" style="font-family: Georgia; font-size: 1.4rem; font-weight: bold; color: #003366;">📚 SustainDex</div>
                <p class="small text-muted pe-md-5">A Peer-Reviewed Sustainable Academic Indexing System. Dedicated to organizing research materials globaly.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="mb-2">
                    <a href="#" class="small me-3">Privacy Policy</a>
                    <a href="#" class="small">Contact Us</a>
                </div>
                <div class="mt-3">
                    <a href="/submit" class="btn btn-sm btn-outline-secondary rounded-0 fw-bold">Index Your Work</a>
                </div>
            </div>
        </div>
        <div class="text-center mt-4 pt-3 border-top small text-muted">
            &copy; {{ date('Y') }} SustainDex. All rights reserved.
        </div>
    </div>
</footer>

<script>
    const institutionId = {{ $institution->id }};
    const resultsContainer = document.getElementById('resultsContainer');
    const paginationContainer = document.getElementById('paginationContainer');

    function fetchAuthors(page = 1) {
        resultsContainer.innerHTML = '<div class="col-12 text-center my-5"><span class="spinner-border text-primary"></span></div>';
        
        fetch(`/institution/${institutionId}?page=${page}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(paginator => {
            resultsContainer.innerHTML = ''; 
            document.getElementById('totalAuthors').innerText = paginator.total;

            if (paginator.data.length === 0) {
                resultsContainer.innerHTML = '<div class="col-12 text-center text-muted">No authors registered yet.</div>';
                return;
            }

            paginator.data.forEach(author => {
                const initial = author.name.charAt(0).toUpperCase();
                const card = `
                    <div class="col-md-3 col-sm-6">
                        <div class="author-card">
                            <div class="author-avatar">${initial}</div>
                            <a href="/author/${author.id}" class="author-name text-decoration-none">${author.name}</a>
                            <small class="text-muted">${author.documents_count} Publications</small>
                        </div>
                    </div>`;
                resultsContainer.insertAdjacentHTML('beforeend', card);
            });
            renderPagination(paginator);
        });
    }

    function renderPagination(paginator) {
        if (paginator.last_page <= 1) return;
        let html = '';
        const current = paginator.current_page;
        html += `<li class="page-item ${current === 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${current-1}">&laquo;</a></li>`;
        for (let i = 1; i <= paginator.last_page; i++) {
            html += `<li class="page-item ${i === current ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
        }
        html += `<li class="page-item ${current === paginator.last_page ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${current+1}">&raquo;</a></li>`;
        paginationContainer.innerHTML = html;

        document.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                fetchAuthors(this.getAttribute('data-page'));
                window.scrollTo({ top: 400, behavior: 'smooth' });
            });
        });
    }

    document.addEventListener("DOMContentLoaded", fetchAuthors);
</script>
</body>
</html>