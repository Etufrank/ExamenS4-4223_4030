<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Page d'accueil
$routes->get('/', 'Home::index');

// Routes Admin (opérateur)
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

// Routes Client (login public)
$routes->get('client/login', 'Client::login');
$routes->post('client/do-login', 'Client::doLogin');
$routes->get('client/logout', 'Client::logout');

// Routes Client (protégées par le filtre 'auth')
$routes->group('client', ['filter' => 'auth'], function($routes) {
    $routes->get('dashboard', 'Client::dashboard');
    $routes->get('depot', 'Client::depot');
    $routes->post('do-depot', 'Client::doDepot');
    $routes->get('retrait', 'Client::retrait');
    $routes->post('do-retrait', 'Client::doRetrait');
    $routes->get('transfert', 'Client::transfert');
    $routes->post('do-transfert', 'Client::doTransfert');
    $routes->get('historique', 'Client::historique');
});