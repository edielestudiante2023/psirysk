# PsyRisk - Sistema de Evaluacion de Riesgo Psicosocial

**Empresa:** Cycloid Talent
**Plataforma para la gestion y evaluacion de baterias de riesgo psicosocial** segun Resolucion 2404/2019 y Decreto 1072 (normativa colombiana).

---

## Stack tecnologico

| Componente | Tecnologia |
|------------|-----------|
| Backend | PHP 8.1+ / CodeIgniter 4 |
| Base de datos | MySQL 8 (DigitalOcean Managed, SSL required) |
| Servidor web | Nginx (Ubuntu 24.04) |
| Email | SendGrid API v3 (SMTP relay) |
| PDF | DOMPDF 3.1 (informes ejecutivos, graficos SVG gauge) |
| Excel | PhpSpreadsheet 5.2 |
| IA | OpenAI GPT-4o (interpretaciones, secciones de informe, recomendaciones) |
| Testing | PHPUnit 10.5 |
| Frontend | Bootstrap + JavaScript vanilla |

---

## Modulos principales (15)

| Modulo | Descripcion |
|--------|------------|
| Autenticacion | Login, recuperacion de password, gestion de sesiones |
| Baterias de servicio | Creacion y gestion de baterias de evaluacion psicosocial |
| Trabajadores | CRUD de trabajadores, asignacion a baterias, importacion CSV masiva |
| Evaluacion (Assessment) | Formularios de 4 dimensiones: Intralaboral A/B, Extralaboral, Estres |
| Resultados y reportes | Analisis consolidado, mapas de calor, informes ejecutivos |
| Resultados individuales | Solicitudes de acceso a resultados individuales con aprobacion |
| Maximo riesgo | Identificacion automatica de trabajadores en riesgo critico |
| PDF ejecutivo | Generacion de informes PDF con graficos gauge SVG |
| PDF nativo | Generacion alternativa de PDF con wkhtmltopdf |
| Demograficos | Analisis e interpretacion por segmentos demograficos |
| Recomendaciones IA | Planes de accion generados por IA segun dimension de riesgo |
| Encuesta satisfaccion | Feedback post-evaluacion de servicios |
| Bateria publica | Acceso grupal via QR/link sin necesidad de email |
| Comercial | Gestion de ordenes de venta (equipo Gladiator) |
| Portal cliente | Dashboard readonly para empresas clientes |

---

## Roles de usuario

| Rol | Acceso |
|-----|--------|
| superadmin | Todo el sistema + gestion de usuarios + configuracion |
| consultor | Gestion de empresas asignadas + baterias + reportes + IA completo |
| cliente_gestor | Readonly reportes (gestiona multiples empresas) |
| cliente_empresa | Readonly reportes + historial (una empresa) |
| director_comercial | Gestion de ordenes, empresas, baterias, reportes, satisfaccion |

---

## Estructura del proyecto

```
psyrisk/
├── app/
│   ├── Commands/          # 7 comandos spark (cron jobs, recalculos)
│   ├── Config/            # Routes.php, Database.php, Email.php, scoring configs
│   ├── Controllers/       # 25 controladores principales
│   │   ├── PdfEjecutivo/  # 10 controladores de informe ejecutivo
│   │   └── PdfNativo/     # 2 controladores de PDF nativo
│   ├── Libraries/         # 4 librerias de scoring (Intralaboral A/B, Extralaboral, Estres)
│   ├── Models/            # 21 modelos
│   ├── Services/          # 5 servicios (OpenAI, Email, PDF, Demographics, Report)
│   └── Views/             # 27 directorios de vistas
├── docs/                  # Documentacion tecnica
├── public/                # Punto de entrada web (index.php)
│   ├── assets/            # JSON geografico, templates de imagen
│   ├── js/                # Modulos JS (inline editing, satisfaction, filters)
│   └── uploads/           # Archivos subidos por usuarios
├── scripts/               # Scripts utilitarios
├── tests/                 # Tests PHPUnit
├── writable/              # Logs, cache, sesiones, PDFs generados, fonts
├── .env                   # Variables de entorno (NO commitear)
├── .env.example           # Template de variables (SI commitear)
├── CONTRIBUTING.md        # Guia de contribucion
├── README.md              # Este archivo
├── composer.json          # Dependencias PHP
└── spark                  # CLI de CodeIgniter
```

---

## Requisitos previos

- PHP >= 8.1 con extensiones: intl, mbstring, json, mysqlnd, curl, gd
- MySQL 8.x
- Composer 2.x
- wkhtmltopdf (opcional, para PDF nativo)
- Node.js (no requerido actualmente)

---

## Instalacion local

```bash
# 1. Clonar el repositorio
git clone https://github.com/edielestudiante2023/psirysk.git psyrisk
cd psyrisk

# 2. Instalar dependencias PHP
composer install

# 3. Configurar variables de entorno
cp .env.example .env
# Editar .env con credenciales locales

# 4. Ejecutar migraciones
php spark migrate

# 5. Ejecutar seeders (roles, usuarios iniciales)
php spark db:seed RoleSeeder
php spark db:seed UserSeeder
php spark db:seed ConsultantSeeder

# 6. Iniciar servidor de desarrollo
php spark serve
# Acceder en http://localhost:8080
```

---

## Variables de entorno

| Variable | Descripcion |
|----------|------------|
| `CI_ENVIRONMENT` | Entorno: development / production |
| `app_baseURL` | URL base de la aplicacion |
| `database.default.*` | Credenciales de base de datos principal |
| `encryption.key` | Clave de encriptacion (hex2bin) |
| `email.SMTPPass` | API Key de SendGrid |
| `email.fromEmail` | Email remitente de notificaciones |
| `OPENAI_API_KEY` | API Key de OpenAI para generacion IA |
| `OPENAI_MODEL` | Modelo de OpenAI (default: gpt-4o) |
| `DEBUG_SAVE_VERIFICATION` | Modo debug de verificacion de datos |

---

## Cron jobs

| Comando | Frecuencia | Descripcion |
|---------|-----------|------------|
| `php spark reminders:send` | Diario | Recordatorios de baterias (30 dias, 7 dias, vencidas) |

---

## Deploy

El deploy se realiza via SSH al servidor de produccion:

```bash
ssh root@66.29.154.174
cd /www/wwwroot/psirysk
git pull origin main
composer install --no-dev --optimize-autoloader
php spark migrate
```

---

## Documentacion adicional

- [docs/PERIODICIDAD_NORMATIVA.md](docs/PERIODICIDAD_NORMATIVA.md) — Requisitos normativos de periodicidad
- [docs/PLAN_PERIODICIDAD_PSYRISK.md](docs/PLAN_PERIODICIDAD_PSYRISK.md) — Plan de periodicidad del sistema
- [docs/PLAN_MODULO_IA_MAXIMO_RIESGO.md](docs/PLAN_MODULO_IA_MAXIMO_RIESGO.md) — Modulo IA de maximo riesgo
- [docs/migracion-sendgrid-api.md](docs/migracion-sendgrid-api.md) — Migracion a SendGrid API
