<style>
    .footer-logo-img {
        width: 300px;
        height: auto;
        display: block;
    }

    /* 🔥 Tweak Responsive Footer */
    @media (max-width: 767px) {
        .footer-logo-img {
            margin: 0 auto;
            width: 220px; /* Logo sedikit dikecilkan di HP */
        }
        .academic-footer .text-md-end {
            text-align: center !important; /* Posisi teks/tombol di-tengah saat di HP */
        }
    }
</style>

<footer class="academic-footer">
    <div class="container">
        <div class="row text-center text-md-start align-items-center">
           <div class="col-md-6 mb-4 mb-md-0">
                <div class="footer-logo mb-3">
                    <img src="{{ asset('logo/1_Main_Sustaindex_landscape.png') }}" 
                        alt="SustaIndex"
                        class="footer-logo-img">
                </div>

                <p class="small text-muted pe-md-5 mb-0">
                    A Peer-Reviewed Sustainable Academic Indexing System.
                    Dedicated to organizing, preserving, and providing access to quality global research materials.
                </p>
            </div>
            
            <div class="col-md-6 text-md-end mt-4 mt-md-0">
                <div class="dropdown d-inline-block">
                    <button class="btn btn-outline-secondary rounded-0 fw-bold dropdown-toggle px-4" type="button" id="footerSubmitDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        Index Your Work
                    </button>
                    <!-- Dropdown menu diset start/end otomatis oleh Bootstrap -->
                    <ul class="dropdown-menu dropdown-menu-md-end shadow-sm border-0 mt-3" aria-labelledby="footerSubmitDropdown" style="border-top: 3px solid #003366 !important; min-width: 220px;">
                        <li>
                            <a class="dropdown-item py-2 fw-medium text-dark" href="/submit" style="border: none;">
                                <i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>Upload PDF
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item py-2" href="/submit-beta">
                                <i class="bi bi-robot text-primary me-2"></i>
                                <div class="d-inline-block align-middle">
                                    <span class="fw-bold d-block">Upload PDF (AI)</span>
                                    <small class="text-muted" style="font-size: 11px;">Extraction via Groq AI & Crossref</small>
                                </div>
                            </a>
                        </li>
                         <li><hr class="dropdown-divider"></li>
                         <li>
                            <a class="dropdown-item py-2 fw-medium text-dark" href="/publisher" style="border: none;">
                                <i class="bi bi-filetype-xml text-success me-2"></i>Upload XML (Publisher)
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-5 pt-3 border-top small text-muted">
            &copy; {{ date('Y') }} SustaIndex System. All rights reserved.
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>