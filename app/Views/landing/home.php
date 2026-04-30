<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>psyrisk — Plataforma white-label de Batería de Riesgo Psicosocial</title>
    <meta name="description" content="Aplica la Batería de Riesgo Psicosocial Mintrabajo automáticamente. Sin instalaciones. White-label para psicólogos y consultoras.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; }
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; padding: 100px 0 80px;
        }
        .hero h1 { font-size: 3rem; font-weight: 700; }
        .hero p.lead { font-size: 1.3rem; opacity: 0.95; }
        .feature-card { border: none; box-shadow: 0 5px 20px rgba(0,0,0,0.06); border-radius: 15px; transition: transform 0.3s; }
        .feature-card:hover { transform: translateY(-5px); }
        .feature-icon { font-size: 2.5rem; color: #667eea; }
        .pricing-card { border: 2px solid #e9ecef; border-radius: 15px; transition: all 0.3s; }
        .pricing-card.featured { border-color: #667eea; transform: scale(1.05); }
        .pricing-card:hover { border-color: #764ba2; }
        .price { font-size: 2.5rem; font-weight: 700; color: #667eea; }
        .navbar-light { background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        footer { background: #2d3748; color: white; padding: 40px 0; }
        footer a { color: #cbd5e0; text-decoration: none; }
        footer a:hover { color: white; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?= base_url('/') ?>">
            <img src="<?= base_url('images/logos/logo_psyrisk.png') ?>" alt="psyrisk" style="height: 40px;">
        </a>
        <div class="ms-auto">
            <a href="#features" class="nav-link d-inline-block">Ventajas</a>
            <a href="#pricing" class="nav-link d-inline-block">Precios</a>
            <a href="<?= base_url('login') ?>" class="btn btn-link">Iniciar sesión</a>
            <a href="<?= base_url('signup') ?>" class="btn btn-primary">Crear cuenta gratis</a>
        </div>
    </div>
</nav>

<section class="hero">
    <div class="container text-center">
        <h1>La Batería de Riesgo Psicosocial,<br>en piloto automático.</h1>
        <p class="lead mt-3">Aplica, tabula y reporta evaluaciones del Mintrabajo en minutos. Marca blanca para psicólogos y consultoras.</p>
        <div class="mt-4">
            <a href="<?= base_url('signup') ?>" class="btn btn-light btn-lg me-2"><strong>Empieza gratis 14 días</strong></a>
            <a href="#pricing" class="btn btn-outline-light btn-lg">Ver planes</a>
        </div>
        <p class="mt-3 small opacity-75">Sin tarjeta de crédito · Cumple Resolución 2764/2022 · Ley 1581</p>
    </div>
</section>

<section id="features" class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Todo lo que necesitas para operar la batería</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card p-4 h-100 text-center">
                    <div class="feature-icon mb-3"><i class="fas fa-clipboard-check"></i></div>
                    <h5>Batería oficial Mintrabajo</h5>
                    <p>Cuestionarios intralaboral A/B, extralaboral y estrés con baremos validados. Listos para usar.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card p-4 h-100 text-center">
                    <div class="feature-icon mb-3"><i class="fas fa-chart-line"></i></div>
                    <h5>Reportes automáticos</h5>
                    <p>Dashboards, mapas de calor, ficha sociodemográfica y conclusión total en minutos. Exportables a PDF y Excel.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card p-4 h-100 text-center">
                    <div class="feature-icon mb-3"><i class="fas fa-palette"></i></div>
                    <h5>White-label completo</h5>
                    <p>Tu logo, tus colores, tus datos. Tus clientes ven tu marca, no la nuestra.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card p-4 h-100 text-center">
                    <div class="feature-icon mb-3"><i class="fas fa-shield-alt"></i></div>
                    <h5>Cumplimiento Ley 1581</h5>
                    <p>Aislamiento por tenant, autorización informada, registro de habeas data. Listo para SIC.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card p-4 h-100 text-center">
                    <div class="feature-icon mb-3"><i class="fas fa-bolt"></i></div>
                    <h5>Importación masiva</h5>
                    <p>Sube CSV con 500 trabajadores y la plataforma envía los enlaces, hace seguimiento y consolida.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card p-4 h-100 text-center">
                    <div class="feature-icon mb-3"><i class="fas fa-credit-card"></i></div>
                    <h5>Pago flexible</h5>
                    <p>Suscripción mensual + créditos por uso. Sin cláusulas de permanencia.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="pricing" class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-3">Planes simples y transparentes</h2>
        <p class="text-center text-muted mb-5">Sin permanencia. Cancelas cuando quieras. Crédito = 1 trabajador evaluado.</p>
        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <div class="pricing-card p-4 text-center h-100 bg-white">
                    <h5>Inicial</h5>
                    <p class="text-muted small">Para psicólogos independientes</p>
                    <div class="price">$79.000<small class="text-muted">/mes</small></div>
                    <p class="mb-0"><strong>10 evaluaciones</strong> incluidas</p>
                    <p class="text-muted">Crédito extra: $3.800</p>
                    <hr>
                    <ul class="list-unstyled text-start">
                        <li>✓ Batería completa Mintrabajo</li>
                        <li>✓ Reportes y dashboards</li>
                        <li>✓ Marca propia (logo + colores)</li>
                        <li>✓ Soporte por email</li>
                    </ul>
                    <a href="<?= base_url('signup') ?>?plan=inicial" class="btn btn-outline-primary w-100 mt-3">Empezar gratis</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="pricing-card featured p-4 text-center h-100 bg-white">
                    <span class="badge bg-primary mb-2">MÁS POPULAR</span>
                    <h5>Profesional</h5>
                    <p class="text-muted small">Para consultoras pequeñas</p>
                    <div class="price">$199.000<small class="text-muted">/mes</small></div>
                    <p class="mb-0"><strong>50 evaluaciones</strong> incluidas</p>
                    <p class="text-muted">Crédito extra: $3.500</p>
                    <hr>
                    <ul class="list-unstyled text-start">
                        <li>✓ Todo lo del plan Inicial</li>
                        <li>✓ Conclusión IA (riesgo total)</li>
                        <li>✓ Importación masiva CSV</li>
                        <li>✓ Soporte prioritario</li>
                    </ul>
                    <a href="<?= base_url('signup') ?>?plan=profesional" class="btn btn-primary w-100 mt-3">Empezar gratis</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="pricing-card p-4 text-center h-100 bg-white">
                    <h5>Empresarial</h5>
                    <p class="text-muted small">Para consultoras grandes</p>
                    <div class="price">$499.000<small class="text-muted">/mes</small></div>
                    <p class="mb-0"><strong>200 evaluaciones</strong> incluidas</p>
                    <p class="text-muted">Crédito extra: $3.000</p>
                    <hr>
                    <ul class="list-unstyled text-start">
                        <li>✓ Todo lo del plan Profesional</li>
                        <li>✓ Múltiples consultores</li>
                        <li>✓ Onboarding dedicado</li>
                        <li>✓ SLA y soporte telefónico</li>
                    </ul>
                    <a href="<?= base_url('signup') ?>?plan=empresarial" class="btn btn-outline-primary w-100 mt-3">Empezar gratis</a>
                </div>
            </div>
        </div>
        <p class="text-center text-muted mt-4 small">10% más económico que la competencia · Pagos vía Wompi (PCI-DSS)</p>
    </div>
</section>

<section class="py-5 text-center">
    <div class="container">
        <h2>¿Listo para automatizar tu batería?</h2>
        <p class="lead text-muted">14 días gratis. Sin tarjeta. Cancelas cuando quieras.</p>
        <a href="<?= base_url('signup') ?>" class="btn btn-primary btn-lg mt-3">Crear mi cuenta gratis →</a>
    </div>
</section>

<footer>
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <img src="<?= base_url('images/logos/logo_psyrisk.png') ?>" alt="psyrisk" style="height: 40px; filter: brightness(0) invert(1);">
                <p class="mt-3 small">Plataforma de evaluación de Riesgo Psicosocial automatizada para Colombia.</p>
            </div>
            <div class="col-md-3">
                <h6>Producto</h6>
                <ul class="list-unstyled small">
                    <li><a href="#features">Ventajas</a></li>
                    <li><a href="#pricing">Precios</a></li>
                    <li><a href="<?= base_url('signup') ?>">Crear cuenta</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h6>Legal</h6>
                <ul class="list-unstyled small">
                    <li><a href="<?= base_url('legal/politica') ?>">Política de Tratamiento de Datos</a></li>
                    <li><a href="<?= base_url('legal/aviso') ?>">Aviso de Privacidad</a></li>
                    <li><a href="<?= base_url('legal/terminos') ?>">Términos de Servicio</a></li>
                </ul>
            </div>
            <div class="col-md-2">
                <h6>Operado por</h6>
                <p class="small">CYCLOID TALENT SAS<br>NIT 901.653.912<br>Bogotá, Colombia</p>
            </div>
        </div>
        <hr style="opacity: 0.2;">
        <p class="text-center small mb-0">© <?= date('Y') ?> Cycloid Talent SAS · Todos los derechos reservados.</p>
    </div>
</footer>

</body>
</html>
