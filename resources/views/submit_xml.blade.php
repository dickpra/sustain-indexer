<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload XML - SustaIndex</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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
        .author-badge { position: absolute; top: -12px; left: 15px; background: #003366; color: white; padding: 2px 15px; font-size: 0.85em; font-weight: bold; }
    </style>
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

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 border-start border-4 border-danger" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Oops!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(!isset($extractedData))
            <div class="text-center mb-5 mt-3">
                <h2 style="font-family: 'Georgia', serif; color: #003366;"><i class="bi bi-filetype-xml text-success me-2"></i>OJS Native XML Upload</h2>
                <p class="text-muted">Upload file XML and extract the data.</p>
            </div>

            <div class="card rounded-0 border-0" style="background-color: #f9f9f9; border: 1px dashed #999 !important;">
                <div class="card-body p-5 text-center">
                    <form id="xmlScanForm" action="/submit-xml/scan" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4 mx-auto" style="max-width: 500px;">
                            <label class="form-label fw-bold mb-3">Choose file XML (.xml) <span class="text-danger">*</span></label>
                            <input class="form-control form-control-lg rounded-0" type="file" name="ojs_xml" accept=".xml" required>
                        </div>
                        <button id="btnScanSubmit" type="submit" class="btn btn-academic btn-lg mt-2">
                            <i class="bi bi-search me-2"></i>Scan Document &rarr;
                        </button>
                    </form>
                </div>
            </div>
            
            @else
            <form id="xmlSubmitForm" action="/submit-xml/save" method="POST">
                @csrf
                
                <div id="step1_form">
                    <h2 style="font-family: 'Georgia', serif; color: #003366; margin-bottom: 5px;">Review Extracted XML Data</h2>
                    <div class="alert alert-success rounded-0 border-0 border-start border-4 border-success mb-4 mt-3 shadow-sm">
                        <strong><i class="bi bi-check-circle-fill me-2"></i>Scan Complete!</strong> We have populated the form with data from your XML. Please verify and edit if necessary.
                    </div>

                    <div class="section-title">1. Material Information</div>
                    
                    <div class="mb-4">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="input_title" class="form-control" value="{{ $extractedData['title'] ?? '' }}" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Abstract <span class="text-danger">*</span></label>
                        <textarea name="abstract" id="input_abstract" class="form-control" rows="6" required>{{ $extractedData['abstract'] ?? '' }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Keywords</label>
                        <input type="text" name="keywords" id="input_keywords" class="form-control" value="{{ $extractedData['keywords'] ?? '' }}" placeholder="e.g., global warming, sustainability">
                        <small class="text-muted">Separate multiple keywords with commas.</small>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Document Type <span class="text-danger">*</span></label>
                            <select name="document_type" id="input_doc_type" class="form-select" required>
                                <option value="Journal Article" selected>Journal Article</option>
                                <option value="Book">Book</option>
                                <option value="Conference Paper">Conference Paper</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Publication Year <span class="text-danger">*</span></label>
                            <input type="number" name="pub_year" id="input_pub_year" class="form-control" value="{{ $extractedData['pub_year'] ?? date('Y') }}" required>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">DOI (If available)</label>
                            <input type="text" name="doi" id="input_doi" class="form-control" value="{{ $extractedData['doi'] ?? '' }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Total Pages</label>
                            <input type="number" name="pages" id="input_pages" class="form-control" value="{{ $extractedData['pages'] ?? '' }}" placeholder="e.g., 15">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Reference Count</label>
                            <input type="number" name="reference_count" id="input_ref_count" class="form-control" placeholder="e.g., 45">
                        </div>
                    </div>

                    <div class="section-title mt-5">2. Authors & Affiliations</div>
                    
                    <div id="authorInputsContainer">
                        @if(isset($extractedData['authors']) && count($extractedData['authors']) > 0)
                            @foreach($extractedData['authors'] as $index => $author)
                            <div class="author-block mt-4 author-row">
                                <div class="author-badge">Extracted Author {{ $index + 1 }}</div>
                                <div class="row mt-3">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label small">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" name="authors[{{ $index }}][name]" class="form-control auth-name" value="{{ $author['name'] }}" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label small">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" name="authors[{{ $index }}][email]" class="form-control auth-email" value="{{ $author['email'] }}" placeholder="e.g. author@univ.edu" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label small">Institution / Affiliation</label>
                                        <input type="text" name="authors[{{ $index }}][institution]" class="form-control auth-inst" value="{{ $author['institution'] }}">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label small">Country</label>
                                        <input type="text" name="authors[{{ $index }}][country]" class="form-control auth-country" value="{{ $author['country'] }}">
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <p class="text-danger small fw-bold">No authors detected in the XML.</p>
                        @endif
                    </div>

                    <div class="section-title mt-5">Contact Details (Publisher/Submitter)</div>
                    <div class="row mb-5">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First name <span class="text-danger">*</span></label>
                            <input type="text" name="submitter_first_name" id="input_sub_first" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last name <span class="text-danger">*</span></label>
                            <input type="text" name="submitter_last_name" id="input_sub_last" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="submitter_email" id="input_sub_email" class="form-control" placeholder="e.g. publisher@univ.edu" required>
                            <small class="text-muted">A verification receipt will be tied to this email.</small>
                        </div>
                    </div>

                    <div class="text-end border-top pt-4 mt-5">
                        <button type="button" id="btnReview" class="btn btn-academic btn-lg">Review Submission &rarr;</button>
                    </div>
                </div>

                <div id="step2_review" class="d-none">
                    <h2 style="font-family: 'Georgia', serif; color: #003366; margin-bottom: 5px;">Review Your XML Submission</h2>
                    <div class="alert alert-warning rounded-0 border-0 border-start border-4 border-warning mb-4 mt-3 shadow-sm">
                        <strong>⚠️ Almost done!</strong> Please review the extracted data carefully before finalizing. Once submitted, an index request email will be dispatched.
                    </div>

                    <div class="table-responsive border mb-4 bg-light">
                        <table class="table table-hover w-100 m-0">
                            <tbody>
                                <tr><th style="width: 25%; color:#003366;">Document Title</th><td id="rev_title" class="fw-bold"></td></tr>
                                <tr><th style="color:#003366;">Abstract</th><td id="rev_abstract" style="text-align: justify; font-size: 0.9em; white-space: pre-line;"></td></tr>
                                <tr><th style="color:#003366;">Keywords</th><td id="rev_keywords"></td></tr>
                                <tr><th style="color:#003366;">Type & Year</th><td id="rev_type_year"></td></tr>
                                <tr><th style="color:#003366;">Pages & Refs</th><td id="rev_pages_refs"></td></tr>
                                <tr><th style="color:#003366;">DOI Link</th><td id="rev_doi" class="font-monospace text-success"></td></tr>
                                <tr><th style="color:#003366;">Authors & Affiliations</th><td id="rev_authors"></td></tr>
                                <tr><th style="color:#cc0000;">Contact / Submitter</th><td id="rev_submitter" class="fw-bold text-danger"></td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between mt-5">
                        <button type="button" id="btnBackEdit" class="btn btn-outline-secondary btn-lg rounded-0 px-4">&larr; Back to Edit</button>
                        <button type="submit" id="btnSubmitFinal" class="btn btn-success btn-lg rounded-0 px-5 fw-bold shadow" style="background-color: #198754; border-color: #198754;">Submit to Index &rarr;</button>
                    </div>
                </div>

            </form>
            @endif

        </div>
    </div>
</div>

<div id="loadingOverlay" class="d-none" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.85); z-index: 9999; display: flex; flex-direction: column; justify-content: center; align-items: center; backdrop-filter: blur(4px);">
    <div class="spinner-border text-primary mb-3" style="width: 4rem; height: 4rem; border-width: 0.3em;" role="status"></div>
    <h3 id="loadingText" style="font-family: 'Georgia', serif; color: #003366; font-weight: bold;">Processing...</h3>
    <p id="loadingSubtext" class="text-muted fw-bold">Please wait.</p>
</div>

<script>
    // 1. Loading Efek Saat Form Pertama Kali Scan File XML
    const scanForm = document.getElementById('xmlScanForm');
    if(scanForm) {
        scanForm.addEventListener('submit', function() {
            document.getElementById('loadingText').innerText = "Scanning XML Data...";
            document.getElementById('loadingSubtext').innerText = "Please wait, extracting authors and metadata.";
            document.getElementById('loadingOverlay').classList.remove('d-none');
            document.getElementById('btnScanSubmit').disabled = true;
        });
    }

    // 2. Sistem Ganti Step (Edit <=> Review) di Sisi Browser
    const btnReview = document.getElementById('btnReview');
    const btnBackEdit = document.getElementById('btnBackEdit');
    const xmlSubmitForm = document.getElementById('xmlSubmitForm');

    if(btnReview) {
        btnReview.addEventListener('click', function() {
            // Validasi kelayakan form isi teks HTML5 (Required checking)
            if(!xmlSubmitForm.checkValidity()) {
                xmlSubmitForm.reportValidity();
                return;
            }

            // Klon isi dari Input Form (Step 1) ke Tabel Review (Step 2)
            document.getElementById('rev_title').innerText = document.getElementById('input_title').value;
            document.getElementById('rev_abstract').innerText = document.getElementById('input_abstract').value;
            document.getElementById('rev_keywords').innerText = document.getElementById('input_keywords').value || '-';
            
            const docType = document.getElementById('input_doc_type').value;
            const pubYear = document.getElementById('input_pub_year').value;
            document.getElementById('rev_type_year').innerText = `${docType} (${pubYear})`;

            const pages = document.getElementById('input_pages').value || '0';
            const refs = document.getElementById('input_ref_count').value || '0';
            document.getElementById('rev_pages_refs').innerText = `${pages} Pages / ${refs} References`;
            document.getElementById('rev_doi').innerText = document.getElementById('input_doi').value || '-';

            const subFirst = document.getElementById('input_sub_first').value;
            const subLast = document.getElementById('input_sub_last').value;
            const subEmail = document.getElementById('input_sub_email').value;
            document.getElementById('rev_submitter').innerText = `${subFirst} ${subLast} (${subEmail})`;

            // Pemrosesan List Nama-Nama Author
            let authorsHtml = '<ol class="m-0 ps-3">';
            document.querySelectorAll('.author-row').forEach(function(row) {
                const name = row.querySelector('.auth-name').value;
                const email = row.querySelector('.auth-email').value;
                const inst = row.querySelector('.auth-inst').value || 'No Affiliation';
                const country = row.querySelector('.auth-country').value || 'Unknown';
                authorsHtml += `<li class="mb-1"><strong>${name}</strong> (${email})<br><span class="text-muted small">${inst}, ${country}</span></li>`;
            });
            authorsHtml += '</ol>';
            document.getElementById('rev_authors').innerHTML = authorsHtml;

            // Efek Tukar Layer View
            document.getElementById('step1_form').classList.add('d-none');
            document.getElementById('step2_review').classList.remove('d-none');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    if(btnBackEdit) {
        btnBackEdit.addEventListener('click', function() {
            // Balik ke Halaman Edit Form
            document.getElementById('step2_review').classList.add('d-none');
            document.getElementById('step1_form').classList.remove('d-none');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    if(xmlSubmitForm) {
        xmlSubmitForm.addEventListener('submit', function() {
            // Efek Loading Saat Proses Simpan Database & Tembak API Crossref Berjalan
            document.getElementById('loadingText').innerText = "Finalizing Submission...";
            document.getElementById('loadingSubtext').innerText = "Connecting to Crossref API and dispatching verification mail.";
            document.getElementById('loadingOverlay').classList.remove('d-none');
            document.getElementById('btnSubmitFinal').disabled = true;
        });
    }
</script>
@include('partials.footer')
</body>
</html>