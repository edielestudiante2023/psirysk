<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class PasswordResetController extends BaseController
{
    protected $userModel;
    protected $db;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->db = \Config\Database::connect();
        helper(['form', 'url']);
    }

    // Mostrar formulario para solicitar recuperación
    public function forgotPassword()
    {
        return view('auth/forgot_password');
    }

    // Procesar solicitud de recuperación y enviar email
    public function sendResetLink()
    {
        $rules = [
            'email' => 'required|valid_email',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');

        // Verificar si el email existe
        $user = $this->userModel->getUserByEmail($email);

        if (!$user) {
            return redirect()->back()->withInput()->with('error', 'No existe un usuario con ese correo electrónico');
        }

        // Generar token único
        $token = bin2hex(random_bytes(32));

        // Guardar token en la base de datos
        $passwordResetData = [
            'email' => $email,
            'token' => hash('sha256', $token),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        // Eliminar tokens anteriores del mismo email
        $this->db->table('password_resets')->where('email', $email)->delete();

        // Insertar nuevo token
        $this->db->table('password_resets')->insert($passwordResetData);

        // Enviar email con enlace de recuperación
        $this->sendResetEmail($email, $user['name'], $token);

        return redirect()->to('/login')->with('success', 'Se ha enviado un enlace de recuperación a tu correo electrónico');
    }

    // Mostrar formulario para establecer nueva contraseña
    public function resetPassword($token)
    {
        // Verificar que el token existe y no ha expirado (24 horas)
        $hashedToken = hash('sha256', $token);
        $reset = $this->db->table('password_resets')
            ->where('token', $hashedToken)
            ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')))
            ->get()
            ->getRowArray();

        if (!$reset) {
            return redirect()->to('/login')->with('error', 'El enlace de recuperación es inválido o ha expirado');
        }

        $data = [
            'title' => 'Restablecer Contraseña',
            'token' => $token,
            'email' => $reset['email'],
        ];

        return view('auth/reset_password', $data);
    }

    // Procesar nueva contraseña
    public function updatePassword()
    {
        $rules = [
            'token' => 'required',
            'email' => 'required|valid_email',
            'password' => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $token = $this->request->getPost('token');
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // Verificar token
        $hashedToken = hash('sha256', $token);
        $reset = $this->db->table('password_resets')
            ->where('token', $hashedToken)
            ->where('email', $email)
            ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')))
            ->get()
            ->getRowArray();

        if (!$reset) {
            return redirect()->to('/login')->with('error', 'El enlace de recuperación es inválido o ha expirado');
        }

        // Actualizar contraseña
        $user = $this->userModel->getUserByEmail($email);
        if (!$user) {
            return redirect()->to('/login')->with('error', 'Usuario no encontrado');
        }

        $this->userModel->update($user['id'], ['password' => $password]);

        // Eliminar token usado
        $this->db->table('password_resets')->where('email', $email)->delete();

        return redirect()->to('/login')->with('success', 'Contraseña actualizada exitosamente. Ya puedes iniciar sesión');
    }

    private function sendResetEmail($email, $name, $token)
    {
        $emailService = \Config\Services::email();

        $resetLink = base_url('password-reset/' . $token);

        $message = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; padding: 15px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
                .button:hover { background: #5568d3; }
                .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 5px; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                .info-box { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🔑 Recuperación de Contraseña</h1>
                    <p>Sistema PsyRisk</p>
                </div>

                <div class="content">
                    <h2>Hola ' . esc($name) . ',</h2>

                    <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en <strong>PsyRisk</strong>.</p>

                    <div class="info-box">
                        <p>Para establecer una nueva contraseña, haz clic en el siguiente botón:</p>
                        <div style="text-align: center;">
                            <a href="' . $resetLink . '" class="button">Restablecer Contraseña</a>
                        </div>
                        <p style="margin-top: 20px; font-size: 12px; color: #666;">O copia y pega el siguiente enlace en tu navegador:</p>
                        <p style="word-break: break-all; font-size: 12px; color: #667eea;">' . $resetLink . '</p>
                    </div>

                    <div class="warning">
                        <strong>⚠️ Importante:</strong>
                        <ul style="margin: 10px 0;">
                            <li>Este enlace expirará en 24 horas</li>
                            <li>Si no solicitaste este cambio, ignora este correo</li>
                            <li>Tu contraseña actual permanecerá activa hasta que establezcas una nueva</li>
                        </ul>
                    </div>

                    <p>Si tienes alguna duda o problema, por favor contacta al administrador.</p>
                </div>

                <div class="footer">
                    <p>Este es un correo automático, por favor no responder.</p>
                    <p>&copy; ' . date('Y') . ' PsyRisk - Cycloid Talent</p>
                </div>
            </div>
        </body>
        </html>
        ';

        $emailService->setFrom('noreply@cycloidtalent.com', 'PsyRisk - Cycloid Talent');
        $emailService->setTo($email);
        $emailService->setSubject('🔑 Recuperación de contraseña - PsyRisk');
        $emailService->setMailType('html');
        $emailService->setMessage($message);

        try {
            $emailService->send();
        } catch (\Exception $e) {
            log_message('error', 'Error enviando email de recuperación: ' . $e->getMessage());
        }
    }
}
