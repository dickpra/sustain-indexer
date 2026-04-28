<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Document - SustaIndex</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <style>
        /* TEMA AKADEMIK JURNAL */
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
        
        /* Desain Blok Author */
        .author-block { background-color: #f8f9fa; border: 1px solid #e0e0e0; padding: 20px; margin-bottom: 15px; position: relative; }
        .author-badge { position: absolute; top: -12px; left: 15px; background: #cc0000; color: white; padding: 2px 15px; font-size: 0.85em; font-weight: bold; }
        .coauthor-badge { background: #003366 !important; color: #fff !important; }
        .remove-author { position: absolute; top: 10px; right: 15px; }
        
        /* Map Container */
        #map { height: 300px; width: 100%; border: 1px solid #ccc; margin-top: 10px; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="academic-header">
    <div class="container d-flex justify-content-between align-items-center">
        <h1 class="academic-title"><a href="/">📚 SustaIndex</a></h1>
        <a href="/" class="btn btn-sm btn-outline-light rounded-0">Cancel</a>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10 main-container">
            
            <form id="submitForm" enctype="multipart/form-data">
                @csrf
                <div id="step1_form">
                    <h2 style="font-family: 'Georgia', serif; color: #003366; margin-bottom: 5px;">Submit a New Material</h2>
                    <p class="text-muted mb-4">Please fill out the form carefully. Authors will be deduplicated based on their email addresses.</p>

                    <div class="section-title">1. Material Information</div>
                    <div class="mb-4">
                        <label class="form-label">Title of the Submitted Material <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="Enter the title exactly as it appears..." required>
                        <small class="text-muted">Including capitalization.</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Abstract <span class="text-danger">*</span></label>
                        <textarea name="abstract" class="form-control" rows="5" required></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Keywords</label>
                        <input type="text" name="keywords" class="form-control" placeholder="e.g., global warming, carbon footprint, sustainability">
                        <small class="text-muted">Separate multiple keywords with commas.</small>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Document Type <span class="text-danger">*</span></label>
                            <select name="document_type" class="form-select" required>
                                <option value="" disabled selected>-- select one --</option>
                                <option value="Book">Book</option>
                                <option value="Journal Article">Journal Article</option>
                                <option value="Conference Paper">Conference Paper</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Publication Year</label>
                            <input type="number" name="pub_year" class="form-control" placeholder="e.g., 2026">
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Total Pages</label>
                            <input type="number" name="pages" class="form-control" placeholder="e.g., 15">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Reference Count</label>
                            <input type="number" name="reference_count" class="form-control" placeholder="e.g., 45">
                        </div>
                    </div> <div class="section-title">2. Authors & Affiliations</div>
                    <p class="text-muted small mb-3">Email addresses are strictly required to accurately connect documents to the correct author profiles.</p>
                    
                    <div id="authorContainer">
                        <div class="author-block">
                            <div class="author-badge">Author 1 (Primary)</div>
                            <div class="row mt-2">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="authors[0][name]" class="form-control" placeholder="e.g. Jane Smith" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="authors[0][email]" class="form-control" placeholder="e.g. jane@univ.edu" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small">Country</label>
                                    <input type="text" name="authors[0][country]" class="form-control" placeholder="e.g., Indonesia">
                                </div>
                                <div class="col-md-6 mb-3 position-relative">
                                    <label class="form-label small">Institution / Affiliation <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" name="authors[0][institution]" class="form-control inst-input" placeholder="Search institution..." autocomplete="off" required>
                                        <button class="btn btn-outline-secondary btn-add-inst" type="button" data-bs-toggle="modal" data-bs-target="#mapModal" data-target-input="0">
                                            🗺️ Add New
                                        </button>
                                    </div>
                                    <ul class="list-group position-absolute w-100 d-none inst-suggestions shadow" style="z-index: 1050; max-height: 200px; overflow-y: auto;"></ul>
                                    
                                    <small class="text-muted" style="font-size: 11px;">Not in our database? Click "Add New" to map it.</small>
                                    <input type="hidden" name="authors[0][lat]" id="lat_0">
                                    <input type="hidden" name="authors[0][lng]" id="lng_0">
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="btnAddAuthor" class="btn btn-sm btn-outline-dark mt-1 fw-bold">+ Add Co-Author</button>

                    <div class="section-title mt-5">3. Document Upload</div>
                    <div class="mb-4 p-3" style="background-color: #f9f9f9; border: 1px dashed #999;">
                        <label class="form-label">Please attach a PDF of your submission <span class="text-danger">*</span></label>
                        <input type="file" name="pdf_file" id="pdfFileInput" class="form-control mb-2" accept=".pdf" required>
                        <small class="text-danger fw-bold">Note: The system will scan this file to verify the exact Title and Author names provided above.</small>
                    </div>

                    <div class="section-title mt-2">Contact Details (Submitter)</div>
                    <p class="text-muted small mb-3">The verification email will be sent to this address. The submitter does not have to be an author.</p>
                    <div class="row mb-5">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First name <span class="text-danger">*</span></label>
                            <input type="text" name="submitter_first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last name <span class="text-danger">*</span></label>
                            <input type="text" name="submitter_last_name" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="submitter_email" class="form-control" required>
                            <small class="text-muted">A verification link will be sent to this email.</small>
                        </div>
                    </div>

                    <div class="text-end border-top pt-4 mt-5">
                        <button type="button" id="btnReview" class="btn btn-academic btn-lg">Review Submission &rarr;</button>
                    </div>
                </div>
                <div id="step2_review" class="d-none">
                    <h2 style="font-family: 'Georgia', serif; color: #003366; margin-bottom: 5px;">Review Your Submission</h2>
                    <div class="alert alert-warning rounded-0 border-0 border-start border-4 border-warning mb-4 shadow-sm">
                        <strong>⚠️ Almost done!</strong> Please review your data carefully. Once submitted, changes cannot be made automatically.
                    </div>

                    <div class="table-responsive border mb-4 bg-light">
                        <table class="table table-hover w-100 m-0">
                            <tbody>
                                <tr><th style="width: 25%; color:#003366;">Document Title</th><td id="rev_title" class="fw-bold"></td></tr>
                                <tr><th style="color:#003366;">Abstract</th><td id="rev_abstract" style="text-align: justify; font-size: 0.9em;"></td></tr>
                                <tr><th style="color:#003366;">Keywords</th><td id="rev_keywords"></td></tr>
                                <tr><th style="color:#003366;">Type & Year</th><td id="rev_type_year"></td></tr>
                                <tr><th style="color:#003366;">Pages & Refs</th><td id="rev_pages_refs"></td></tr>
                                <tr><th style="color:#003366;">Authors & Affiliations</th><td id="rev_authors"></td></tr>
                                <tr><th style="color:#cc0000;">Contact / Submitter</th><td id="rev_submitter" class="fw-bold text-danger"></td></tr>
                                <tr><th style="color:#003366;">PDF File</th><td id="rev_file" class="text-primary font-monospace"></td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between mt-5">
                        <button type="button" id="btnBackEdit" class="btn btn-outline-secondary btn-lg rounded-0 px-4">&larr; Back to Edit</button>
                        <button type="button" id="btnSubmitFinal" class="btn btn-success btn-lg rounded-0 px-5 fw-bold shadow">Submit to Index &rarr;</button>
                    </div>
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
                <input type="text" id="newInstName" class="form-control" placeholder="e.g., Universitas Gadjah Mada">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Country</label>
                <input type="text" id="newInstCountry" class="form-control" placeholder="e.g., Indonesia">
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
    // --- LOGIKA MULTIPLE AUTHORS ---
    const authorContainer = document.getElementById('authorContainer');
    let authorIndex = 0;

    document.getElementById('btnAddAuthor').addEventListener('click', function() {
        // Tambah author baru
        authorIndex++;
        let html = `
        <div class="card mb-3 author-card" id="author_${authorIndex}">
            <div class="card-body position-relative">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="author-badge coauthor-badge">Author ${authorIndex + 1}</div>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-author remove-author">Remove</button>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="authors[${authorIndex}][name]" class="form-control" placeholder="e.g. Jane Smith" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="authors[${authorIndex}][email]" class="form-control" placeholder="e.g. jane@univ.edu" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small">Country</label>
                        <input type="text" name="authors[${authorIndex}][country]" class="form-control" placeholder="e.g. United Kingdom">
                    </div>
                    
                    <div class="col-md-6 mb-3 position-relative">
                        <label class="form-label small">Institution / Affiliation <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="authors[${authorIndex}][institution]" class="form-control inst-input" placeholder="Search institution..." autocomplete="off" required>
                            <button class="btn btn-outline-secondary btn-add-inst" type="button" data-bs-toggle="modal" data-bs-target="#mapModal" data-target-input="${authorIndex}">
                                🗺️ Add New
                            </button>
                        </div>
                        <ul class="list-group position-absolute w-100 d-none inst-suggestions shadow" style="z-index: 1050; max-height: 200px; overflow-y: auto;"></ul>
                        
                        <small class="text-muted" style="font-size: 11px;">Not in our database? Click "Add New" to map it.</small>
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
        // Pakai .closest() agar kalau klik kena icon/teks di dalem tombol tetep jalan
        let removeBtn = e.target.closest('.btn-remove-author');
        
        if(removeBtn) {
            removeBtn.closest('.author-card').remove();
            updateAuthorNumbers();
        }
    });

    // Fungsi untuk update nomor dan atribut author setelah add/remove
    function updateAuthorNumbers() {
        const authorCards = authorContainer.querySelectorAll('.author-card, .author-block');
        authorCards.forEach((card, idx) => {
            // Update judul
            let title = card.querySelector('.card-title, .author-badge');
            if (title) {
                if (idx === 0) {
                    // Primary author
                    if (title.classList.contains('author-badge')) {
                        title.textContent = `Author 1 (Primary)`;
                    } else {
                        title.textContent = `Author 1 (Primary)`;
                    }
                } else {
                    if (title.classList.contains('author-badge')) {
                        title.textContent = `Author ${idx + 1}`;
                    } else {
                        title.textContent = `Author ${idx + 1}`;
                    }
                }
            }
            // Update semua input name dan id
            card.querySelectorAll('input, button').forEach(input => {
                if (input.name) {
                    input.name = input.name.replace(/authors\[\d+\]/, `authors[${idx}]`);
                }
                if (input.id && input.id.match(/^lat_\d+$/)) {
                    input.id = `lat_${idx}`;
                }
                if (input.id && input.id.match(/^lng_\d+$/)) {
                    input.id = `lng_${idx}`;
                }
                if (input.getAttribute('data-target-input') !== null) {
                    input.setAttribute('data-target-input', idx);
                }
            });
        });
        // Reset authorIndex ke jumlah terakhir
        authorIndex = authorCards.length - 1;
    }

    // ==========================================
    // LOGIKA MULTI-STEP & SUBMIT
    // ==========================================
    const step1 = document.getElementById('step1_form');
    const step2 = document.getElementById('step2_review');
    const form = document.getElementById('submitForm');

    // 1. TOMBOL "REVIEW SUBMISSION" (Dari Form ke Tabel)
    document.getElementById('btnReview').addEventListener('click', function() {
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = new FormData(form);

        document.getElementById('rev_title').innerText = formData.get('title');
        document.getElementById('rev_abstract').innerText = formData.get('abstract');
        document.getElementById('rev_keywords').innerText = formData.get('keywords') || '-';
        document.getElementById('rev_type_year').innerText = formData.get('document_type') + ' (' + (formData.get('pub_year') || 'N/A') + ')';
        
        const pgs = formData.get('pages') ? formData.get('pages') + ' pages' : 'N/A';
        const refs = formData.get('reference_count') ? formData.get('reference_count') + ' refs' : 'N/A';
        document.getElementById('rev_pages_refs').innerText = pgs + ' | ' + refs;

        const submitterName = formData.get('submitter_first_name') + ' ' + formData.get('submitter_last_name');
        document.getElementById('rev_submitter').innerText = submitterName + ' (' + formData.get('submitter_email') + ')';

        let authorsHtml = '<ol class="mb-0 ps-3">';
        let index = 0;
        while(formData.has(`authors[${index}][name]`)) {
            let name = formData.get(`authors[${index}][name]`);
            let email = formData.get(`authors[${index}][email]`);
            let inst = formData.get(`authors[${index}][institution]`);
            let country = formData.get(`authors[${index}][country]`);
            
            authorsHtml += `
                <li class="mb-2">
                    <strong>${name}</strong> <span class="text-muted">(${email})</span><br>
                    <small style="color:#006600;">🏛️ ${inst} ${country ? '- ' + country : ''}</small>
                </li>`;
            index++;
        }
        authorsHtml += '</ol>';
        document.getElementById('rev_authors').innerHTML = authorsHtml;

        const fileInput = document.querySelector('input[name="pdf_file"]');
        document.getElementById('rev_file').innerText = fileInput.files[0] ? '📄 ' + fileInput.files[0].name : '-';

        step1.classList.add('d-none');
        step2.classList.remove('d-none');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // 2. TOMBOL "BACK TO EDIT"
    document.getElementById('btnBackEdit').addEventListener('click', function() {
        step2.classList.add('d-none');
        step1.classList.remove('d-none');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // 3. TOMBOL "FINAL SUBMIT" (Kirim Data ke Backend)
    document.getElementById('btnSubmitFinal').addEventListener('click', function() {
        const btn = this;
        const originalText = btn.innerHTML;
        btn.innerHTML = 'Uploading Data... <span class="spinner-border spinner-border-sm"></span>';
        btn.disabled = true;
        document.getElementById('btnBackEdit').disabled = true;

        const formElement = document.getElementById('submitForm'); // Ganti kalau ID form-mu beda
        const formData = new FormData(formElement);

        fetch('/submit-index', {
            method: 'POST',
            headers: { 
                'Accept': 'application/json',
                // 🔥 WAJIB ADA: Biar Laravel tahu ini bukan serangan hacker (CSRF)
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
            },
            body: formData
        })
        .then(response => response.json().then(data => ({status: response.status, body: data})))
        .then(res => {
            if (res.status === 200 || res.status === 201) {
                // 1. JURUS BUMI HANGUS: Kosongkan form
                document.getElementById('submitForm').reset();

                // 2. CEK STATUS DARI CONTROLLER
                if (res.body.status === 'pending_duplicate') {
                    // POPUP KUNING (WARNING)
                    Swal.fire({
                        title: 'Status Pending',
                        text: 'This submission is currently pending due to potential duplicates. Please check the receipt for details.',
                        icon: 'warning',
                        confirmButtonColor: '#f39c12',
                        confirmButtonText: 'View Receipt'
                    }).then(() => {
                        window.location.replace('/receipt/' + res.body.confirmation_id);
                    });
                } else {
                    // POPUP HIJAU (SUKSES NORMAL)
                    Swal.fire({
                        title: 'Success!',
                        text: 'Book has been successfully submitted to the index.',
                        icon: 'success',
                        showConfirmButton: false, // Hilangkan tombol biar elegan
                        timer: 1500 // Otomatis pindah setelah 1.5 detik
                    }).then(() => {
                        window.location.replace('/receipt/' + res.body.confirmation_id);
                    });
                }
            } else {
            // --- JURUS DETEKTIF ERROR LARAVEL ---
                let errorMessage = "Check your form, something seems wrong.";

                // 1. Cek apakah ini error validasi bawaan Laravel (ada object 'errors')
                if (res.body.errors) {
                    // Ambil nama kolom pertama yang error (misal: 'title')
                    const firstErrorField = Object.keys(res.body.errors)[0];
                    // Ambil pesan error pertamanya
                    errorMessage = res.body.errors[firstErrorField][0];
                } 
                // 2. Cek apakah ini error kustom dari Controller (pakai 'message' atau 'error')
                else if (res.body.message && res.body.message !== "The given data was invalid.") {
                    errorMessage = res.body.message;
                } else if (res.body.error) {
                    errorMessage = res.body.error;
                }
                // ------------------------------------

                // POPUP MERAH (ERROR VALIDASI / JUDUL DOUBLE)
                Swal.fire({
                    title: 'Oops! Cannot Submit',
                    text: errorMessage, // <--- Sekarang pakai pesan yang sudah dilacak
                    icon: 'error',
                    confirmButtonColor: '#cc0000',
                    confirmButtonText: 'Fix Form'
                });
                
                // Nyalakan tombol lagi
                btn.innerHTML = originalText;
                btn.disabled = false;
                document.getElementById('btnBackEdit').disabled = false;
            }
        })
        .catch(err => {
            // POPUP MERAH (ERROR SERVER / KONEKSI)
            Swal.fire({
                title: 'Connection Error',
                text: 'Failed to process data. Original error message: ' + err.message,
                icon: 'error',
                confirmButtonColor: '#cc0000'
            });

            btn.innerHTML = originalText;
            btn.disabled = false;
            document.getElementById('btnBackEdit').disabled = false;
        });
    });

    // --- LOGIKA LEAFLET MAP ---
    let map = null;
    let marker = null;
    const mapModal = document.getElementById('mapModal');
    
    document.addEventListener('click', function(e) {
        if(e.target.closest('.btn-add-inst')) {
            document.getElementById('activeAuthorIndex').value = e.target.closest('.btn-add-inst').getAttribute('data-target-input');
        }
    });

    mapModal.addEventListener('shown.bs.modal', function () {
        if (!map) {
            map = L.map('map').setView([-2.5489, 118.0149], 4);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            map.on('click', function(e) {
                if (marker) map.removeLayer(marker);
                marker = L.marker(e.latlng).addTo(map);
                
                document.getElementById('newInstLat').value = e.latlng.lat.toFixed(6);
                document.getElementById('newInstLng').value = e.latlng.lng.toFixed(6);
                document.getElementById('coordDisplay').innerText = `${e.latlng.lat.toFixed(4)}, ${e.latlng.lng.toFixed(4)}`;
            });

            L.Control.geocoder({
                // 🔥 TAMBAHKAN BARIS INI: Memaksa Nominatim pakai bahasa Inggris (en)
                geocoder: L.Control.Geocoder.nominatim({
                    geocodingQueryParams: {
                        'accept-language': 'en'
                    }
                }),
                defaultMarkGeocode: false,
                placeholder: "Search university or city..."
            })
            .on('markgeocode', function(e) {
                var latlng = e.geocode.center;
                if (marker) map.removeLayer(marker);
                marker = L.marker(latlng).addTo(map);
                map.flyTo(latlng, 15);
                
                document.getElementById('newInstLat').value = latlng.lat.toFixed(6);
                document.getElementById('newInstLng').value = latlng.lng.toFixed(6);
                document.getElementById('coordDisplay').innerText = `${latlng.lat.toFixed(4)}, ${latlng.lng.toFixed(4)}`;
                
                if(!document.getElementById('newInstName').value) {
                    document.getElementById('newInstName').value = e.geocode.name.split(',')[0];
                }
            })
            .addTo(map);
        }
        setTimeout(() => map.invalidateSize(), 100);
    });

    // Logika Simpan Institusi Baru
    document.getElementById('btnSaveInstitution').addEventListener('click', function() {
        const instName = document.getElementById('newInstName').value;
        const instLat = document.getElementById('newInstLat').value;
        const instLng = document.getElementById('newInstLng').value;
        const targetIndex = document.getElementById('activeAuthorIndex').value;

        if(!instName || !instLat) {
            alert("Please enter the institution name and select a location on the map.");
            return;
        }

        const inputField = document.querySelector(`input[name="authors[${targetIndex}][institution]"]`);
        inputField.value = instName;
        inputField.style.backgroundColor = "#e8f4f8";

        // 👇 TAMBAHKAN 2 BARIS INI 👇
        document.getElementById(`lat_${targetIndex}`).value = instLat;
        document.getElementById(`lng_${targetIndex}`).value = instLng;
        
        document.querySelector('#mapModal .btn-close').click();
        document.body.style.overflow = 'auto'; 

        document.getElementById('newInstName').value = '';
        document.getElementById('newInstCountry').value = '';
        if(marker) map.removeLayer(marker);
        document.getElementById('coordDisplay').innerText = 'Not selected';
    });

    // ==========================================
    // LOGIKA LIVE SEARCH (AUTOCOMPLETE) INSTITUSI
    // ==========================================
    document.addEventListener('input', function(e) {
        // Cek apakah yang diketik adalah kotak Institusi
        if (e.target.classList.contains('inst-input')) {
            const query = e.target.value;
            const wrapper = e.target.closest('.col-md-6');
            const suggestionBox = wrapper.querySelector('.inst-suggestions');

            // Kalau huruf yang diketik kurang dari 2, sembunyikan saran
            if (query.length < 2) {
                suggestionBox.classList.add('d-none');
                return;
            }

            // Panggil API secara diam-diam
            fetch('/api/institutions?q=' + query)
            .then(res => res.json())
            .then(data => {
                suggestionBox.innerHTML = ''; // Bersihkan saran lama
                
                if (data.length > 0) {
                    data.forEach(inst => {
                        const li = document.createElement('li');
                        li.className = 'list-group-item list-group-item-action cursor-pointer text-sm py-2';
                        li.style.cursor = 'pointer';
                        li.innerHTML = `<strong>${inst.name}</strong> <br><small class="text-muted">${inst.latitude ? '📍 Map Available' : ''}</small>`;
                        
                        // Kalau sarannya diklik:
                        li.onclick = function() {
                            e.target.value = inst.name; // Isi namanya
                            e.target.style.backgroundColor = "#e8f4f8";
                            
                            // Isi koordinat tersembunyinya!
                            const index = wrapper.querySelector('.btn-add-inst').getAttribute('data-target-input');
                            document.getElementById(`lat_${index}`).value = inst.latitude || '';
                            document.getElementById(`lng_${index}`).value = inst.longitude || '';
                            
                            // Tutup kotak saran
                            suggestionBox.classList.add('d-none');
                        };
                        suggestionBox.appendChild(li);
                    });
                    suggestionBox.classList.remove('d-none'); // Tampilkan kotak
                } else {
                    suggestionBox.classList.add('d-none'); // Sembunyikan kalau tidak ada hasil
                }
            });
        }
    });

    // Sembunyikan kotak saran kalau user klik di luar kotak
    document.addEventListener('click', function(e) {
        if (!e.target.classList.contains('inst-input')) {
            document.querySelectorAll('.inst-suggestions').forEach(box => box.classList.add('d-none'));
        }
    });
</script>

@include('partials.footer')
</body>
</html>