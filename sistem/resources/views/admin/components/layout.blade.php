<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda | Sistem Informasi Komputer ESDM</title>
    <link href="{{ asset('assets/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('assets/style.css') }}">
</head>
<body class="d-flex flex-column min-vh-100">

    @include('admin.components.navbar')
    <div class="container py-5">
        @yield('content')
    </div>

    <script src="{{ asset('assets/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
