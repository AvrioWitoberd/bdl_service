<?php
// index.php (letakkan di root project, bukan di public/)
session_start();
// Jangan redirect otomatis ke login
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Elektronik ABC - Perbaikan & Pemeliharaan Terpercaya</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* --- VARIABEL & RESET --- */
        :root {
            --primary: #2563eb; /* Biru Modern */
            --primary-dark: #1e40af;
            --secondary: #64748b;
            --accent: #10b981; /* Hijau Sukses */
            --bg-light: #f8fafc;
            --white: #ffffff;
            --shadow-sm: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        * {
            box-sizing: border-box;
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Poppins', sans-serif; /* Ganti font agar lebih fresh */
            margin: 0;
            padding: 0;
            background-color: var(--bg-light);
            color: #334155;
            line-height: 1.7;
            overflow-x: hidden;
        }

        /* --- ANIMASI DASAR --- */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        /* Class untuk Scroll Animation (di-handle oleh JS di bawah) */
        .reveal {
            opacity: 0;
            transform: translateY(50px);
            transition: all 0.8s ease-out;
        }
        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* --- HEADER (GLASSMORPHISM) --- */
        header {
            background-color: rgba(255, 255, 255, 0.85); /* Transparan */
            backdrop-filter: blur(12px); /* Efek Kaca */
            -webkit-backdrop-filter: blur(12px);
            color: var(--primary-dark);
            padding: 1rem 5%;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        header h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(90deg, var(--primary), var(--primary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 2rem;
        }

        nav a {
            color: #475569;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            position: relative;
            transition: color 0.3s ease;
        }

        nav a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 0;
            background-color: var(--primary);
            transition: width 0.3s ease;
        }

        nav a:hover {
            color: var(--primary);
        }

        nav a:hover::after {
            width: 100%;
        }

        /* --- MAIN & SECTIONS --- */
        main {
            padding: 0;
            max-width: 100%;
            margin: 0 auto;
        }

        section {
            padding: 4rem 10%; /* Padding kiri kanan lebih besar */
            background-color: transparent;
            border-radius: 0;
            box-shadow: none;
            margin-bottom: 0;
        }

        h2 {
            color: var(--primary-dark);
            font-size: 2rem;
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
            border-bottom: none;
        }
        
        h2::after {
            content: '';
            display: block;
            width: 60px;
            height: 4px;
            background: var(--primary);
            margin: 10px auto 0;
            border-radius: 2px;
        }

        /* --- HERO SECTION --- */
        #hero {
            text-align: center;
            padding: 8rem 2rem 6rem;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            position: relative;
            overflow: hidden;
        }

        /* Hiasan background bulat */
        #hero::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 400px;
            height: 400px;
            background: rgba(37, 99, 235, 0.1);
            border-radius: 50%;
            z-index: 0;
        }

        #hero h2, #hero p, #hero .cta-buttons {
            position: relative;
            z-index: 1;
        }

        #hero h2 {
            font-size: 3rem;
            margin-bottom: 1rem;
            line-height: 1.2;
            color: #1e293b;
            animation: fadeInUp 0.8s ease-out;
        }
        
        #hero h2::after { display: none; } /* Hapus garis bawah di hero */

        #hero p {
            font-size: 1.25rem;
            color: #64748b;
            margin-bottom: 2.5rem;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            animation: fadeInUp 1s ease-out;
        }

        .cta-buttons {
            animation: fadeInUp 1.2s ease-out;
        }

        /* --- TOMBOL (BUTTONS) --- */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--primary);
            color: white;
            padding: 1rem 2.5rem;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            margin: 0.5rem;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); /* Bouncy effect */
        }

        .btn:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.4);
            background: var(--primary-dark);
        }

        .btn-secondary {
            background: white;
            color: var(--primary);
            border: 2px solid var(--primary);
            box-shadow: none;
        }

        .btn-secondary:hover {
            background: var(--primary);
            color: white;
        }

        .btn-special {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }
        
        .btn-special:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.4);
        }

        /* --- LAYANAN SECTION (GRID CARDS) --- */
        #layanan ul {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            padding: 1rem;
        }

        #layanan li {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            border-left: none; /* Hapus style lama */
            box-shadow: var(--shadow-sm);
            text-align: center;
            font-size: 1.1rem;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        
        /* Efek Hover Card */
        #layanan li:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        /* Icon Pura-pura dengan CSS Before */
        #layanan li::before {
            content: '🛠️';
            display: block;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            animation: float 3s ease-in-out infinite;
        }

        /* --- CARA KERJA (TIMELINE STYLE) --- */
        #cara-kerja {
            background-color: white; /* Bedakan background section */
        }

        #cara-kerja ol {
            max-width: 800px;
            margin: 0 auto;
            padding: 0;
            list-style: none;
            counter-reset: step-counter;
        }

        #cara-kerja li {
            position: relative;
            padding: 1.5rem 1.5rem 1.5rem 5rem;
            margin-bottom: 2rem;
            background: var(--bg-light);
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            transition: transform 0.3s ease;
        }
        
        #cara-kerja li:hover {
            transform: scale(1.02);
            background: #fff;
            box-shadow: var(--shadow-sm);
        }

        /* Style Angka Bulat Keren */
        #cara-kerja li::before {
            counter-increment: step-counter;
            content: counter(step-counter);
            position: absolute;
            left: -1rem; /* Keluar sedikit agar unik */
            top: 50%;
            transform: translateY(-50%);
            width: 3.5rem;
            height: 3.5rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
            border: 4px solid white;
        }

        #cara-kerja strong {
            display: block;
            color: var(--primary-dark);
            font-size: 1.1rem;
            margin-bottom: 0.2rem;
        }

        /* --- CTA SECTION --- */
        #cta {
            text-align: center;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: white;
            border-radius: 20px;
            margin: 4rem 5%;
            padding: 4rem 2rem;
            width: 90%;
        }

        #cta h2 {
            color: white;
            border-bottom: none;
        }
        
        #cta h2::after { background: var(--accent); }

        #cta p {
            color: #cbd5e1;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        /* --- FOOTER --- */
        footer {
            background-color: #0f172a;
            color: #94a3b8;
            text-align: center;
            padding: 3rem 1.5rem;
            margin-top: 0;
            border-top: 1px solid #1e293b;
        }

        /* --- RESPONSIVE --- */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                gap: 1rem;
            }
            
            nav ul {
                gap: 1rem;
                flex-wrap: wrap;
                justify-content: center;
            }

            #hero h2 { font-size: 2rem; }
            
            #cara-kerja li {
                padding-left: 1.5rem;
                padding-top: 4rem;
                text-align: center;
            }
            
            #cara-kerja li::before {
                left: 50%;
                top: 0;
                transform: translate(-50%, -50%);
            }
        }
    </style>
</head>
<body>

<?php
// PHP session_start() sudah ada di bagian atas
?>

<header>
    <h1>Service Elektronik ABC</h1>
    <nav>
        <ul>
            <li><a href="#tentang">Tentang</a></li>
            <li><a href="#layanan">Layanan</a></li>
            <li><a href="#cara-kerja">Cara Kerja</a></li>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <li><a href="views/auth/login.php" style="color: var(--primary); font-weight: bold;">Login</a></li>
            <?php else: ?>
                <li><a href="views/dashboard.php">Dashboard (<?= htmlspecialchars($_SESSION['name'] ?? 'User') ?>)</a></li>
                <li><a href="controllers/AuthController.php?action=logout" style="color: #ef4444;">Logout</a></li>
            <?php endif; ?>
            <li><a href="views/services/track_public.php">Track Service</a></li>
        </ul>
    </nav>
</header>

<main>
    <section id="hero">
        <h2>🚀 Solusi Perbaikan Elektronik Terpercaya</h2>
        <p>Layanan <strong>cepat, transparan, dan bergaransi</strong> untuk laptop, PC, dan gadget kesayangan Anda.</p>
        
        <div class="cta-buttons">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="views/customers/register.php" class="btn">Daftar Akun Baru</a>
                <a href="views/auth/login.php" class="btn btn-secondary">Login</a>
            <?php else: ?>
                <a href="views/services/create.php" class="btn btn-special">Buat Service Baru</a>
            <?php endif; ?>
            <a href="views/services/track_public.php" class="btn btn-secondary">Cek Status Service</a>
        </div>
    </section>

    <section id="tentang" class="reveal">
        <h2>Tentang Kami</h2>
        <p style="text-align: center; max-width: 800px; margin: 0 auto; font-size: 1.1rem;">
            Kami adalah layanan perbaikan elektronik terpercaya sejak <strong>2020</strong>. Didukung oleh <strong>tim teknisi berpengalaman</strong> dan <strong>proses yang transparan</strong>, kami menjamin kepuasan pelanggan dan perangkat Anda kembali prima.
        </p>
    </section>

    <section id="layanan" class="reveal">
        <h2>⚙️ Layanan Kami</h2>
        <ul>
            <li>Perbaikan Laptop<br><small>Ganti LCD, Keyboard, Baterai</small></li>
            <li>Perbaikan Komputer<br><small>Rakitan, All-in-One, Upgrade</small></li>
            <li>Service Gadget<br><small>Smartphone, Tablet, Smartwatch</small></li>
            <li>Maintenance Berkala<br><small>Cleaning, Thermal Paste</small></li>
            <li>Software & OS<br><small>Instalasi Ulang, Recovery Data</small></li>
        </ul>
    </section>

    <section id="cara-kerja" class="reveal">
        <h2>Cara Kerja Service</h2>
        <ol>
            <li><strong>Daftar & Login</strong> Buat akun atau masuk jika sudah terdaftar untuk akses penuh.</li>
            <li><strong>Isi Formulir Service</strong> Jelaskan detail kerusakan perangkat Anda secara online.</li>
            <li><strong>Drop-off Perangkat</strong> Serahkan perangkat ke lokasi kami atau gunakan layanan pickup.</li>
            <li><strong>Track Progress</strong> Pantau status perbaikan (Pending, Proses, Selesai) real-time.</li>
            <li><strong>Ambil & Bayar</strong> Perangkat prima kembali, lakukan pembayaran dengan mudah.</li>
        </ol>
    </section>

    <section id="cta" class="reveal">
        <h2>✅ Siap untuk Service?</h2>
        <p>Jangan biarkan produktivitas terhambat. Kami siap membantu Anda sekarang!</p>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="views/customers/register.php" class="btn btn-special">Daftar Sekarang</a>
            <a href="views/auth/login.php" class="btn">Login</a>
        <?php else: ?>
            <a href="views/services/create.php" class="btn btn-special">Buat Service Baru</a>
            <a href="views/services/list.php" class="btn">History Service</a>
        <?php endif; ?>
    </section>
</main>

<footer>
    <p>&copy; 2025 Service Elektronik ABC. <br> Hak Cipta Dilindungi. | Dibuat dengan ❤️ oleh Tim Teknis</p>
</footer>

<script>
    // Fungsi untuk mengecek elemen yang masuk viewport
    function reveal() {
        var reveals = document.querySelectorAll(".reveal");

        for (var i = 0; i < reveals.length; i++) {
            var windowHeight = window.innerHeight;
            var elementTop = reveals[i].getBoundingClientRect().top;
            var elementVisible = 150; // Jarak trigger dari bawah

            if (elementTop < windowHeight - elementVisible) {
                reveals[i].classList.add("active");
            } else {
                // Opsional: Hapus else ini jika ingin animasi hanya sekali
                // reveals[i].classList.remove("active");
            }
        }
    }

    // Jalankan saat scroll
    window.addEventListener("scroll", reveal);
    
    // Jalankan sekali saat load agar section atas langsung muncul
    reveal();
</script>

</body>
</html>