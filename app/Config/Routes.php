<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */


$routes->get('/', 'Home::index');


$routes->group('admin', function($routes) {
    $routes->get('prefixes', 'Admin::prefixes');
    $routes->post('ajouter-prefixe', 'Admin::ajouterPrefixe');
    $routes->get('supprimer-prefixe/(:num)', 'Admin::supprimerPrefixe/$1');

    $routes->get('types-operations', 'Admin::typesOperations');
    $routes->post('ajouter-type', 'Admin::ajouterType');
    $routes->get('supprimer-type/(:num)', 'Admin::supprimerType/$1');

    $routes->get('baremes', 'Admin::baremes');
    $routes->post('ajouter-bareme', 'Admin::ajouterBareme');
    $routes->get('modifier-bareme/(:num)', 'Admin::modifierBareme/$1');
    $routes->post('mettre-a-jour-bareme/(:num)', 'Admin::mettreAJourBareme/$1');
    $routes->get('supprimer-bareme/(:num)', 'Admin::supprimerBareme/$1');

    $routes->get('gains', 'Admin::gains');
    $routes->get('clients', 'Admin::clients');
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