<?php

/**
 * tenant_helper.php
 * Helpers globales para acceder al contexto del tenant del usuario logueado.
 * Diseñados para usarse en layouts/views (main.php, report.php, email.php).
 *
 * Defaults seguros: si no hay tenant en sesión (ruta pública o platform admin),
 * todos los helpers devuelven valores de la marca psyrisk base.
 */

if (!function_exists('tenant_context')) {
    /**
     * Devuelve el tenant del usuario logueado (cacheado por request).
     * Null si no hay sesión, no hay tenant_id, o el tenant no existe.
     */
    function tenant_context(): ?array
    {
        static $cache = null;
        static $resolved = false;

        if ($resolved) {
            return $cache;
        }

        $resolved = true;

        if (!function_exists('session') || !session()->get('isLoggedIn')) {
            return $cache = null;
        }

        $tenantId = session()->get('tenant_id');
        if (!$tenantId) {
            return $cache = null;
        }

        $model = new \App\Models\TenantModel();
        $cache = $model->withoutTenantScope()->find($tenantId);
        return $cache;
    }
}

if (!function_exists('tenant_logo_url')) {
    /**
     * URL del logo del tenant. Si el tenant no tiene logo o no hay tenant,
     * devuelve el logo base de psyrisk.
     */
    function tenant_logo_url(): string
    {
        $tenant = tenant_context();
        if ($tenant && !empty($tenant['logo_path'])) {
            return base_url($tenant['logo_path']);
        }
        return base_url('images/logos/logo_psyrisk.png');
    }
}

if (!function_exists('tenant_brand_name')) {
    /**
     * Nombre comercial visible (para títulos, headers).
     */
    function tenant_brand_name(): string
    {
        $tenant = tenant_context();
        return $tenant['trade_name'] ?? 'psyrisk';
    }
}

if (!function_exists('tenant_legal_name')) {
    /**
     * Razón social legal (para footers, copyright, documentos formales).
     */
    function tenant_legal_name(): string
    {
        $tenant = tenant_context();
        return $tenant['legal_name'] ?? 'CYCLOID TALENT SAS';
    }
}

if (!function_exists('tenant_nit')) {
    function tenant_nit(): string
    {
        $tenant = tenant_context();
        return $tenant['nit'] ?? '901653912';
    }
}

if (!function_exists('tenant_primary_color')) {
    function tenant_primary_color(): string
    {
        $tenant = tenant_context();
        return $tenant['brand_primary_color'] ?? '#667eea';
    }
}

if (!function_exists('tenant_secondary_color')) {
    function tenant_secondary_color(): string
    {
        $tenant = tenant_context();
        return $tenant['brand_secondary_color'] ?? '#764ba2';
    }
}

if (!function_exists('tenant_contact_email')) {
    function tenant_contact_email(): string
    {
        $tenant = tenant_context();
        return $tenant['contact_email'] ?? 'head.consultant.cycloidtalent@gmail.com';
    }
}

if (!function_exists('tenant_pdf_footer_text')) {
    /**
     * Texto adicional al pie del PDF (configurable por tenant).
     */
    function tenant_pdf_footer_text(): string
    {
        $tenant = tenant_context();
        return $tenant['pdf_footer_text'] ?? 'Generado por psyrisk — plataforma de evaluación psicosocial';
    }
}

if (!function_exists('tenant_email_from_name')) {
    function tenant_email_from_name(): string
    {
        $tenant = tenant_context();
        return $tenant['email_from_name'] ?? 'psyrisk';
    }
}

if (!function_exists('tenant_email_from_address')) {
    function tenant_email_from_address(): string
    {
        $tenant = tenant_context();
        if ($tenant && !empty($tenant['email_from_address'])) {
            return $tenant['email_from_address'];
        }
        return 'no-reply@psyrisk.app';
    }
}

if (!function_exists('platform_logo_url')) {
    /**
     * Logo de la plataforma psyrisk (siempre el mismo, no varía por tenant).
     * Usar en footer de PDF como atribución "Powered by psyrisk".
     */
    function platform_logo_url(): string
    {
        return base_url('images/logos/logo_psyrisk.png');
    }
}
