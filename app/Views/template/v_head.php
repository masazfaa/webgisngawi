<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/x-icon" href="<?php echo base_url('favicon.ico'); ?>">
  <title>WebGIS 3D Kota - Tesis Azfa</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
      @media (min-width: 576px) {
    .container, .container-sm {
        max-width: 100vw;
    }}
    
    body {
      font-family: 'Poppins', sans-serif;
      margin: 0;
      padding: 0;
      height: 100vh;
      display: flex;
      flex-direction: column;
      background-color: #f9fafb;
      color: #333;
    }

    header {
      background-color: #4f46e5;
      padding: 10px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      color: white;
      height: 10vh;
    }

    header .logo-container {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    header .logo-container img {
      width: 60px;
      height: 60px;
      border-radius: 50%;
    }

    header .logo-container button {
      background-color: transparent;
      border: none;
      color: white;
      font-size: 20px;
      cursor: pointer;
    }

    header .title {
      font-size: 20px;
      font-weight: 600;
    }

    .nav-panel {
      position: fixed;
      top: 0;
      left: -250px;
      width: 250px;
      height: 100%;
      background-color: #4f46e5;
      color: white;
      box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
      overflow-y: auto;
      transition: left 0.3s ease;
      z-index: 1000;
    }

    .nav-panel.open {
      left: 0;
    }

    .nav-panel .close-btn {
      text-align: right;
      padding: 10px;
    }

    .nav-panel .close-btn button {
      background-color: transparent;
      border: none;
      color: white;
      font-size: 20px;
      cursor: pointer;
    }

    .nav-panel ul {
      list-style: none;
      padding: 20px;
      margin: 0;
    }

    .nav-panel ul li {
      margin-bottom: 15px;
    }

    .nav-panel ul li a {
      color: white;
      text-decoration: none;
      font-size: 16px;
      font-weight: 500;
    }

    .nav-panel ul li a:hover {
      text-decoration: underline;
    }

    .container {
      height:80vh;
      flex: 1;
      padding: 20px;
      background: white;
      border-radius: 8px;
      box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .container h1 {
      font-size: 24px;
      font-weight: 600;
      margin-bottom: 20px;
      color: #4f46e5;
    }

    .d-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
    }
    .w-25 {
      max-width: 300px; /* Atur lebar maksimal kolom pencarian */
    }
    .btn-primary {
      margin-right: 10px; /* Tambahkan jarak opsional jika diperlukan */
    }

    .button {
      display: inline-block;
      padding: 8px 15px;
      background-color: #4f46e5;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 500;
      text-decoration: none;
    }

    .table-wrapper {
      flex: 1;
      border: 1px solid #ddd;
      border-radius: 8px;
      box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
      overflow-y: auto;
      overflow-x: auto; /* Tambahkan horizontal scroll */
      max-height: 55vh; /* Tetap atur tinggi maksimum */
    }


    table {
      width: 100%;
      border-collapse: collapse;
    }

    table th, table td {
      padding: 10px;
      text-align: left;
      border-bottom: 1px solid #ddd;
      font-size: 11px;
    }

    table th {
      background-color: #4f46e5;
      color: white;
    }

    /* Tombol Edit dan Delete */
    .btn-action {
    font-size: 10px; /* Ukuran font kecil */
    padding: 3px 8px; /* Padding lebih kecil */
    }

    /* Untuk memastikan tombol selalu sejajar */
    .btn-group .btn {
    display: inline-block;
    margin-right: 2px; /* Jarak antar tombol */
    }

    .btn-group .btn:last-child {
    margin-right: 0; /* Tidak ada jarak untuk tombol terakhir */
    }


    .pagination .page-link {
      background-color: #4f46e5;
      color: white;
      border: 1px solid #4f46e5;
    }

    .pagination .page-link:hover {
      background-color: #4338ca;
      color: white;
    }

    .pagination .page-item.active .page-link {
      background-color: #4338ca;
      border-color: #4338ca;
    }

    footer {
      text-align: center;
      background-color: #4f46e5;
      color: white;
      height: 10vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }
  </style>
</head>