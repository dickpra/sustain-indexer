<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Document - SustainDex</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .btn-secondary-academic { background-color: #e0e0e0; color: #333; border-radius: 0; border: 1px solid #ccc; font-weight: bold;}
        .btn-secondary-academic:hover { background-color: #d0d0d0; }

        .author-row { display: flex; gap: 10px; margin-bottom: 10px; }
        
        /* Tabel Review */
        .review-table th { background-color: #f9f9f9; width: 30%; font-weight: bold; color: #444; }
        .review-table td, .review-table th { border: 1px solid #ddd; padding: 12px; }
    </style>
</head>
<body>

<div class="academic-header">
    <div class="container d-flex justify-content-between align-items-center">
        <h1 class="academic-title"><a href="/">📚 Sustain Index</a></h1>
        <a href="/" class="btn btn-sm btn-outline-light rounded-0">Cancel / Back to Search</a>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10 main-container">
            
            <div id="errorAlert" class="alert alert-danger d-none fw-bold rounded-0" role="alert"></div>

            <form id="submitForm" enctype="multipart/form-data">
                @csrf
                <div id="step1_form">
                    <h2 style="font-family: 'Georgia', serif; color: #003366; margin-bottom: 5px;">Submit a New Material</h2>
                    <p class="text-muted mb-4">Please fill out the form below carefully. You will have a chance to review your entries before final submission.</p>

                    <div class="section-title">Material Information</div>
                    <div class="mb-4">
                        <label class="form-label">Title of the Submitted Material <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="Enter the title exactly as it appears..." required>
                        <small class="text-muted">Including capitalization.</small>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Author(s) <span class="text-danger">*</span></label>
                        <p class="text-muted small mb-2">Enter the authors in the order that they appear in the source.</p>
                        <div id="authorContainer">
                            <div class="author-row">
                                <input type="text" name="authors[]" class="form-control" placeholder="Author 1 Name" required>
                                <button type="button" class="btn btn-secondary-academic disabled" style="width: 45px;">-</button>
                            </div>
                        </div>
                        <button type="button" id="btnAddAuthor" class="btn btn-sm btn-secondary-academic mt-1">+ Add another author</button>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Abstract <span class="text-danger">*</span></label>
                        <textarea name="abstract" class="form-control" rows="6" required></textarea>
                    </div>

                    <div class="section-title">Publication Details</div>
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Document Type <span class="text-danger">*</span></label>
                            <select name="document_type" class="form-select" required>
                                <option value="" disabled selected>-- select one --</option>
                                <option value="Book">Book</option>
                                <option value="Conference Paper">Conference Paper</option>
                                <option value="Journal Article">Journal Article</option>
                                <option value="Report">Report</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Publication Year</label>
                            <input type="number" name="pub_year" class="form-control" placeholder="e.g., 2026">
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Pages</label>
                            <input type="number" name="pages" class="form-control">
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Reference Count</label>
                            <input type="number" name="reference_count" class="form-control">
                        </div>
                    </div>

                    <div class="section-title">Document Upload</div>
                    <div class="mb-4 p-3" style="background-color: #f9f9f9; border: 1px dashed #999;">
                        <label class="form-label">Please attach a PDF of your submission <span class="text-danger">*</span></label>
                        <input type="file" name="pdf_file" id="pdfFileInput" class="form-control mb-2" accept=".pdf" required>
                        <small class="text-danger fw-bold">Note: The system will scan this file to verify the exact Title and Author names provided above.</small>
                    </div>

                    <div class="section-title">Your Contact Details</div>
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

                    <div class="text-end border-top pt-4">
                        <button type="button" class="btn btn-academic btn-lg" id="btnGoToReview">Continue to Review &rarr;</button>
                    </div>
                </div>
                
                <div id="step2_review" class="d-none">
                    <h2 style="font-family: 'Georgia', serif; color: #003366; margin-bottom: 15px;">Review Submission</h2>
                    
                    <div class="alert alert-warning rounded-0 border-0" style="background-color: #fff8e1; color: #856404; border-left: 4px solid #ffeeba !important;">
                        Please review your submission details listed below. If you need to modify any details, please use the <b>Edit</b> button to go back to the submission form. Once you have confirmed that your submission details are correct, you must click the <b>Final Submit</b> button to complete your submission.
                    </div>

                    <table class="table review-table w-100 mt-4">
                        <tbody>
                            <tr><th>Title</th><td id="rev_title"></td></tr>
                            <tr><th>Author(s)</th><td id="rev_authors"></td></tr>
                            <tr><th>Abstract</th><td id="rev_abstract" style="text-align: justify;"></td></tr>
                            <tr><th>Document Type</th><td id="rev_type"></td></tr>
                            <tr><th>Publication Year</th><td id="rev_year"></td></tr>
                            <tr><th>Pages</th><td id="rev_pages"></td></tr>
                            <tr><th>Reference Count</th><td id="rev_ref"></td></tr>
                            <tr><th>Attached File</th><td id="rev_file" class="text-primary fw-bold"></td></tr>
                            <tr><th>Submitter Name</th><td id="rev_name"></td></tr>
                            <tr><th>Submitter Email</th><td id="rev_email"></td></tr>
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-between mt-5 pt-3 border-top">
                        <button type="button" class="btn btn-secondary-academic btn-lg" id="btnBackToForm">&larr; Edit Details</button>
                        <button type="submit" class="btn btn-academic btn-lg" id="btnFinalSubmit">Final Submit</button>
                    </div>
                </div>
            </form>

            <div id="step3_receipt" class="d-none text-center py-5">
                <h2 style="font-family: 'Georgia', serif; color: #006600;">Submission Received Successfully</h2>
                <div class="p-4 mt-4 mb-4" style="background-color: #f1f8f1; border: 1px solid #c3e6c3;">
                    <p class="fs-5 mb-1">Your submission Confirmation ID is:</p>
                    <p class="fs-3 fw-bold text-dark mb-0" id="receiptId"></p>
                </div>
                
                <p class="fs-5 mt-4">
                    An email confirming your submission details has been sent to <br>
                    <strong id="receiptEmail" class="text-primary"></strong>
                </p>
                <div class="alert alert-info rounded-0 mt-3 d-inline-block text-start">
                    <strong>Important Action Required:</strong><br>
                    Please check your inbox (or spam folder) and click the verification link to officially publish your document.
                </div>
                
                <div class="mt-5">
                    <a href="/submit" class="btn btn-secondary-academic">Submit Another Document</a>
                    <a href="/" class="btn btn-academic ms-2">Return to Homepage</a>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    // --- LOGIC TAMBAH AUTHOR ---
    const authorContainer = document.getElementById('authorContainer');
    let authorCount = 1;

    document.getElementById('btnAddAuthor').addEventListener('click', function() {
        authorCount++;
        const newRow = document.createElement('div');
        newRow.className = 'author-row';
        newRow.innerHTML = `
            <input type="text" name="authors[]" class="form-control" placeholder="Author ${authorCount} Name" required>
            <button type="button" class="btn btn-secondary-academic btn-remove-author" style="width: 45px;">X</button>
        `;
        authorContainer.appendChild(newRow);

        newRow.querySelector('.btn-remove-author').addEventListener('click', function() {
            newRow.remove();
        });
    });

    // --- ALUR STEP 1 -> STEP 2 (REVIEW) ---
    const form = document.getElementById('submitForm');
    const step1 = document.getElementById('step1_form');
    const step2 = document.getElementById('step2_review');
    const step3 = document.getElementById('step3_receipt');
    const errorAlert = document.getElementById('errorAlert');

    document.getElementById('btnGoToReview').addEventListener('click', function() {
        // Validasi HTML5 (Biar kalau ada yang kosong, disuruh isi dulu)
        if (!form.reportValidity()) return;

        // Ambil data dari form
        const formData = new FormData(form);
        
        // Gabungkan array authors
        const authorInputs = document.querySelectorAll('input[name="authors[]"]');
        let authorArray = [];
        authorInputs.forEach(input => authorArray.push(input.value));

        // Tulis ke Tabel Review
        document.getElementById('rev_title').innerText = formData.get('title');
        document.getElementById('rev_authors').innerText = authorArray.join('; ');
        document.getElementById('rev_abstract').innerText = formData.get('abstract');
        document.getElementById('rev_type').innerText = formData.get('document_type');
        document.getElementById('rev_year').innerText = formData.get('pub_year') || 'N/A';
        document.getElementById('rev_pages').innerText = formData.get('pages') || 'N/A';
        document.getElementById('rev_ref').innerText = formData.get('reference_count') || 'N/A';
        document.getElementById('rev_name').innerText = formData.get('submitter_first_name') + ' ' + formData.get('submitter_last_name');
        document.getElementById('rev_email').innerText = formData.get('submitter_email');
        
        // Ambil nama file PDF
        const fileInput = document.getElementById('pdfFileInput');
        if(fileInput.files.length > 0) {
            document.getElementById('rev_file').innerText = fileInput.files[0].name;
        }

        // Sembunyikan Step 1, Munculkan Step 2
        step1.classList.add('d-none');
        step2.classList.remove('d-none');
        window.scrollTo(0,0);
    });

    // --- ALUR STEP 2 KEMBALI KE STEP 1 (EDIT) ---
    document.getElementById('btnBackToForm').addEventListener('click', function() {
        step2.classList.add('d-none');
        step1.classList.remove('d-none');
        window.scrollTo(0,0);
    });

    // --- ALUR FINAL SUBMIT (STEP 2 -> STEP 3) ---
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        let btnFinalSubmit = document.getElementById('btnFinalSubmit');
        let btnBackToForm = document.getElementById('btnBackToForm');
        let formData = new FormData(form);

        // Kunci tombol agar tidak double klik
        btnFinalSubmit.innerHTML = 'Verifying Document...';
        btnFinalSubmit.disabled = true;
        btnBackToForm.disabled = true;
        errorAlert.classList.add('d-none');

        fetch('/submit-index', {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value }
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(res => {
            if (res.status === 200) {
                // Sembunyikan form penuh, Munculkan Receipt (Step 3)
                form.classList.add('d-none');
                step3.classList.remove('d-none');
                
                document.getElementById('receiptId').innerText = res.body.confirmation_id;
                document.getElementById('receiptEmail').innerText = formData.get('submitter_email');
                
                window.scrollTo(0,0);
            } else {
                // Balik ke Step 1 dan Munculkan Error
                step2.classList.add('d-none');
                step1.classList.remove('d-none');
                errorAlert.classList.remove('d-none');
                errorAlert.innerText = res.body.error || 'System error occurred during verification.';
                window.scrollTo(0,0);
            }
        })
        .finally(() => {
            btnFinalSubmit.innerHTML = 'Final Submit';
            btnFinalSubmit.disabled = false;
            btnBackToForm.disabled = false;
        });
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