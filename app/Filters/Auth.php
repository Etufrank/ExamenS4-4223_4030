<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Auth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Ne pas bloquer les pages de login et d'inscription
        $uri = $request->getUri()->getPath();
        if (strpos($uri, 'client/login') !== false || strpos($uri, 'client/do-login') !== false) {
            return;
        }

        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/client/login')->with('error', 'Veuillez vous connecter.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Rien
    }
}