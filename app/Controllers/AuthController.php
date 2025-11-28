<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class AuthController extends BaseController
{
    public function index()
    {
        // Si ya está autenticado, redirigir al dashboard
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    public function login()
    {
        $session = session();
        $userModel = new UserModel();

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $user = $userModel->getUserByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            // Actualizar último login
            $userModel->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);

            // Crear sesión
            $sessionData = [
                'id'          => $user['id'],
                'name'        => $user['name'],
                'email'       => $user['email'],
                'role_id'     => $user['role_id'],
                'role_name'   => $user['role_name'],
                'company_id'  => $user['company_id'] ?? null,
                'isLoggedIn'  => true,
            ];
            $session->set($sessionData);

            return redirect()->to('/dashboard');
        } else {
            $session->setFlashdata('error', 'Email o contraseña incorrectos');
            return redirect()->back()->withInput();
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
