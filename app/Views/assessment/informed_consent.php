<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consentimiento Informado - PsyRisk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .consent-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        .consent-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 2.5rem;
            margin-bottom: 2rem;
        }
        .consent-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 3px solid #667eea;
        }
        .consent-header h1 {
            color: #667eea;
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        .consent-header .subtitle {
            color: #666;
            font-size: 1.1rem;
        }
        .consent-content {
            text-align: justify;
            line-height: 1.8;
            color: #333;
            font-size: 1.05rem;
            margin-bottom: 2.5rem;
        }
        .consent-content p {
            margin-bottom: 1.2rem;
        }
        .consent-content strong {
            color: #667eea;
            font-weight: 600;
        }
        .consent-footer {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
        }
        .consent-question {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1.5rem;
        }
        .btn-accept {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 15px 60px;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 50px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
        }
        .btn-accept:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.6);
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
        .btn-accept:active {
            transform: translateY(0);
        }
        .logo-container {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .logo-container i {
            font-size: 4rem;
            color: #667eea;
        }

        /* Optimizaciones para móviles */
        @media (max-width: 768px) {
            body {
                padding: 1rem 0;
            }
            .consent-card {
                border-radius: 15px;
                padding: 1.5rem;
            }
            .consent-header h1 {
                font-size: 1.5rem;
            }
            .consent-header .subtitle {
                font-size: 1rem;
            }
            .consent-content {
                font-size: 0.95rem;
                line-height: 1.6;
            }
            .consent-question {
                font-size: 1.1rem;
            }
            .btn-accept {
                padding: 12px 40px;
                font-size: 1.1rem;
            }
            .logo-container i {
                font-size: 3rem;
            }
        }

        /* Animación de entrada */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .consent-card {
            animation: fadeInUp 0.6s ease-out;
        }
    </style>
</head>
<body>
    <div class="consent-container">
        <div class="consent-card">
            <div class="logo-container">
                <i class="fas fa-shield-alt"></i>
            </div>

            <div class="consent-header">
                <h1>Consentimiento Informado</h1>
                <div class="subtitle">Evaluación de Riesgo Psicosocial</div>
            </div>

            <div class="consent-content">
                <p>
                    La aplicación de los siguientes cuestionarios tiene como objetivo <strong>diagnosticar el riesgo psicosocial</strong>, de acuerdo con las <strong>resoluciones 2764 de 2022, 2646 de 2008 y 2404 de 2019 del Ministerio de Trabajo</strong>, en la que establecen que todas las empresas deben identificar, evaluar, prevenir, intervenir y monitorear permanentemente la exposición a los factores de riesgo psicosocial en el trabajo.
                </p>

                <p>
                    Estos factores son condiciones que pueden ocasionar <strong>efectos negativos en la salud física y mental de los trabajadores</strong> y el desempeño laboral de los mismos.
                </p>

                <p>
                    Esta evaluación se realizará a través de <strong>cuatro (4) cuestionarios</strong> que miden Factores de Riesgo <strong>Intralaboral, Extralaboral, Individual y Estrés</strong>. Una vez recopilada la información, se tabulará, analizará y se entregará un informe grupal con el fin de establecer las medidas de control correspondientes.
                </p>

                <p>
                    En el cumplimiento de los objetivos propuestos, la información utilizada para la evaluación de Factores Psicosociales estará sometida a <strong>reserva, conforme lo establece la Ley 1090 de 2006</strong>; así como la protección de datos personales contenidas en la <strong>Ley Estatutaria 1581 de 2012, Decreto 1377 de 2013</strong>, y demás normas concordantes.
                </p>

                <p>
                    En consecuencia, los expertos evaluadores garantizarán el compromiso de usar la información obtenida, <strong>única y exclusivamente para los fines inherentes a la Seguridad y Salud en el Trabajo</strong>.
                </p>
            </div>

            <div class="consent-footer">
                <div class="consent-question">
                    ¿Acepta participar en esta evaluación de riesgo psicosocial?
                </div>

                <form id="consentForm" method="POST" action="<?= base_url('assessment/accept-consent') ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-accept" id="btnAccept">
                        <i class="fas fa-check-circle me-2"></i>
                        SÍ, Acepto
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('consentForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const btn = document.getElementById('btnAccept');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Procesando...';

            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Gracias!',
                        text: 'Su consentimiento ha sido registrado. Ahora será redirigido a los cuestionarios.',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        window.location.href = data.redirect;
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Hubo un error al procesar su consentimiento.'
                    });
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check-circle me-2"></i>SÍ, Acepto';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Hubo un error al procesar su solicitud.'
                });
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle me-2"></i>SÍ, Acepto';
            });
        });
    </script>
</body>
</html>
