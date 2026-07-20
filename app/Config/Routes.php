<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'HomeController::index');

$routes->group('admin', function($routes) {
    $routes->get('prefixes', 'AdminController::prefixes');
    $routes->post('ajouter-prefixe', 'AdminController::ajouterPrefixe');
    $routes->get('supprimer-prefixe/(:num)', 'AdminController::supprimerPrefixe/$1');

    $routes->get('types-operations', 'AdminController::typesOperations');
    $routes->post('ajouter-type', 'AdminController::ajouterType');
    $routes->get('supprimer-type/(:num)', 'AdminController::supprimerType/$1');

    $routes->get('baremes', 'AdminController::baremes');
    $routes->post('ajouter-bareme', 'AdminController::ajouterBareme');
    $routes->get('modifier-bareme/(:num)', 'AdminController::modifierBareme/$1');
    $routes->post('mettre-a-jour-bareme/(:num)', 'AdminController::mettreAJourBareme/$1');
    $routes->get('supprimer-bareme/(:num)', 'AdminController::supprimerBareme/$1');

    $routes->get('gains', 'AdminController::gains');
    $routes->get('clients', 'AdminController::clients');
});

$routes->get('client/login', 'ClientController::login');
$routes->post('client/do-login', 'ClientController::doLogin');
$routes->get('client/logout', 'ClientController::logout');

$routes->group('client', ['filter' => 'auth'], function($routes) {
    $routes->get('dashboard', 'ClientController::dashboard');
    $routes->get('depot', 'ClientController::depot');
    $routes->post('do-depot', 'ClientController::doDepot');
    $routes->get('retrait', 'ClientController::retrait');
    $routes->post('do-retrait', 'ClientController::doRetrait');
    $routes->get('transfert', 'ClientController::transfert');
    $routes->post('do-transfert', 'ClientController::doTransfert');
    $routes->get('historique', 'ClientController::historique');
});