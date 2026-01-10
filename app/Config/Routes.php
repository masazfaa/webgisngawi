<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Halaman Utama & Dashboard
$routes->get('/', 'Home::index');
$routes->get('geospasial', 'GeospasialController::geospasial');

// --- MANAJEMEN GRUP (STYLE & KATEGORI) ---
$routes->group('geospasial', function($routes) {
    $routes->post('saveGrup', 'GeospasialController::saveGrup');
    $routes->get('deleteGrup/(:num)', 'GeospasialController::deleteGrup/$1');

    // --- MANAJEMEN DATA GEOMETRI (POLYGON/LINE/POINT) ---
    $routes->post('save/(:segment)', 'GeospasialController::save/$1');
    $routes->get('delete/(:segment)/(:num)', 'GeospasialController::delete/$1/$2');

    // --- MANAJEMEN FILE PDF ---
    // Route untuk hapus file PDF satuan via AJAX dari modal edit
    $routes->post('deletePdf/(:num)', 'GeospasialController::deletePdf/$1');
});