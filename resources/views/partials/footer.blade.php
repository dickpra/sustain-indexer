<style>
    /* ==========================================
       FOOTER UTAMA
       Semua jarak diatur lewat variabel di sini
    ========================================== */
    .academic-footer {
        --ft-green: #166534;
        --ft-navy: #003366;
        --ft-heading: #1e293b;
        --ft-text: #64748b;
        --ft-line: #e2e8f0;

        --ft-pad-top: 40px;
        --ft-pad-bottom: 16px;
        --ft-col-gap-x: 2.5rem;   
        --ft-col-gap-y: 1.75rem;  
        --ft-title-gap: 12px;     

        background: #fff;
        border-top: 1px solid var(--ft-line);
        padding: var(--ft-pad-top) 0 var(--ft-pad-bottom);
    }
    .academic-footer .container { max-width: 1200px; }
    .footer-main { padding-bottom: 32px; border-bottom: 1px solid var(--ft-line); }
    .footer-main > .row { --bs-gutter-x: var(--ft-col-gap-x); --bs-gutter-y: var(--ft-col-gap-y); }
    
    /* LOGO SUSTAINDEX + MISI */
    .footer-logo-img { display: block; width: 240px; max-width: 100%; height: auto; margin-bottom: 14px; }
    .footer-mission-content { max-width: 440px; }
    .footer-mission-title { margin: 0 0 8px; color: var(--ft-green); font-size: 1.08rem; font-weight: 900; line-height: 1.35; }
    .footer-mission-text { margin: 0 0 14px; color: var(--ft-text); font-size: 0.84rem; line-height: 1.65; }
    .footer-mission-tags { display: flex; flex-wrap: wrap; gap: 6px; }
    .footer-mission-tag { padding: 4px 10px; border: 1px solid #dcfce7; border-radius: 50px; background: #f0fdf4; color: var(--ft-green); font-size: 0.68rem; font-weight: 800; white-space: nowrap; }

    /* JUDUL KOLOM */
    .footer-section-title { margin: 0 0 var(--ft-title-gap); color: var(--ft-heading); font-size: 0.9rem; font-weight: 900; line-height: 1.3; }

    /* DEVELOPED BY + POWERED BY */
    .developer-logo { display: block; width: auto; height: auto; max-width: 100px; max-height: 64px; object-fit: contain; }
    .footer-partner-list { display: flex; flex-wrap: wrap; align-items: center; gap: 10px 14px; min-height: 64px; }
    .partner-logo { display: block; width: auto; height: auto; max-width: 90px; max-height: 42px; object-fit: contain; }

    /* CONTACT & LINKS */
    .footer-contact-text { margin: 0 0 6px; color: var(--ft-text); font-size: 0.82rem; line-height: 1.55; }
    .footer-contact-link { display: inline-block; color: var(--ft-navy); font-size: 0.86rem; font-weight: 800; text-decoration: none; overflow-wrap: anywhere; margin-bottom: 6px;}
    .footer-contact-link:hover { color: #2563eb; text-decoration: underline; }
    .footer-contact-actions { margin-top: 16px; }
    .footer-submit-btn { padding: 8px 14px; font-size: 0.82rem; font-weight: 800; white-space: nowrap; }
    
    /* DROPDOWN MENU */
    .footer-dropdown-menu { min-width: 240px; margin-bottom: 8px !important; border: 0; border-top: 3px solid var(--ft-navy); font-size: 0.88rem; }
    .footer-dropdown-menu .dropdown-item { display: flex; align-items: flex-start; gap: 10px; padding: 9px 16px; color: var(--ft-heading); font-weight: 500; white-space: normal; }
    .footer-dropdown-menu .dropdown-item i { margin-top: 2px; }
    .footer-dropdown-menu .dropdown-item small { display: block; color: var(--ft-text); font-size: 0.72rem; font-weight: 400; }
    .footer-dropdown-menu .dropdown-item .item-title { display: block; font-weight: 700; }

    /* COPYRIGHT */
    .footer-copyright { padding-top: 16px; font-size: 0.78rem; text-align: center; }

    /* RESPONSIVE */
    @media (max-width: 991.98px) { .academic-footer { --ft-pad-top: 32px; --ft-col-gap-x: 1.5rem; } .footer-mission-content { max-width: 560px; } }
    @media (max-width: 767.98px) {
        .academic-footer { --ft-pad-top: 28px; --ft-pad-bottom: 12px; --ft-col-gap-y: 1.5rem; --ft-title-gap: 10px; }
        .footer-main { padding-bottom: 24px; }
        .footer-brand-column { text-align: center; margin-bottom: 30px; }
        .footer-logo-img { width: 210px; margin-left: auto; margin-right: auto; }
        .footer-mission-content { max-width: 500px; margin: 0 auto; }
        .footer-mission-tags, .footer-partner-list, .footer-quick-links { justify-content: center; }
        .developer-logo { margin: 0 auto; max-height: 58px; }
        .footer-partner-list { min-height: 58px; }
        .partner-logo { max-width: 80px; max-height: 38px; }
        .footer-contact-actions, .mobile-center-text { text-align: center; }
    }
    @media (max-width: 380px) {
        .footer-logo-img { width: 185px; } .footer-mission-title { font-size: 0.98rem; } .footer-mission-text { font-size: 0.78rem; }
        .footer-mission-tag { padding: 4px 8px; font-size: 0.62rem; } .partner-logo { max-width: 70px; max-height: 33px; } .footer-partner-list { gap: 8px 10px; }
    }
</style>

<footer class="academic-footer">
    <div class="container">
        <!-- BARIS UTAMA: IDENTITAS + INFORMASI FOOTER -->
        <div class="footer-main">
            <div class="row align-items-start">

                <!-- KOLOM 1: LOGO DAN MISI SUSTAINDEX -->
                <div class="col-12 col-lg-4 footer-brand-column">
                    <img src="{{ asset('logo/1_Main_Sustaindex_landscape.png') }}" alt="SustaIndex" class="footer-logo-img">
                    <div class="footer-mission-content">
                        <h5 class="footer-mission-title">Research for a Sustainable Future</h5>
                        <p class="footer-mission-text">
                            Connecting quality research with global sustainability goals.
                            Supporting the discovery, preservation, and accessibility of
                            academic knowledge for a better future.
                        </p>
                        <div class="footer-mission-tags">
                            <span class="footer-mission-tag">Quality Research</span>
                            <span class="footer-mission-tag">Global Access</span>
                            <span class="footer-mission-tag">Sustainability</span>
                        </div>
                    </div>
                </div>

                <!-- KOLOM 2: DEVELOPER, PARTNER & QUICK LINKS (Di-grouping agar rapi) -->
                <div class="col-12 col-md-7 col-lg-5 mb-4 mb-md-0 mobile-center-text">
                    <!-- Baris Atas: Logo-logo -->
                    <div class="row">
                        <!-- DEVELOPED BY -->
                        <div class="col-6 col-sm-5 mb-4 mb-sm-0">
                            <h6 class="footer-section-title">Developed by :</h6>

                            <a href="https://www.wfied.org"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="d-inline-flex flex-column align-items-center text-decoration-none">

                                <img
                                    src="{{ asset('logo/1_color_wfied_potrait.png') }}"
                                    alt="WFIED"
                                    class="developer-logo"
                                >

                                <span class="mt-2">www.wfied.org</span>

                            </a>
                        </div>

                        <!-- POWERED BY -->
                        <div class="col-6 col-sm-7">
                            <h6 class="footer-section-title">Powered by :</h6>
                            <div class="footer-partner-list">
                                <img src="{{ asset('logo/um_logo.webp') }}" alt="Universitas Negeri Malang" class="partner-logo">
                                <img src="{{ asset('logo/yudharta_logo.png') }}" alt="Universitas Yudharta" class="partner-logo">
                                <img src="{{ asset('logo/acenesia_logo.png') }}" alt="Acenesia" class="partner-logo">
                            </div>
                        </div>
                    </div>

                    <!-- 🔥 Baris Bawah: QUICK LINKS (Langsung mengisi ruang kosong) -->
                    <div class="row mt-4 pt-2">
                        <div class="col-12">
                            {{-- <h6 class="footer-section-title">Quick Links:</h6> --}}
                            <ul class="list-unstyled mb-0 d-flex flex-wrap gap-3 footer-quick-links">
                                <!-- Tarik data halaman aktif dari database secara instan -->
                                @php
                                    $cmsPages = \App\Models\Page::where('is_active', true)->get();
                                @endphp
                                
                                @foreach($cmsPages as $page)
                                    <li>
                                        <a href="/page/{{ $page->slug }}" class="footer-contact-link" style="font-weight: 600; font-size: 0.85rem;">
                                            <i class="bi bi-chevron-right text-muted" style="font-size: 0.7rem;"></i> {{ $page->title }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- KOLOM 3: CONTACT & BUTTON SUBMIT -->
                <div class="col-12 col-md-5 col-lg-3 mobile-center-text">
                    <h6 class="footer-section-title">Contact:</h6>
                    <p class="footer-contact-text">If you have any questions, please contact:</p>
                    <a href="mailto:sustaindex@wfied.org" class="footer-contact-link">
                        <i class="bi bi-envelope-fill me-1"></i> sustaindex@wfied.org
                    </a>

                    <!-- INDEX YOUR WORK -->
                    <div class="footer-contact-actions">
                        <div class="dropdown dropup dropdown-center d-inline-block">
                            <button class="btn btn-outline-secondary rounded-0 dropdown-toggle footer-submit-btn" type="button" id="footerSubmitDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Index Your Work
                            </button>
                            <ul class="dropdown-menu shadow-sm footer-dropdown-menu" aria-labelledby="footerSubmitDropdown">
                                <li><a class="dropdown-item" href="/submit"><i class="bi bi-file-earmark-pdf-fill text-danger"></i><span class="item-title">Upload PDF</span></a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/submit-beta"><i class="bi bi-robot text-primary"></i><span><span class="item-title">Upload PDF (AI)</span><small>Extraction via Groq AI &amp; Crossref</small></span></a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/publisher"><i class="bi bi-filetype-xml text-success"></i><span class="item-title">Upload XML (Publisher)</span></a></li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- BARIS COPYRIGHT -->
        <div class="text-muted footer-copyright">
            &copy; {{ date('Y') }} SustaIndex System. All rights reserved.
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>