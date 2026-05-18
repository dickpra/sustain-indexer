<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload XML - SustaIndex</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        /* TEMA AKADEMIK JURNAL (Sama persis dengan submit.blade.php) */
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
                <div class="alert alert-danger alert-dismissible fade show shadow-sm mt-3 border-0 border-start border-4 border-danger" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Oops!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(!isset($extractedData))
            <div class="text-center mb-5 mt-3">
                <h2 style="font-family: 'Georgia', serif; color: #003366;"><i class="bi bi-filetype-xml text-success me-2"></i>OJS Native XML Upload</h2>
                <p class="text-muted">Upload a Native XML file from OJS and extract the data.</p>
            </div>

            <div class="card rounded-0 border-0" style="background-color: #f9f9f9; border: 1px dashed #999 !important;">
                <div class="card-body p-5 text-center">
                    <form id="xmlScanForm" action="/submit-xml/scan" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4 mx-auto" style="max-width: 500px;">
                            <label class="form-label fw-bold mb-3">Choose file XML (.xml) <span class="text-danger">*</span> <small class="text-muted">Accepted file types: XML, ZIP</small></label>
                            <input class="form-control form-control-lg rounded-0" type="file" name="ojs_xml" accept=".xml" required>
                        </div>
                        <button id="btnScanSubmit" type="submit" class="btn btn-academic btn-lg mt-2">
                            <i class="bi bi-search me-2"></i>Scan Document &rarr;
                        </button>
                    </form>
                </div>
            </div>
            
            @else
            <form action="/submit-xml/save" method="POST">
                @csrf
                <h2 style="font-family: 'Georgia', serif; color: #003366; margin-bottom: 5px;">Review Extracted XML Data</h2>
                <div class="alert alert-success rounded-0 border-0 border-start border-4 border-success mb-4 mt-3 shadow-sm">
                    <strong><i class="bi bi-check-circle-fill me-2"></i>Scan Complete!</strong> We have populated the form with data from your XML. Please review and edit if necessary before final submission.
                </div>

                <div class="section-title">1. Material Information</div>
                
                <div class="mb-4">
                    <label class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ $extractedData['title'] ?? '' }}" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Abstract <span class="text-danger">*</span></label>
                    <textarea name="abstract" class="form-control" rows="5" required>{{ $extractedData['abstract'] ?? '' }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label">Keywords</label>
                    <input type="text" name="keywords" class="form-control" value="{{ $extractedData['keywords'] ?? '' }}" placeholder="e.g., global warming, carbon footprint">
                    <small class="text-muted">Separate multiple keywords with commas.</small>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Document Type <span class="text-danger">*</span></label>
                        <select name="document_type" class="form-select" required>
                            <option value="Journal Article" selected>Journal Article</option>
                            <option value="Book">Book</option>
                            <option value="Conference Paper">Conference Paper</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Publication Year <span class="text-danger">*</span></label>
                        <input type="number" name="pub_year" class="form-control" value="{{ $extractedData['pub_year'] ?? date('Y') }}" required>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">DOI (If available)</label>
                        <input type="text" name="doi" class="form-control" value="{{ $extractedData['doi'] ?? '' }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Total Pages</label>
                        <input type="number" name="pages" class="form-control" value="{{ $extractedData['pages'] ?? '' }}" placeholder="e.g., 15">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Reference Count</label>
                        <input type="number" name="reference_count" class="form-control" placeholder="e.g., 45">
                    </div>
                </div>

                <div class="section-title mt-5">2. Authors & Affiliations</div>
                
                @if(isset($extractedData['authors']))
                            @foreach($extractedData['authors'] as $index => $author)
                            <div class="author-block mt-4">
                                <div class="author-badge">Extracted Author {{ $index + 1 }}</div>
                                <div class="row mt-3">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label small">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" name="authors[{{ $index }}][name]" class="form-control" value="{{ $author['name'] }}" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label small">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" name="authors[{{ $index }}][email]" class="form-control" value="{{ $author['email'] }}" placeholder="e.g. author@univ.edu" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label small">Institution / Affiliation</label>
                                        <input type="text" name="authors[{{ $index }}][institution]" class="form-control" value="{{ $author['institution'] }}">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label small">Country</label>
                                        <input type="text" name="authors[{{ $index }}][country]" class="form-control" value="{{ $author['country'] }}">
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <p class="text-danger small fw-bold">No authors detected in the XML. Please add manually if required.</p>
                        @endif

                <div class="section-title mt-5">Contact Details (Publisher/Submitter)</div>
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
                        <input type="email" name="submitter_email" class="form-control" placeholder="e.g. publisher@univ.edu" required>
                        <small class="text-muted">A verification receipt will be tied to this email.</small>
                    </div>
                    <div class="d-flex justify-content-between border-top pt-4 mt-5">
                        <a href="/submit-xml" class="btn btn-outline-secondary btn-lg rounded-0 px-4">&larr; Scan Different File</a>
                    <button type="submit" class="btn btn-success btn-lg rounded-0 px-5 fw-bold shadow" style="background-color: #198754; border-color: #198754;">Index Document &rarr;</button>
                </div>
            </form>
            @endif

        </div>
    </div>
</div>
<div id="loadingOverlay" class="d-none" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.85); z-index: 9999; display: flex; flex-direction: column; justify-content: center; align-items: center; backdrop-filter: blur(4px);">
    <div class="spinner-border text-primary mb-3" style="width: 4rem; height: 4rem; border-width: 0.3em;" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <h3 style="font-family: 'Georgia', serif; color: #003366; font-weight: bold;">Scanning XML Data...</h3>
    <p class="text-muted fw-bold">Please wait, extracting authors and metadata.</p>
</div>

<script>
    const scanForm = document.getElementById('xmlScanForm');
    
    if(scanForm) {
        scanForm.addEventListener('submit', function(e) {
            // 1. Munculkan Layar Loading Full Screen
            document.getElementById('loadingOverlay').classList.remove('d-none');
            
            // 2. Kunci Tombol Submit biar gak di-spam klik
            const btnSubmit = document.getElementById('btnScanSubmit');
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';
        });
    }
</script>
</body>
@include('partials.footer')
</html>