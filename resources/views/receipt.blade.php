<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Receipt - SustaIndex</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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
        body { background-color: #f4f4f4; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; }
        .academic-header { background-color: #003366; color: white; padding: 20px 0; border-bottom: 4px solid #cc0000; }
        .academic-header a { color: white; text-decoration: none; }
        .academic-title { font-family: 'Georgia', serif; font-size: 1.8rem; font-weight: normal; margin: 0; }
        .main-container { background: white; padding: 40px; border: 1px solid #ccc; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-top: 50px; margin-bottom: 50px; border-top: 5px solid #006600;}
        .btn-academic { background-color: #003366; color: white; border-radius: 0; padding: 8px 20px; font-weight: bold; border: 1px solid #002244; }
    </style>
</head>
<body>

<div class="academic-header shadow-sm">
    <div class="container d-flex justify-content-between align-items-center">
        <h1 class="academic-title"><a href="/">📚 SustaIndex</a></h1>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8 main-container text-center">
            
            <h2 style="font-family: 'Georgia', serif; color: #006600;">Submission Received Successfully</h2>
            
            <div class="p-4 mt-4 mb-4" style="background-color: #f8f9fa; border: 1px dashed #aaa;">
                <p class="fs-5 mb-1">Your tracking ID is:</p>
                <p class="fs-2 fw-bold text-dark mb-0" id="receiptId">{{ $document->document_number }}</p>
                <p class="text-muted small mt-2">You can bookmark this page or check your browser history to return here.</p>
            </div>
            
            <p class="fs-5 mt-4">
                A verification link has been sent to:<br>
                <strong class="text-primary">{{ $document->submitter_email }}</strong>
            </p>

            @if($document->is_verified)
                <div class="alert alert-success rounded-0 mt-3 d-inline-block text-start" style="border-left: 4px solid #198754; background-color: #d4edda;">
                    <strong>✅ Document Verified!</strong><br>
                    Your document has been successfully verified and is now live in the SustaIndex index.
                </div>
            @else
                <div class="alert alert-warning rounded-0 mt-3 d-inline-block text-start" style="border-left: 4px solid #ffc107;">
                    <strong>⚠️ Action Required:</strong><br>
                    Your document is currently pending. You must click the link in your email to officially publish your document to our index.
                </div>

                <div class="mt-5 p-4 border-top">
                    <p class="mb-2 fw-bold text-secondary">Didn't receive the email? Check your spam folder or request a new one.</p>
                    
                    <div id="resendAlert" class="alert d-none rounded-0" role="alert"></div>

                    <button id="btnResend" class="btn btn-outline-secondary rounded-0 fw-bold">
                        ✉️ Resend Verification Email
                    </button>
                </div>
            @endif

            {{-- <div class="mt-5 p-4 border-top">
                <p class="mb-2 fw-bold text-secondary">Didn't receive the email? Check your spam folder or request a new one.</p>
                
                <div id="resendAlert" class="alert d-none rounded-0" role="alert"></div>

                <button id="btnResend" class="btn btn-outline-secondary rounded-0 fw-bold">
                    ✉️ Resend Verification Email
                </button>
            </div> --}}
            
            <div class="mt-4 pt-3">
                <a href="/" class="btn btn-academic">Return to Homepage</a>
            </div>

        </div>
    </div>
</div>

<script>
    document.getElementById('btnResend').addEventListener('click', function() {
        let btn = this;
        let alertBox = document.getElementById('resendAlert');
        
        // Kunci tombol biar nggak di-spam
        btn.innerHTML = 'Sending... <span class="spinner-border spinner-border-sm"></span>';
        btn.disabled = true;
        alertBox.classList.add('d-none');
        alertBox.classList.remove('alert-success', 'alert-danger');

        // Panggil API Resend yang kita buat di Controller
        fetch('/resend-email', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ document_number: '{{ $document->document_number }}' })
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(res => {
            alertBox.classList.remove('d-none');
            if (res.status === 200) {
                alertBox.classList.add('alert-success');
                alertBox.innerText = res.body.message;
            } else {
                alertBox.classList.add('alert-danger');
                alertBox.innerText = res.body.error || 'Terjadi kesalahan.';
            }
        })
        .catch(error => {
            alertBox.classList.remove('d-none');
            alertBox.classList.add('alert-danger');
            alertBox.innerText = 'Gagal terhubung ke server.';
        })
        .finally(() => {
            // Beri cooldown 10 detik sebelum bisa diklik lagi
            setTimeout(() => {
                btn.innerHTML = '✉️ Resend Verification Email';
                btn.disabled = false;
            }, 10000);
        });
    });
</script>

</body>
@include('partials.footer')
</html>