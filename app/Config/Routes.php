<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
// $routes->get('data', 'Home::data');
// Halaman Utama
$routes->get('geospasial', 'GeospasialController::geospasial');

// CRUD Grup (Style)
$routes->post('geospasial/saveGrup', 'GeospasialController::saveGrup');
$routes->get('geospasial/deleteGrup/(:num)', 'GeospasialController::deleteGrup/$1');

// CRUD Data (Polygon/Line/Point)
$routes->post('geospasial/save/(:segment)', 'GeospasialController::save/$1');
$routes->get('geospasial/delete/(:segment)/(:num)', 'GeospasialController::delete/$1/$2');