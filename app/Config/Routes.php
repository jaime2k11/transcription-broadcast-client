<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'TranscriptionClient::index');
$routes->get('/translate', 'TranscriptionClient::translate');
$routes->post('/translate-api', 'TranslationApi::translate');

