<?php

namespace App\Controllers;

/**
 * LandingController
 * Sitio comercial público de psyrisk:
 *   /home → landing con value prop, features, pricing, CTA signup
 *   /pricing → tabla detallada de precios
 *   /legal/politica → Política de tratamiento de datos (HTML del .md)
 *   /legal/aviso → Aviso de privacidad
 */
class LandingController extends BaseController
{
    public function home()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }
        return view('landing/home');
    }

    public function pricing()
    {
        return view('landing/pricing');
    }

    public function legalPolitica()
    {
        return view('landing/legal_politica');
    }

    public function legalAviso()
    {
        return view('landing/legal_aviso');
    }

    public function legalTerminos()
    {
        return view('landing/legal_terminos');
    }
}
