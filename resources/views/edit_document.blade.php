<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Document - SustaIndex</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <style>
        body { background-color: #f4f4f4; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; }
        .academic-header { background-color: #003366; color: white; padding: 20px 0; border-bottom: 4px solid #cc0000; }
        .academic-header a { color: white; text-decoration: none; }
        .academic-title { font-family: 'Georgia', serif; font-size: 1.8rem; font-weight: normal; margin: 0; }
        
        .main-container { background: white; padding: 40px; border: 1px solid #ccc; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-top: 30px; margin-bottom: 50px; }
        .section-title { font-family: 'Georgia', serif; font-size: 1.3em; color: #003366; border-bottom: 1px solid #003366; padding-bottom: 8px; margin-bottom: 20px; margin-top: 30px; }
        
        .form-label { font-weight: bold; color: #222; font-size: 0.95em; }
        .form-control, .form-select { border-radius: 0; border: 1px solid #999; }
        .form-control:focus { border-color: #003366; box-shadow: none; }
        
        .btn-academic { background-color: #003366; color: white; border-radius: 0; padding: 10px 25px; font-weight: bold; border: 1px solid #002244; }
        .btn-academic:hover { background-color: #002244; color: white; }
        
        .author-card { background-color: #f8f9fa; border: 1px solid #e0e0e0; margin-bottom: 15px; position: relative; }
        .author-badge { position: absolute; top: -12px; left: 15px; background: #003366; color: white; padding: 2px 15px; font-size: 0.85em; font-weight: bold; z-index: 10; }
        .primary-badge { background: #cc0000 !important; }
        
        #map { height: 300px; width: 100%; border: 1px solid #ccc; margin-top: 10px; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="academic-header">
    <div class="container d-flex justify-content-between align-items-center">
        <h1 class="academic-title"><a href="/">📚 SustaIndex</a></h1>
        <div class="text-end">
            <span class="badge bg-warning text-dark me-3"><i class="bi bi-shield-lock-fill"></i> Secure Edit Mode</span>
            <a href="/document/{{ $document->document_number }}" class="btn btn-sm btn-outline-light rounded-0">Cancel</a>
        </div>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10 main-container">
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible shadow-sm border-0 border-start border-4 border-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Oops!</strong> {{ session('error') }}
                </div>
            @endif

            <form action="/document/{{ $document->document_number }}/update" method="POST">
                @csrf
                <h2 style="font-family: 'Georgia', serif; color: #003366; margin-bottom: 5px;">Edit Document Metadata</h2>
                <p class="text-muted small">Update any typos or incorrect information below. Changes will be reflected immediately upon saving.</p>

                <div class="section-title">1. Material Information</div>
                <div class="mb-4">
                    <label class="form-label">Title of the Submitted Material <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ $document->title }}" required>
                </div>

                <div class="row mb-4 bg-light p-3 border">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <!-- Hapus (Optional), tambah bintang dan required -->
                            <label class="form-label">Journal / Conference Name <span class="text-danger">*</span></label>
                            <input type="text" name="journal_title" class="form-control" value="{{ $document->journal_title ?? '' }}" required>
                            <small class="text-muted">Where was this published?</small>
                        </div>
                        <div class="col-md-6">
                            <!-- Hapus (Optional), tambah bintang dan required -->
                            <label class="form-label">Publisher <span class="text-danger">*</span></label>
                            <input type="text" name="publisher" class="form-control" value="{{ $document->publisher ?? '' }}" required>
                            <small class="text-muted">Who is the publisher?</small>
                        </div>
                    </div>

                <div class="mb-4">
                    <label class="form-label">Abstract <span class="text-danger">*</span></label>
                    <textarea name="abstract" class="form-control" rows="8" required>{{ $document->abstract }}</textarea>
                </div>


               <div class="mb-4">
                    <label class="form-label">Keywords</label>
                    <input type="text" name="keywords" class="form-control" value="{{ $document->keywords }}">
                    <small class="text-muted d-block mt-1">Separate multiple keywords with commas.</small>
                    
                    <!-- 🔥 WADAH BADGE AI (Memuat SDG lama dari database) -->
                    <div id="sdgBadgeContainer" class="mt-2 d-flex flex-wrap gap-2">
                        @if($document->sdgs && $document->sdgs->count() > 0)
                            @foreach($document->sdgs as $sdg)
                                <span class="badge rounded-pill shadow-sm" style="background-color: #003366; font-size: 0.8em; padding: 6px 12px; border: 1px solid #002244;">
                                    <i class="bi bi-robot text-info me-1"></i> {{ $sdg->sdg_name }}
                                </span>
                            @endforeach
                        @endif
                    </div>
                    
                    <!-- 🔥 HIDDEN INPUT (Mempertahankan data lama agar tidak hilang saat disave) -->
                    <div id="hiddenSdgInputs">
                        @if($document->sdgs && $document->sdgs->count() > 0)
                            @foreach($document->sdgs as $sdg)
                                <input type="hidden" name="sdgs[]" value="{{ $sdg->sdg_name }}">
                            @endforeach
                        @endif
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Document Type <span class="text-danger">*</span></label>
                        <select name="document_type" class="form-select" required>
                            <option value="Journal Article" {{ $document->document_type == 'Journal Article' ? 'selected' : '' }}>Journal Article</option>
                            <option value="Book" {{ $document->document_type == 'Book' ? 'selected' : '' }}>Book</option>
                            <option value="Conference Paper" {{ $document->document_type == 'Conference Paper' ? 'selected' : '' }}>Conference Paper</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Publication Year</label>
                        <input type="number" name="pub_year" class="form-control" value="{{ $document->pub_year }}">
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">DOI Link</label>
                        <input type="text" name="doi" class="form-control" value="{{ $document->doi }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Total Pages</label>
                        <input type="number" name="pages" class="form-control" value="{{ $document->pages }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Reference Count</label>
                        <input type="number" name="reference_count" class="form-control" value="{{ $document->reference_count }}">
                    </div>
                </div> 

                <div class="section-title">2. Authors & Affiliations</div>
                
                <div id="authorContainer">
                    @foreach($document->authors as $index => $author)
                    <div class="card mb-3 author-card" id="author_{{ $index }}">
                        <div class="card-body position-relative pt-4">
                            <div class="author-badge {{ $index == 0 ? 'primary-badge' : '' }}">Author {{ $index + 1 }} {{ $index == 0 ? '(Primary)' : '' }}</div>
                            @if($index > 0)
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-author remove-author" style="position: absolute; top: 10px; right: 15px;">Remove</button>
                            @endif

                            <div class="row mt-2">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="authors[{{ $index }}][name]" class="form-control" value="{{ $author->name }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="authors[{{ $index }}][email]" class="form-control" value="{{ $author->email }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small">Country</label>
                                    <input type="text" name="authors[{{ $index }}][country]" class="form-control" value="{{ $author->country }}">
                                </div>
                                <div class="col-md-6 mb-3 position-relative">
                                    <label class="form-label small">Institution / Affiliation <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" name="authors[{{ $index }}][institution]" class="form-control inst-input" value="{{ $author->institution ? $author->institution->name : '' }}" autocomplete="off" required>
                                        
                                        @php
                                            $hasMap = $author->institution && $author->institution->latitude;
                                        @endphp
                                        <button class="btn btn-add-inst {{ $hasMap ? 'btn-success text-white fw-bold' : 'btn-outline-secondary' }}" type="button" data-bs-toggle="modal" data-bs-target="#mapModal" data-target-input="{{ $index }}">
                                            {!! $hasMap ? '📍 Mapped' : '🗺️ Add Map' !!}
                                        </button>
                                    </div>
                                    <ul class="list-group position-absolute w-100 d-none inst-suggestions shadow" style="z-index: 1050; max-height: 200px; overflow-y: auto;"></ul>
                                    
                                    <input type="hidden" name="authors[{{ $index }}][lat]" id="lat_{{ $index }}" value="{{ $author->institution ? $author->institution->latitude : '' }}">
                                    <input type="hidden" name="authors[{{ $index }}][lng]" id="lng_{{ $index }}" value="{{ $author->institution ? $author->institution->longitude : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <button type="button" id="btnAddAuthor" class="btn btn-sm btn-outline-dark mt-1 fw-bold">+ Add Co-Author</button>

                <div class="text-end border-top pt-4 mt-5">
                    <button type="submit" class="btn btn-academic btn-lg">
                        <i class="bi bi-save me-2"></i> Save Changes & Update Index
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<div class="modal fade" id="mapModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content rounded-0">
      <div class="modal-header bg-dark text-white rounded-0">
        <h5 class="modal-title" style="font-family: 'Georgia', serif;">🗺️ Register New Institution</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info small rounded-0 border-0">
            Please search your institution on the map and click to pinpoint its exact location.
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Institution Name <span class="text-danger">*</span></label>
                <input type="text" id="newInstName" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Country</label>
                <input type="text" id="newInstCountry" class="form-control">
            </div>
        </div>

        <label class="form-label fw-bold mt-2">Pinpoint Location</label>
        <div id="map"></div>
        <div class="text-muted small mt-1">Coordinates: <span id="coordDisplay">Not selected</span></div>
        
        <input type="hidden" id="newInstLat">
        <input type="hidden" id="newInstLng">
        <input type="hidden" id="activeAuthorIndex">

      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-outline-secondary rounded-0" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-academic" id="btnSaveInstitution">Save & Select</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

<script>
    const authorContainer = document.getElementById('authorContainer');
    let authorIndex = {{ count($document->authors) - 1 }};

    document.getElementById('btnAddAuthor').addEventListener('click', function() {
        authorIndex++;
        let html = `
        <div class="card mb-3 author-card" id="author_${authorIndex}">
            <div class="card-body position-relative pt-4">
                <div class="author-badge">Author ${authorIndex + 1}</div>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-author remove-author" style="position: absolute; top: 10px; right: 15px;">Remove</button>

                <div class="row mt-2">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="authors[${authorIndex}][name]" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="authors[${authorIndex}][email]" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small">Country</label>
                        <input type="text" name="authors[${authorIndex}][country]" class="form-control">
                    </div>
                    
                    <div class="col-md-6 mb-3 position-relative">
                        <label class="form-label small">Institution / Affiliation <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="authors[${authorIndex}][institution]" class="form-control inst-input" autocomplete="off" required>
                            <button class="btn btn-outline-secondary btn-add-inst" type="button" data-bs-toggle="modal" data-bs-target="#mapModal" data-target-input="${authorIndex}">
                                🗺️ Add Map
                            </button>
                        </div>
                        <ul class="list-group position-absolute w-100 d-none inst-suggestions shadow" style="z-index: 1050; max-height: 200px; overflow-y: auto;"></ul>
                        <input type="hidden" name="authors[${authorIndex}][lat]" id="lat_${authorIndex}">
                        <input type="hidden" name="authors[${authorIndex}][lng]" id="lng_${authorIndex}">
                    </div>
                </div>
            </div>
        </div>
        `;
        authorContainer.insertAdjacentHTML('beforeend', html);
        updateAuthorNumbers();
    });

    authorContainer.addEventListener('click', function(e) {
        if(e.target.classList.contains('btn-remove-author')) {
            e.target.closest('.author-card').remove();
            updateAuthorNumbers();
        }
    });

    function updateAuthorNumbers() {
        const authorCards = authorContainer.querySelectorAll('.author-card');
        authorCards.forEach((card, idx) => {
            let title = card.querySelector('.author-badge');
            if (title) {
                title.textContent = idx === 0 ? `Author 1 (Primary)` : `Author ${idx + 1}`;
                if(idx === 0) {
                    title.classList.add('primary-badge');
                    let rmBtn = card.querySelector('.btn-remove-author');
                    if(rmBtn) rmBtn.remove();
                } else {
                    title.classList.remove('primary-badge');
                }
            }
            card.querySelectorAll('input, button').forEach(input => {
                if (input.name) input.name = input.name.replace(/authors\[\d+\]/, `authors[${idx}]`);
                if (input.id && input.id.match(/^lat_\d+$/)) input.id = `lat_${idx}`;
                if (input.id && input.id.match(/^lng_\d+$/)) input.id = `lng_${idx}`;
                if (input.getAttribute('data-target-input') !== null) input.setAttribute('data-target-input', idx);
            });
        });
        authorIndex = authorCards.length - 1;
    }

    // --- SCRIPT MAP & AUTOCOMPLETE (SAMA PERSIS DENGAN SUBMIT) ---
    let map = null;
    let marker = null;
    
    document.addEventListener('click', function(e) {
        if(e.target.closest('.btn-add-inst')) {
            document.getElementById('activeAuthorIndex').value = e.target.closest('.btn-add-inst').getAttribute('data-target-input');
        }
    });

    document.getElementById('mapModal').addEventListener('shown.bs.modal', function () {
        const targetIndex = document.getElementById('activeAuthorIndex').value;
        const currentLat = document.getElementById(`lat_${targetIndex}`).value;
        const currentLng = document.getElementById(`lng_${targetIndex}`).value;
        const currentInst = document.querySelector(`input[name="authors[${targetIndex}][institution]"]`).value;

        document.getElementById('newInstName').value = currentInst;

        if (!map) {
            map = L.map('map').setView([-2.5489, 118.0149], 4);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

            map.on('click', function(e) {
                if (marker) map.removeLayer(marker);
                marker = L.marker(e.latlng).addTo(map);
                document.getElementById('newInstLat').value = e.latlng.lat.toFixed(6);
                document.getElementById('newInstLng').value = e.latlng.lng.toFixed(6);
                document.getElementById('coordDisplay').innerText = `${e.latlng.lat.toFixed(4)}, ${e.latlng.lng.toFixed(4)}`;
            });

            L.Control.geocoder({
                geocoder: L.Control.Geocoder.nominatim({ geocodingQueryParams: { 'accept-language': 'en' } }),
                defaultMarkGeocode: false
            }).on('markgeocode', function(e) {
                var latlng = e.geocode.center;
                if (marker) map.removeLayer(marker);
                marker = L.marker(latlng).addTo(map);
                map.flyTo(latlng, 15);
                document.getElementById('newInstLat').value = latlng.lat.toFixed(6);
                document.getElementById('newInstLng').value = latlng.lng.toFixed(6);
                document.getElementById('coordDisplay').innerText = `${latlng.lat.toFixed(4)}, ${latlng.lng.toFixed(4)}`;
                if(!document.getElementById('newInstName').value) document.getElementById('newInstName').value = e.geocode.name.split(',')[0];
            }).addTo(map);
        }

        if (currentLat && currentLng) {
            const latlng = [parseFloat(currentLat), parseFloat(currentLng)];
            if (marker) map.removeLayer(marker);
            marker = L.marker(latlng).addTo(map);
            map.setView(latlng, 15);
            document.getElementById('newInstLat').value = currentLat;
            document.getElementById('newInstLng').value = currentLng;
            document.getElementById('coordDisplay').innerText = `${parseFloat(currentLat).toFixed(4)}, ${parseFloat(currentLng).toFixed(4)}`;
        } else {
            map.setView([-2.5489, 118.0149], 4);
            if (marker) map.removeLayer(marker);
            document.getElementById('newInstLat').value = '';
            document.getElementById('newInstLng').value = '';
            document.getElementById('coordDisplay').innerText = 'Not selected';
        }
        setTimeout(() => map.invalidateSize(), 100);
    });

    document.getElementById('btnSaveInstitution').addEventListener('click', function() {
        const instName = document.getElementById('newInstName').value;
        const instLat = document.getElementById('newInstLat').value;
        const instLng = document.getElementById('newInstLng').value;
        const targetIndex = document.getElementById('activeAuthorIndex').value;

        if(!instName || !instLat) {
            alert("Please enter the institution name and select a location.");
            return;
        }

        const inputField = document.querySelector(`input[name="authors[${targetIndex}][institution]"]`);
        inputField.value = instName;
        inputField.style.backgroundColor = "#e8f4f8";

        document.getElementById(`lat_${targetIndex}`).value = instLat;
        document.getElementById(`lng_${targetIndex}`).value = instLng;
        
        document.querySelector('#mapModal .btn-close').click();
    });

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('inst-input')) {
            const query = e.target.value;
            const wrapper = e.target.closest('.input-group').parentElement;
            const suggestionBox = wrapper.querySelector('.inst-suggestions');

            if (query.length < 2) { suggestionBox.classList.add('d-none'); return; }

            fetch('/api/institutions?q=' + query)
            .then(res => res.json())
            .then(data => {
                suggestionBox.innerHTML = ''; 
                if (data.length > 0) {
                    data.forEach(inst => {
                        const li = document.createElement('li');
                        li.className = 'list-group-item list-group-item-action cursor-pointer text-sm py-2';
                        li.style.cursor = 'pointer';
                        li.innerHTML = `<strong>${inst.name}</strong> <br><small class="text-muted">${inst.latitude ? '📍 Map Available' : ''}</small>`;
                        
                        li.onclick = function() {
                            e.target.value = inst.name; 
                            e.target.style.backgroundColor = "#e8f4f8";
                            const index = wrapper.querySelector('.btn-add-inst').getAttribute('data-target-input');
                            document.getElementById(`lat_${index}`).value = inst.latitude || '';
                            document.getElementById(`lng_${index}`).value = inst.longitude || '';
                            suggestionBox.classList.add('d-none');
                        };
                        suggestionBox.appendChild(li);
                    });
                    suggestionBox.classList.remove('d-none');
                } else {
                    suggestionBox.classList.add('d-none');
                }
            });
        }
    });

    document.addEventListener('click', function(e) {
        if (!e.target.classList.contains('inst-input')) {
            document.querySelectorAll('.inst-suggestions').forEach(box => box.classList.add('d-none'));
        }
    });

    // ==========================================
    // 🤖 BETA AI V6: NINJA HIDDEN INPUTS (UNTUK HALAMAN EDIT)
    // ==========================================
    const sdgFullNames = {
        'sdg1': 'SDG 1: No Poverty', 'sdg2': 'SDG 2: Zero Hunger', 'sdg3': 'SDG 3: Good Health and Well-being',
        'sdg4': 'SDG 4: Quality Education', 'sdg5': 'SDG 5: Gender Equality', 'sdg6': 'SDG 6: Clean Water and Sanitation',
        'sdg7': 'SDG 7: Affordable and Clean Energy', 'sdg8': 'SDG 8: Decent Work and Economic Growth',
        'sdg9': 'SDG 9: Industry Innovation and Infrastructure', 
        'sdg10': 'SDG 10: Reduced Inequality', 'sdg11': 'SDG 11: Sustainable Cities and Communities', 
        'sdg12': 'SDG 12: Responsible Consumption and Production', 'sdg13': 'SDG 13: Climate Action', 
        'sdg14': 'SDG 14: Life Below Water', 'sdg15': 'SDG 15: Life on Land',
        'sdg16': 'SDG 16: Peace and Justice Strong Institutions', 'sdg17': 'SDG 17: Partnerships to achieve the Goal'
    };

    let aiDebounceTimer;
    const abstractInput = document.querySelector('textarea[name="abstract"]');
    const hiddenSdgContainer = document.getElementById('hiddenSdgInputs');

    async function scanAbstractForSDG(showToast = true) {
        if (!abstractInput) return;
        const text = abstractInput.value;
        const badgeContainer = document.getElementById('sdgBadgeContainer');

        // Jika abstrak dikosongkan, hapus semua
        if (text.length < 30) {
            if (badgeContainer) badgeContainer.innerHTML = ''; 
            if (hiddenSdgContainer) hiddenSdgContainer.innerHTML = ''; 
            return; 
        }

        try {
            const response = await fetch('/api/scan-sdg', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ abstract: text })
            });

            const data = await response.json();
            const detectedSdgs = data.detected || []; 

            let newlyDetectedCount = 0;
            let detectedShortNames = [];
            let fullSdgNames = [];

            detectedSdgs.forEach(sdgId => {
                const key = sdgId.toLowerCase(); 
                if (sdgFullNames[key]) {
                    newlyDetectedCount++;
                    detectedShortNames.push(sdgFullNames[key].split(':')[0]); 
                    fullSdgNames.push(sdgFullNames[key]); 
                }
            });

            // Ganti SDG lama dengan hasil Scan terbaru dari AI!
            if (hiddenSdgContainer) hiddenSdgContainer.innerHTML = ''; 
            if (badgeContainer) badgeContainer.innerHTML = ''; 

            if (fullSdgNames.length > 0) {
                fullSdgNames.forEach(sdgName => {
                    hiddenSdgContainer.insertAdjacentHTML('beforeend', `<input type="hidden" name="sdgs[]" value="${sdgName}">`);

                    if (badgeContainer) {
                        let badgeHtml = `
                            <span class="badge rounded-pill shadow-sm" style="background-color: #003366; font-size: 0.8em; padding: 6px 12px; border: 1px solid #002244; transform: scale(0); animation: popIn 0.3s forwards;">
                                <i class="bi bi-robot text-info me-1"></i> ${sdgName}
                            </span>
                        `;
                        badgeContainer.insertAdjacentHTML('beforeend', badgeHtml);
                    }
                });
            }

            if (newlyDetectedCount > 0 && showToast) {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 4500,
                    timerProgressBar: true
                });

                Toast.fire({
                    icon: 'success',
                    title: '🤖 AI Re-Scan',
                    text: `Auto-updated ${newlyDetectedCount} goals: ${detectedShortNames.join(', ')}`
                });
            }

        } catch (error) {
            console.error("AI Scan Failed:", error);
        }
    }

    if (abstractInput) {
        // AI hanya memindai ulang jika user MENGETIK (mengubah) abstraknya.
        // Jika tidak diubah, data SDG lama tetap dipertahankan.
        abstractInput.addEventListener('input', function() {
            clearTimeout(aiDebounceTimer);
            aiDebounceTimer = setTimeout(() => scanAbstractForSDG(true), 1500);
        });
    }
</script>
</body>
</html>