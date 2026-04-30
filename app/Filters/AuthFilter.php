<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AuthFilter
 * Bloquea acceso a rutas protegidas si no hay sesión activa.
 * Reemplaza la verificación manual `if (!session()->get('isLoggedIn'))` repetida
 * en cada controlador.
 */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->get('isLoggedIn')) {
            // Petición AJAX/JSON: 401
            if ($request->hasHeader('X-Requested-With') &&
                strtolower($request->getHeaderLine('X-Requested-With')) === 'xmlhttprequest') {
                return service('response')
                    ->setStatusCode(401)
                    ->setJSON(['error' => 'No autenticado']);
            }
            return redirect()->to('/login')->with('error', 'Debes iniciar sesión');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No hace nada en el after.
    }
}
