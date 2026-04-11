<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $document->title }} - SustainDex</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* CSS tetap sama seperti milikmu */
        body { background-color: #ffffff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; }
        .academic-header { background-color: #003366; color: white; padding: 15px 0; border-bottom: 4px solid #cc0000; }
        .academic-header a { color: white; text-decoration: none; }
        .academic-title { font-family: 'Georgia', serif; font-size: 1.8rem; font-weight: normal; margin: 0; }
        .academic-nav a { font-size: 0.95rem; font-weight: bold; margin-left: 25px; color: #e0e0e0; text-decoration: none; }
        .main-container { padding-top: 30px; }
        .meta-label { font-weight: 600; color: #555; margin-bottom: 0; font-size: 0.9em; }
        .meta-value { color: #222; margin-bottom: 15px; font-size: 0.95em; }
        .doc-title { font-size: 2rem; font-weight: bold; color: #1a0dab; line-height: 1.3; }
        .doc-authors { font-size: 1.15rem; color: #006621; font-weight: 500; margin-top: 15px; margin-bottom: 5px; }
        .author-institution { font-size: 0.9rem; color: #666; margin-bottom: 25px; }
        .abstract-title { font-size: 1.2rem; font-weight: bold; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 15px; margin-top: 20px; }
        .abstract-text { font-size: 1.05rem; line-height: 1.8; color: #444; text-align: justify; }
        .doi-box { background-color: #f8f9fa; border-left: 4px solid #198754; padding: 15px 20px; margin-top: 40px; border-radius: 4px; }
        .keyword-badge { background-color: #e9ecef; color: #495057; padding: 5px 12px; border-radius: 4px; font-size: 0.85rem; margin-right: 5px; border: 1px solid #dee2e6; }
        .academic-footer { background-color: #f1f3f5; padding: 40px 0 20px 0; margin-top: 60px; border-top: 1px solid #d5d5d5; }
    </style>
</head>
<body>

<header class="academic-header shadow-sm">
    <div class="container d-flex justify-content-between align-items-center">
        <h1 class="academic-title"><a href="/">📚 SustainDex</a></h1>
        <div class="academic-nav d-none d-md-block">
            <a href="/submit">Submit Document</a>
        </div>
    </div>
</header>

<div class="container main-container mb-5 pb-5">
    <a href="javascript:history.back()" class="btn btn-link p-0 mb-4 text-decoration-none">&larr; Back to results</a>
    
    <div class="row">
        <div class="col-md-3 border-end pe-4">
            <p class="meta-label">Document Number:</p>
            <p class="meta-value text-primary fw-bold">{{ $document->document_number }}</p>

            <p class="meta-label">Record Type:</p>
            <p class="meta-value">{{ $document->document_type ?: 'N/A' }}</p>

            <p class="meta-label">Publication Year:</p>
            <p class="meta-value">{{ $document->pub_year ?: 'N/A' }}</p>

            <p class="meta-label">Pages:</p>
            <p class="meta-value">{{ $document->pages ?: 'N/A' }}</p>

            <p class="meta-label">Reference Count:</p>
            <p class="meta-value">{{ $document->reference_count ?: 'N/A' }}</p>

            <p class="meta-label">Peer Reviewed:</p>
            <p class="meta-value text-success fw-bold">
                {{ $document->is_verified ? 'Yes (Verified)' : 'Pending Review' }}
            </p>

            <p class="meta-label">Indexed Date:</p>
            <p class="meta-value">{{ $document->created_at->format('M d, Y') }}</p>
        </div>

        <div class="col-md-9 ps-md-5">
            <h1 class="doc-title">{{ $document->title }}</h1>
            
            <div class="doc-authors">
                @if(count($authors) > 0)
                    @foreach($authors as $author)
                        <a href="/author/{{ $author->id }}" class="text-decoration-none" style="color: inherit;">
                            {{ $author->name }}
                        </a>{{ !$loop->last ? '; ' : '' }}
                    @endforeach
                @else
                    <span class="text-muted">Unknown Author</span>
                @endif
            </div>

            <div class="author-institution">
                @foreach($authors as $author)
                    <div class="mb-1 italic small">
                        <sup>{{ $loop->iteration }}</sup> 
                        {{ $author->institution ? $author->institution->name : 'Independent Researcher' }} 
                        @if($author->country) ({{ $author->country }}) @endif
                    </div>
                @endforeach
            </div>

            <h3 class="abstract-title">Abstract</h3>
            <div class="abstract-text">
                {{ $document->abstract }}
            </div>

            @if($document->keywords)
                <div class="mt-4 pt-3 border-top">
                    <h6 class="fw-bold text-muted mb-2">Keywords:</h6>
                    <div>
                        @foreach(explode(',', $document->keywords) as $keyword)
                            @php $cleanKeyword = trim($keyword); @endphp
                            @if(!empty($cleanKeyword))
                                <a href="/results?q={{ urlencode($cleanKeyword) }}" class="badge bg-light text-secondary border text-decoration-none me-2 mb-2 px-3 py-2 hover-keyword" style="font-size: 0.85rem; transition: 0.2s;">
                                    # {{ $cleanKeyword }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            @if($document->doi)
            <div class="doi-box">
                <span class="fw-bold d-block mb-1 text-success">Direct Link (DOI):</span>
                <a href="{{ $document->doi }}" target="_blank" class="text-decoration-none fs-5">{{ $document->doi }}</a>
            </div>
            @endif
            
        </div>
    </div>
</div>

<footer class="academic-footer">
    <div class="container">
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="footer-logo mb-2">📚 SustainDex</div>
                <p class="small text-muted pe-md-5">A Peer-Reviewed Sustainable Academic Indexing System.</p>
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
            &copy; {{ date('Y') }} SustainDex Indexing System.
        </div>
    </div>
</footer>

</body>
</html>