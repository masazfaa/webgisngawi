<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - WebGIS Ngawi</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #4f46e5;      /* Warna Utama (Indigo) */
            --dark-bg: #0f172a;      /* Warna Header */
            --sidebar-width: 280px;  /* Lebar Navigasi */
            --header-height: 70px;   /* Tinggi Header */
            --bg-body: #f1f5f9;      /* Warna Latar Belakang Abu Muda */
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: #334155;
            overflow-x: hidden;
            padding-top: var(--header-height); /* Memberi ruang agar konten tidak tertutup header fixed */
        }

        /* --- 2. HEADER PROFESSIONAL --- */
        .app-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--header-height);
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            z-index: 1040;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-img {
            height: 40px;
            width: auto;
            object-fit: contain;
        }

        .app-title {
            font-weight: 700;
            font-size: 1.1rem;
            color: #1e293b;
            letter-spacing: -0.5px;
        }
        
        .app-subtitle {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 500;
            display: block;
            margin-top: -3px;
        }

        /* Tombol Toggle Menu */
        .menu-toggle {
            background: transparent;
            border: none;
            font-size: 1.4rem;
            color: #64748b;
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: 0.2s;
        }
        .menu-toggle:hover {
            background-color: #f1f5f9;
            color: var(--primary);
        }

        /* --- 3. SIDE NAVIGATION (DRAWER) --- */
        .nav-backdrop {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.4); /* Backdrop gelap transparan */
            backdrop-filter: blur(2px);
            z-index: 1045;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        .nav-backdrop.active {
            opacity: 1;
            visibility: visible;
        }

        .nav-panel {
            position: fixed;
            top: 0;
            left: -300px; /* Sembunyi di kiri */
            width: var(--sidebar-width);
            height: 100vh;
            background: #ffffff;
            z-index: 1050;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 10px 0 25px -5px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }

        .nav-panel.open {
            transform: translateX(300px);
        }

        .nav-header {
            height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            border-bottom: 1px solid #e2e8f0;
        }

        .nav-links {
            flex: 1;
            padding: 20px 10px;
            overflow-y: auto;
            list-style: none;
            margin: 0;
        }

        .nav-links li { margin-bottom: 5px; }

        .nav-links a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #475569;
            text-decoration: none;
            font-weight: 500;
            border-radius: 8px;
            transition: 0.2s;
        }

        .nav-links a i {
            width: 25px;
            text-align: center;
            margin-right: 10px;
            font-size: 1.1rem;
        }

        .nav-links a:hover {
            background-color: #f1f5f9;
            color: var(--primary);
        }

        .nav-links a.active {
            background-color: #eff6ff; /* Biru sangat muda */
            color: var(--primary);
            font-weight: 600;
        }

        .nav-footer {
            padding: 20px;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .btn-logout {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 10px;
            border: 1px solid #e2e8f0;
            background: white;
            color: #ef4444;
            border-radius: 8px;
            font-weight: 600;
            transition: 0.2s;
            text-decoration: none;
        }
        .btn-logout:hover {
            background: #fef2f2;
            border-color: #ef4444;
        }

        /* --- 4. MAIN CONTENT AREA --- */
        .main-wrapper {
            padding: 25px 0;
            min-height: calc(100vh - var(--header-height) - 60px); /* Kurangi header & footer */
        }

        .page-header {
            margin-bottom: 25px;
        }
        
        .page-title {
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            font-size: 1.75rem;
        }

        /* Footer Halaman */
        .app-footer {
            text-align: center;
            padding: 20px;
            color: #94a3b8;
            font-size: 0.85rem;
            margin-top: auto;
        }

        /* --- RESPONSIF --- */
        @media (max-width: 576px) {
            .app-title { display: none; } /* Sembunyikan judul panjang di HP */
            .logo-area::after {
                content: "WebGIS";
                font-weight: 700;
                font-size: 1.1rem;
                margin-left: 5px;
            }
            .page-title { font-size: 1.4rem; }
            .main-wrapper { padding: 15px 0; }
        }
    </style>
</head>