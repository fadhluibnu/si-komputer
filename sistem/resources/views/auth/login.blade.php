@extends('auth.components.layouts')

@section('content')
    <div class="login-wrapper">
        <div class="login-image">
            <div class="animated-background">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
            </div>
            <div class="text-center">
                <div class="mb-4">
                    <i class="bi bi-pc-display" style="font-size: 4rem; filter: drop-shadow(0 5px 10px rgba(0,0,0,0.1));"></i>
                </div>
                <h2 class="mt-4 mb-3 fw-bold" style="text-shadow: 0 2px 4px rgba(0,0,0,0.1);">SI-KOMPUTER ESDM</h2>
                <p class="mb-0">Sistem Informasi Pengelolaan Data Komputer<br>Dinas Energi dan Sumber Daya Mineral</p>
                <div class="mt-4">
                    <span class="badge bg-light text-primary p-2">Versi 1.0</span>
                </div>
            </div>
        </div>
        <div class="login-form">
            <div class="login-logo fade-in">
                <i class="bi bi-pc-display"></i> SI-KOMPUTER ESDM
            </div>

            <h1 class="login-title fade-in delay-1">Selamat Datang</h1>
            <p class="login-subtitle fade-in delay-1">Silakan login untuk mengakses panel admin</p>

{{--            <?php if(!empty($error)): ?>--}}
{{--            <div class="alert alert-danger fade-in delay-1" role="alert">--}}
{{--                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?>--}}
{{--            </div>--}}
{{--            <?php endif; ?>--}}

{{--            <?php if(isset($_SESSION['error_message'])): ?>--}}
{{--            <div class="alert alert-danger fade-in delay-1" role="alert">--}}
{{--                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $_SESSION['error_message']; ?>--}}
{{--            </div>--}}
{{--                <?php unset($_SESSION['error_message']); ?>--}}
{{--            <?php endif; ?>--}}

{{--            <?php if(isset($_GET['logout']) && $_GET['logout'] == 'success'): ?>--}}
{{--            <div class="alert alert-success fade-in delay-1" role="alert">--}}
{{--                <i class="bi bi-check-circle-fill me-2"></i> Anda berhasil keluar dari sistem--}}
{{--            </div>--}}
{{--            <?php endif; ?>--}}

            <form action="{{ route('login.post') }}" method="POST" class="fade-in delay-2">
                @csrf
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control m-0" placeholder="Username" required autofocus>
                </div>

                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control m-0" id="password" placeholder="Password" required>
                    <span class="input-group-text password-toggle" onclick="togglePassword()">
                        <i class="bi bi-eye" id="toggleIcon"></i>
                    </span>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4 fade-in delay-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label" for="remember">
                            Ingat saya
                        </label>
                    </div>
                    <a href="#" class="forgot-password">Lupa password?</a>
                </div>

                <button type="submit" class="btn btn-primary w-100 fade-in delay-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Masuk
                </button>

                <div class="text-center mt-4 fade-in delay-3">
                    <a href="index.php" class="text-decoration-none">
                        <i class="bi bi-arrow-left"></i> Kembali ke halaman utama
                    </a>
                </div>
            </form>

            <div class="login-footer fade-in delay-3">
                <p>&copy; <?php echo date('Y'); ?> Dinas ESDM Jawa Tengah | SI-KOMPUTER ESDM v1.0</p>
            </div>
        </div>
    </div>
@endsection
