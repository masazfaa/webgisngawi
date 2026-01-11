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
// Save Grup
    $routes->post('saveGrup', 'GeospasialController::saveGrup');
    $routes->get('deleteGrup/(:num)', 'GeospasialController::deleteGrup/$1');
    $routes->post('importGeoJSONGrup', 'GeospasialController::importGeoJSONGrup');
    $routes->get('exportGeoJSON/(:num)', 'GeospasialController::exportGeoJSON/$1');

    // Save Data Item (Dinamis: line, point, polygon)
    // (:alpha) akan menangkap kata 'line', 'point', atau 'polygon'
    $routes->post('save/(:alpha)', 'GeospasialController::save/$1'); 
    
    // Delete Data Item
    $routes->get('delete/(:alpha)/(:num)', 'GeospasialController::delete/$1/$2');
    
    // Get Detail (AJAX)
    $routes->get('getDetail/(:alpha)/(:num)', 'GeospasialController::getDetail/$1/$2');
    
    // Delete PDF
    $routes->post('deletePdf/(:num)', 'GeospasialController::deletePdf/$1');

    // Route untuk Export GeoJSON
    // (:num) memastikan parameter yang diterima hanya angka (ID Grup)
    $routes->get('geospasial/exportGeoJSON/(:num)', 'Geospasial::exportGeoJSON/$1');
});