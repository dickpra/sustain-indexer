<footer class="academic-footer">
    <div class="container">
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="footer-logo mb-2">📚 SustaIndex</div>
                <p class="small text-muted pe-md-5">A Peer-Reviewed Sustainable Academic Indexing System. Dedicated to organizing, preserving, and providing access to quality global research materials.</p>
            </div>
            
            <div class="col-md-6 text-md-end">
                <div class="mb-2">
                    <a href="#" class="small me-3">Selection Policy</a>
                    <a href="#" class="small me-3">Privacy Policy</a>
                    <a href="#" class="small">Contact Us</a>
                </div>
                <div class="mt-3 dropdown d-inline-block">
                    <button class="btn btn-sm btn-outline-secondary rounded-0 fw-bold dropdown-toggle" type="button" id="footerSubmitDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        Index Your Work
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-3" aria-labelledby="footerSubmitDropdown" style="border-top: 3px solid #003366 !important; min-width: 200px;">
                        <li>
                            <a class="dropdown-item py-2 fw-medium text-dark" href="/submit" style="margin-left: 0; border: none;">
                                <i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>Upload PDF
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item py-2 fw-medium text-dark" href="/submit-xml" style="margin-left: 0; border: none;">
                                <i class="bi bi-filetype-xml text-success me-2"></i>Upload OJS XML
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item py-2" href="/submit-beta">
                                <i class="bi bi-robot text-primary me-2"></i>
                                <div>
                                    <span class="fw-bold d-block">Upload PDF</span>
                                    <small class="text-muted" style="font-size: 11px;">Automatic extraction using Groq AI & Crossref</small>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4 pt-3 border-top small text-muted">
            &copy; {{ date('Y') }} SustaIndex System. All rights reserved.
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>