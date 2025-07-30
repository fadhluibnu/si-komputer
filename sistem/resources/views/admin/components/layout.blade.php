<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda | Sistem Informasi Komputer ESDM</title>
    <link href="{{ asset('assets/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('assets/style.css') }}">

    <style>
        /* Mencegah layout "melompat" saat modal Bootstrap terbuka */
        body.modal-open {
            overflow: auto !important;
            padding-right: 0 !important;
        }
    </style>
    </head>
<body class="d-flex flex-column min-vh-100">

    @include('admin.components.navbar')

    {{-- Konten utama tidak lagi dibungkus .container agar @yield bisa mengontrolnya --}}
    @yield('content')

    {{-- Footer bisa ditambahkan di sini jika perlu --}}
    {{-- <footer class="mt-auto bg-light text-center text-lg-start">
        <div class="text-center p-3">
            © {{ date('Y') }} Hak Cipta:
            <a class="text-dark" href="#">ESDM</a>
        </div>
    </footer> --}}

    <script src="{{ asset('assets/bootstrap.bundle.min.js') }}"></script>
    
    {{-- Stack untuk script tambahan dari halaman lain --}}
    @stack('scripts')
</body>
</html>