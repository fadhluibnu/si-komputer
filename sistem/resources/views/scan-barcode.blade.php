{{--
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pindai Barcode Komputer</title>
    <!-- Bootstrap untuk styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }

        #reader {
            width: 100%;
            max-width: 500px;
            background: #000;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #dee2e6;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        #reader video {
            width: 100%;
            height: auto;
            display: block;
        }

        .card-scan {
            width: 100%;
            max-width: 550px;
        }

        .scan-status {
            transition: all 0.3s ease-in-out;
        }

        .scanning-animation {
            position: relative;
            overflow: hidden;
        }

        .scanning-animation::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, transparent, #0d6efd, transparent);
            animation: scan-line 2s infinite linear;
        }

        @keyframes scan-line {
            0% {
                transform: translateY(-10px);
            }

            50% {
                transform: translateY(calc(100% + 10px));
            }

            100% {
                transform: translateY(-10px);
            }
        }

        .btn-upload {
            font-family: 'Inter', sans-serif;
        }

        #qr-file-input {
            display: none;
        }
    </style>
</head>

<body>

    <div class="card card-scan shadow-lg border-0 rounded-4">
        <div class="card-body p-4 p-md-5 text-center">
            <i class="bi bi-qr-code-scan text-primary" style="font-size: 4rem;"></i>
            <h1 class="h3 fw-bold mt-3">Pindai Barcode</h1>
            <p class="text-muted mb-4">Arahkan kamera ke QR code pada unit komputer.<br>Atau pilih file gambar QR jika
                tidak bisa menggunakan kamera.</p>

            <!-- Kamera scanner -->
            <div id="reader" class="rounded-3"></div>

            <!-- Tombol Upload Gambar QR -->
            <div class="mt-3">
                <label for="qr-file-input" class="btn btn-outline-primary btn-upload">
                    <i class="bi bi-upload me-1"></i> Pilih File QR
                </label>
                <input type="file" id="qr-file-input" accept="image/*">
            </div>

            <!-- Status message -->
            <div id="scan-status" class="alert alert-info mt-4 scan-status" role="alert">
                <div class="d-flex align-items-center">
                    <div class="spinner-border spinner-border-sm me-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span id="status-text">Mempersiapkan scanner...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Library Scan Barcode -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const statusContainer = document.getElementById('scan-status');
            const statusText = document.getElementById('status-text');
            const readerElement = document.getElementById('reader');
            const fileInput = document.getElementById('qr-file-input');

            // --- Penting: Cek HTTPS atau localhost ---
            if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
                statusContainer.classList.remove('alert-info');
                statusContainer.classList.add('alert-danger');
                statusText.innerHTML = '<strong>Kesalahan:</strong> Halaman ini harus diakses melalui <strong>HTTPS</strong> atau <strong>localhost</strong> untuk menggunakan kamera.';
                readerElement.style.display = 'none';
                return;
            }

            const html5QrCode = new Html5Qrcode("reader");

            // Fungsi ketika berhasil scan
            function handleScanResult(decodedText) {
                statusContainer.classList.remove('alert-info', 'alert-warning');
                statusContainer.classList.add('alert-success');
                statusText.innerHTML = `<strong>Berhasil!</strong> Barcode terdeteksi: <span class="text-primary">${decodedText}</span><br>Mengarahkan ke halaman detail...`;

                // Ambil uuid dari hasil scan
                const urlParts = decodedText.split('/');
                const uuid = urlParts[urlParts.length - 1];

                if (uuid && uuid.length > 5) { // Validasi sederhana
                    const baseUrl = window.location.origin;
                    setTimeout(() => {
                        window.location.href = `${baseUrl}/scan/${uuid}`;
                    }, 1200);
                } else {
                    statusContainer.classList.remove('alert-success');
                    statusContainer.classList.add('alert-danger');
                    statusText.innerHTML = `<strong>Gagal:</strong> Format QR Code tidak valid.`;
                }
            }

            function onScanSuccess(decodedText, decodedResult) {
                html5QrCode.stop().then(() => {
                    readerElement.style.display = 'none';
                    readerElement.classList.remove('scanning-animation');
                }).catch(err => {
                    console.error("Gagal menghentikan scanner.", err);
                });
                handleScanResult(decodedText);
            }

            function onScanFailure(error) {
                // Bisa tambahkan log/feedback jika scan gagal
            }

            // --- Logika mengaktifkan kamera ---
            statusText.innerText = 'Meminta izin kamera...';

            Html5Qrcode.getCameras().then(devices => {
                if (devices && devices.length) {
                    const backCam = devices.find(d => d.label.toLowerCase().includes('back') || d.label.toLowerCase().includes('belakang'));
                    const cameraId = backCam ? backCam.id : devices[0].id;

                    statusText.innerText = 'Mengaktifkan kamera...';
                    readerElement.classList.add('scanning-animation');

                    html5QrCode.start(
                        cameraId,
                        {
                            fps: 10,
                            qrbox: (w, h) => {
                                let minEdge = Math.min(w, h);
                                let qrboxSize = Math.floor(minEdge * 0.7);
                                return { width: qrboxSize, height: qrboxSize };
                            },
                            aspectRatio: 1.333,
                        },
                        onScanSuccess,
                        onScanFailure
                    ).catch(err => {
                        console.error("Tidak dapat memulai scanner", err);
                        statusContainer.classList.remove('alert-info');
                        statusContainer.classList.add('alert-danger');
                        statusText.innerHTML = `<strong>Kesalahan Kamera:</strong> ${err.name === 'NotAllowedError' ? 'Izin kamera ditolak.' : 'Tidak dapat mengakses kamera.'}`;
                        readerElement.style.display = 'none';
                    });
                } else {
                    throw new Error("Kamera tidak ditemukan.");
                }
            }).catch(err => {
                console.error("Error mendapatkan kamera:", err);
                statusContainer.classList.remove('alert-info');
                statusContainer.classList.add('alert-danger');
                statusText.innerHTML = `<strong>Kesalahan:</strong> ${err.message || 'Tidak dapat menemukan atau mengakses kamera.'}`;
                readerElement.style.display = 'none';
            });

            // --- File Upload QR ---
            fileInput.addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (!file) return;

                statusContainer.classList.remove('alert-success', 'alert-danger');
                statusContainer.classList.add('alert-info');
                statusText.innerHTML = `<strong>Membaca QR dari file...</strong>`;

                html5QrCode.scanFile(file, true)
                    .then(decodedText => {
                        handleScanResult(decodedText);
                    })
                    .catch(err => {
                        statusContainer.classList.remove('alert-success', 'alert-info');
                        statusContainer.classList.add('alert-danger');
                        statusText.innerHTML = `<strong>Gagal membaca QR:</strong> ${err.message || 'QR tidak terdeteksi di gambar.'}`;
                    })
                    .finally(() => {
                        fileInput.value = '';
                    });
            });
        });
    </script>
</body>

</html> --}}

<!-- Index.html file -->
{{--
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Scanner / Reader</title>

    <style>
        body {
            display: flex;
            justify-content: center;
            margin: 0;
            padding: 0;
            height: 100vh;
            box-sizing: border-box;
            text-align: center;
            background: rgb(128 0 0 / 66%);
        }

        .container {
            width: 100%;
            max-width: 500px;
            margin: 5px;
        }

        .container h1 {
            color: #ffffff;
        }

        .section {
            background-color: #ffffff;
            padding: 50px 30px;
            border: 1.5px solid #b2b2b2;
            border-radius: 0.25em;
            box-shadow: 0 20px 25px rgba(0, 0, 0, 0.25);
        }

        #my-qr-reader {
            padding: 20px !important;
            border: 1.5px solid #b2b2b2 !important;
            border-radius: 8px;
        }

        #my-qr-reader img[alt="Info icon"] {
            display: none;
        }

        #my-qr-reader img[alt="Camera based scan"] {
            width: 100px !important;
            height: 100px !important;
        }

        button {
            padding: 10px 20px;
            border: 1px solid #b2b2b2;
            outline: none;
            border-radius: 0.25em;
            color: white;
            font-size: 15px;
            cursor: pointer;
            margin-top: 15px;
            margin-bottom: 10px;
            background-color: #008000ad;
            transition: 0.3s background-color;
        }

        button:hover {
            background-color: #008000;
        }

        #html5-qrcode-anchor-scan-type-change {
            text-decoration: none !important;
            color: #1d9bf0;
        }

        video {
            width: 100% !important;
            border: 1px solid #b2b2b2 !important;
            border-radius: 0.25em;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Scan QR Codes</h1>
        <div class="section">
            <div id="my-qr-reader">
            </div>
        </div>
    </div>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js">
    </script>

    <script>

        function domReady(fn) {
            if (document.readyState === "complete" || document.readyState === "interactive"

            ) {
                setTimeout(fn, 1000);
            }

            else {
                document.addEventListener("DOMContentLoaded", fn);
            }
        }

        domReady(function () {

            // If found you qr code
            function onScanSuccess(decodeText, decodeResult) {
                alert("You Qr is : " + decodeText, decodeResult);
            }

            let htmlscanner = new Html5QrcodeScanner("my-qr-reader",
                {
                    fps: 10, qrbos: 250
                });
            htmlscanner.render(onScanSuccess);
        });
    </script>
</body>

</html> --}}


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Scanner / Reader</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons (optional for icon) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #e3e8ee 0%, #e7f0fa 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }

        .container {
            width: 100%;
            max-width: 570px;
        }

        .section {
            background-color: #fff;
            padding: 40px 28px 32px 28px;
            border-radius: 1.5em;
            border: 1px solid #dee2e6;
            box-shadow: 0 6px 32px rgba(56, 100, 197, 0.14), 0 1.5px 6px rgba(0, 0, 0, 0.08);
            margin-top: 18px;
        }

        h1 {
            color: #0d6efd;
            font-weight: 700;
            font-size: 2.2rem;
            margin-top: 0.75em;
            margin-bottom: 0.5em;
            font-family: 'Inter', sans-serif;
            letter-spacing: -1px;
        }

        .subtext {
            color: #7b809a;
            margin-bottom: 1.5em;
            margin-top: -0.5em;
            font-size: 1.07em;
        }

        .scanner-frame {
            position: relative;
            width: 100%;
            margin: 0 auto 1.5em auto;
            background: #111;
            border-radius: 18px;
            box-shadow: 0 2px 10px rgba(13, 110, 253, 0.07);
            border: 1.5px solid #eee;
            overflow: hidden;
            max-width: 430px;
            min-height: 290px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* scan-line effect */
        .scan-line {
            position: absolute;
            left: 7%;
            width: 86%;
            height: 3.5px;
            background: linear-gradient(90deg, transparent, #0d6efd 60%, transparent);
            border-radius: 3px;
            animation: scanline-move 2.2s linear infinite;
            z-index: 20;
        }

        @keyframes scanline-move {
            0% {
                top: 18px;
                opacity: 0.7;
            }

            10% {
                opacity: 1;
            }

            50% {
                top: calc(100% - 22px);
                opacity: 1;
            }

            90% {
                opacity: 1;
            }

            100% {
                top: 18px;
                opacity: 0.7;
            }
        }

        /* Make #my-qr-reader fill frame */
        #my-qr-reader {
            width: 96% !important;
            max-width: 410px !important;
            min-height: 260px !important;
            margin: 0 auto !important;
            background: transparent;
            border: none !important;
            box-shadow: none !important;
            border-radius: 14px !important;
            position: relative;
            z-index: 10;
            padding: 0 !important;
        }

        #my-qr-reader video {
            border-radius: 14px !important;
            width: 100% !important;
            height: auto !important;
            box-shadow: 0 0 0 0 transparent !important;
            border: none !important;
            background: #000;
        }

        #my-qr-reader img[alt="Info icon"] {
            display: none;
        }

        #my-qr-reader img[alt="Camera based scan"] {
            width: 90px !important;
            height: 90px !important;
        }

        /* Decorative corner borders */
        .scanner-corner {
            position: absolute;
            width: 24px;
            height: 24px;
            border: 3px solid #0d6efd;
            z-index: 21;
        }

        .corner-tl {
            top: 12px;
            left: 12px;
            border-right: none;
            border-bottom: none;
        }

        .corner-tr {
            top: 12px;
            right: 12px;
            border-left: none;
            border-bottom: none;
        }

        .corner-bl {
            bottom: 12px;
            left: 12px;
            border-right: none;
            border-top: none;
        }

        .corner-br {
            bottom: 12px;
            right: 12px;
            border-left: none;
            border-top: none;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .section {
                padding: 18px 4px;
            }

            .scanner-frame {
                min-height: 180px;
                max-width: 98vw;
            }

            #my-qr-reader {
                min-height: 120px !important;
            }
        }

        #my-qr-reader__scan_region img {
            opacity: 0.8;
            padding: 10px;
            background: white;
            border-radius: 8px;
        }

        #html5-qrcode-button-camera-permission {
            outline: none;
            border: none;
            border-radius: 8px;
            padding: 10px;
            background: #0d6efd;
            color: white;
        }

        #html5-qrcode-anchor-scan-type-change {
            text-decoration: underline;
            cursor: pointer;
            color: white;
        }
    </style>
</head>

<body>
    <div class="container" style="padding: 0 10px;">
        <div class="section shadow-lg border-0 rounded-4" style="padding: 30px;">
            <i class="bi bi-qr-code-scan text-primary" style="font-size: 3.3rem;"></i>
            <h1>Pindai QR / Barcode</h1>
            <div class="subtext">Arahkan kamera ke QR code atau barcode untuk memindai.<br />Efek garis scan dan sudut
                biru untuk pengalaman lebih nyata.</div>
            <div class="scanner-frame">
                <div class="scanner-corner corner-tl"></div>
                <div class="scanner-corner corner-tr"></div>
                <div class="scanner-corner corner-bl"></div>
                <div class="scanner-corner corner-br"></div>
                <div class="scan-line"></div>
                <div id="my-qr-reader"></div>
            </div>
        </div>
    </div>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        function domReady(fn) {
            if (document.readyState === "complete" || document.readyState === "interactive") {
                setTimeout(fn, 1000);
            } else {
                document.addEventListener("DOMContentLoaded", fn);
            }
        }
        domReady(function () {
            function onScanSuccess(decodeText, decodeResult) {
                // alert("You Qr is : " + decodeText);
                const urlParts = decodeText.split('/');
                const uuid = urlParts[urlParts.length - 1];
                const baseUrl = window.location.origin;
                window.location.href = `${baseUrl}/scan/${uuid}`;
                // alert("You Qr is : " + decodeText + "\nUUID: " + uuid);
            }
            let htmlscanner = new Html5QrcodeScanner("my-qr-reader",
                {
                    fps: 10, qrbos: 250
                });
            htmlscanner.render(onScanSuccess);
        });
    </script>
</body>

</html>

{{--
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Scanner / Reader</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons (optional for icon) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #e3e8ee 0%, #e7f0fa 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }

        .container {
            width: 100%;
            max-width: 570px;
        }

        .section {
            background-color: #fff;
            padding: 40px 28px 32px 28px;
            border-radius: 1.5em;
            border: 1px solid #dee2e6;
            box-shadow: 0 6px 32px rgba(56, 100, 197, 0.14), 0 1.5px 6px rgba(0, 0, 0, 0.08);
            margin-top: 18px;
        }

        h1 {
            color: #0d6efd;
            font-weight: 700;
            font-size: 2.2rem;
            margin-top: 0.75em;
            margin-bottom: 0.5em;
            font-family: 'Inter', sans-serif;
            letter-spacing: -1px;
        }

        .subtext {
            color: #7b809a;
            margin-bottom: 1.5em;
            margin-top: -0.5em;
            font-size: 1.07em;
        }

        .scanner-frame {
            position: relative;
            width: 100%;
            margin: 0 auto 1.5em auto;
            background: #111;
            border-radius: 18px;
            box-shadow: 0 2px 10px rgba(13, 110, 253, 0.07);
            border: 1.5px solid #eee;
            overflow: hidden;
            max-width: 430px;
            min-height: 290px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-upload {
            display: block;
            margin: 0 auto;
            padding: 10px 20px;
            font-size: 1rem;
            color: white;
            background-color: #0d6efd;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            transition: background-color 0.3s;
        }

        .btn-upload:hover {
            background-color: #0b5ed7;
        }

        #qr-file-input {
            display: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="section shadow-lg border-0 rounded-4">
            <h1>QR Code Scanner</h1>
            <div class="subtext">Arahkan kamera ke QR code untuk memindai atau unggah gambar QR.</div>
            <div class="scanner-frame">
                <div id="my-qr-reader"></div>
            </div>
            <!-- Upload Button -->
            <label for="qr-file-input" class="btn-upload">Unggah Gambar QR</label>
            <input type="file" id="qr-file-input" accept="image/*">
        </div>
    </div>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        function domReady(fn) {
            if (document.readyState === "complete" || document.readyState === "interactive") {
                setTimeout(fn, 1000);
            } else {
                document.addEventListener("DOMContentLoaded", fn);
            }
        }

        domReady(function () {
            // Inisialisasi Scanner Live
            function onScanSuccess(decodedText, decodedResult) {
                const urlParts = decodedText.split('/');
                const uuid = urlParts[urlParts.length - 1];
                const baseUrl = window.location.origin;
                window.location.href = `${baseUrl}/scan/${uuid}`;
            }

            let htmlscanner = new Html5QrcodeScanner("my-qr-reader", {
                fps: 10,
                qrbos: 250,
            });
            htmlscanner.render(onScanSuccess);

            // Fungsi untuk File Upload
            const fileInput = document.getElementById("qr-file-input");
            fileInput.addEventListener("change", async (event) => {
                const file = event.target.files[0];
                if (!file) {
                    alert("Tidak ada file yang dipilih.");
                    return;
                }

                try {
                    const html5QrCode = new Html5Qrcode("my-qr-reader");
                    const result = await html5QrCode.scanFile(file, true);
                    const urlParts = result.split('/');
                    const uuid = urlParts[urlParts.length - 1];
                    const baseUrl = window.location.origin;
                    window.location.href = `${baseUrl}/scan/${uuid}`;
                } catch (error) {
                    alert("Gagal membaca QR code dari gambar. Pastikan gambar valid.");
                }
            });
        });
    </script>
</body>

</html> --}}