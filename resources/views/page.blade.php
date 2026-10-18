<!DOCTYPE html>
<html lang="en">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        /* 🔥 Menyembunyikan nama file & ukuran bawaan Trix Editor */
        figure.attachment figcaption.attachment__caption {
            display: none !important;
        }
        
        /* Memperbaiki jarak margin bawaan agar gambar rapi */
        figure.attachment {
            margin: 0;
            padding: 0;
        }
        </style>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->title }} - SustaIndex</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .page-content-wrapper { max-width: 900px; margin: 60px auto; padding: 0 20px; }
        .page-title { color: #003366; font-weight: 900; margin-bottom: 30px; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px; }
        .page-body img { max-width: 100%; height: auto; border-radius: 8px; }
    </style>
</head>
<body class="bg-light">

    @include('partials.header')

    <div class="page-content-wrapper bg-white shadow-sm p-4 p-md-5 rounded-4 mb-5">
        <h1 class="page-title">{{ $page->title }}</h1>
        
        <div class="page-body" style="line-height: 1.8; color: #475569;">
            {!! $page->content !!}
        </div>
        
        <div class="mt-5 pt-3 border-top text-end">
            <a href="/" class="btn btn-outline-secondary btn-sm">Kembali ke Beranda</a>
        </div>
    </div>

    @include('partials.footer')
</body>
</html>