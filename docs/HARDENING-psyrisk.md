# HARDENING DE REPOSITORIO — psyrisk

**Fecha:** 2026-04-05
**Aplicativo:** psyrisk — Sistema de Evaluacion de Riesgo Psicosocial
**Empresa:** Cycloid Talent
**Preparado para:** Edwin Lopez (consultor de infraestructura)

---

## TABLA DE CONTENIDO

1. Descripcion del aplicativo
2. Mapa de base de datos
3. Inventario de API Keys y servicios externos
4. Documentacion del proyecto (README, CONTRIBUTING, .env.example)
5. Ramas de trabajo
6. Pipelines CI/CD (Gitea)
7. Organizacion del repositorio
8. Hallazgos criticos y acciones pendientes

---

## 1. DESCRIPCION DEL APLICATIVO

### Stack tecnologico

| Componente | Tecnologia |
|------------|-----------|
| Backend | PHP 8.1+ / CodeIgniter 4 |
| Base de datos | MySQL 8.0.45 (DigitalOcean Managed, SSL required) |
| Servidor web | Nginx (Ubuntu 24.04, Hetzner LXC) |
| Email | SendGrid API v3 (SMTP relay, click tracking desactivado) |
| PDF | DOMPDF 3.1 (informes ejecutivos con graficos SVG gauge) |
| Excel | PhpSpreadsheet 5.2 |
| IA | OpenAI GPT-4o (interpretaciones, secciones de informe, recomendaciones) |
| Testing | PHPUnit 10.5 + Faker |
| Frontend | Bootstrap + JavaScript vanilla |

### Modulos principales (15)

| Modulo | Descripcion |
|--------|------------|
| Autenticacion | Login, recuperacion de password, gestion de sesiones |
| Baterias de servicio | Creacion y gestion de baterias de evaluacion psicosocial |
| Trabajadores | CRUD, asignacion a baterias, importacion CSV masiva con contingencia |
| Evaluacion (Assessment) | Formularios de 4 dimensiones: Intralaboral A/B, Extralaboral, Estres |
| Resultados y reportes | Analisis consolidado, mapas de calor, informes ejecutivos |
| Resultados individuales | Solicitudes de acceso con aprobacion por revisor |
| Maximo riesgo | Identificacion automatica de trabajadores en riesgo critico |
| PDF ejecutivo | 10 controladores de informe PDF con graficos gauge SVG |
| PDF nativo | Generacion alternativa con wkhtmltopdf |
| Demograficos | Analisis e interpretacion IA por segmentos demograficos |
| Recomendaciones IA | Planes de accion generados por OpenAI segun dimension de riesgo |
| Encuesta satisfaccion | Feedback post-evaluacion de servicios |
| Bateria publica | Acceso grupal via QR/link sin necesidad de email |
| Comercial | Gestion de ordenes de venta (equipo Gladiator) |
| Portal cliente | Dashboard readonly para empresas clientes |

### Roles de usuario

| Rol | Acceso |
|-----|--------|
| superadmin | Todo el sistema + gestion de usuarios + configuracion |
| consultor | Gestion de empresas asignadas + baterias + reportes + IA completo |
| cliente_gestor | Readonly reportes (gestiona multiples empresas) |
| cliente_empresa | Readonly reportes + historial (una empresa) |
| director_comercial | Gestion de ordenes, empresas, baterias, reportes, satisfaccion |

### Estructura del proyecto

```
psyrisk/
├── app/
│   ├── Commands/          # 7 comandos spark (cron jobs, recalculos, debug)
│   ├── Config/            # Routes.php, Database.php, Email.php, scoring configs
│   ├── Controllers/       # 25 controladores principales
│   │   ├── PdfEjecutivo/  # 10 controladores de informe ejecutivo
│   │   └── PdfNativo/     # 2 controladores de PDF nativo
│   ├── Database/
│   │   ├── Migrations/    # 40+ migraciones de esquema
│   │   └── Seeds/         # Seeders (roles, usuarios, consultores)
│   ├── Libraries/         # 4 librerias de scoring (IntralaboralA/B, Extralaboral, Estres)
│   ├── Models/            # 21 modelos
│   ├── Services/          # 5 servicios (OpenAI, Email, PDF, Demographics, Report)
│   └── Views/             # 27 directorios de vistas
├── docs/                  # Documentacion tecnica
├── public/                # Punto de entrada web
├── scripts/               # Scripts utilitarios
├── tests/                 # Tests PHPUnit
├── writable/              # Logs, cache, sesiones, PDFs, fonts
├── .env                   # Variables de entorno (NO commitear)
├── .env.example           # Template de variables
├── CONTRIBUTING.md        # Guia de contribucion
├── README.md              # Documentacion principal
├── composer.json          # Dependencias PHP
└── spark                  # CLI de CodeIgniter
```

### Cron jobs

| Comando | Frecuencia | Descripcion |
|---------|-----------|------------|
| `php spark reminders:send` | Diario | Recordatorios de baterias (30 dias, 7 dias, vencidas) via SendGrid |

---

## 2. MAPA DE BASE DE DATOS

**Motor:** MySQL 8.0.45 (DigitalOcean Managed)
**Base de datos:** psyrisk
**Tamano total:** 12.41 MB
**SSL:** Required

### Usuarios de base de datos

| Usuario | Permisos | Uso |
|---------|----------|-----|
| cycloid_userdb | Full access | Aplicacion principal (CRUD) |
| cycloid_readonly | SELECT only | Consultas de solo lectura |
| doadmin | Administrador | Gestion DigitalOcean (no usar en app) |

### Resumen

- **23 tablas** (BASE TABLE)
- **0 vistas** (VIEW)
- **31 foreign keys** definidas
- **7 tablas vacias** (30%) — modulos pendientes de activar

### Tablas por modulo

**Nucleo (4 tablas):** roles (5 reg), users (10), company_users (0 — vacia), consultants (0 — vacia)

**Empresas y baterias (3 tablas):** companies (8 reg), battery_services (8), battery_schedules (0 — vacia)

**Trabajadores y evaluaciones (3 tablas):** workers (585 reg), worker_demographics (408), responses (61,158 — la mas grande, 8.55 MB)

**Resultados y calculos (3 tablas):** calculated_results (353 reg), max_risk_results (147), validation_results (282)

**Reportes (3 tablas):** reports (0 — vacia), report_sections (297, 1.61 MB), demographics_sections (2)

**Importacion CSV (1 tabla):** csv_imports (5 reg)

**Otros (6 tablas):** action_plans (13), individual_results_requests (5), demographics_interpretations (0 — vacia), password_resets (0 — vacia), service_satisfaction_surveys (0 — vacia), migrations (31)

### Tabla central: battery_services

10 tablas dependen de `battery_services.id` via foreign key. Es la entidad central del sistema junto con `users` (9 FKs) y `workers` (3 FKs).

### Tablas mas grandes por peso

| Tabla | Registros | Tamano |
|-------|-----------|--------|
| responses | 61,158 | 8.55 MB |
| report_sections | 297 | 1.61 MB |
| action_plans | 13 | 0.45 MB |
| calculated_results | 353 | 0.30 MB |
| workers | 585 | 0.27 MB |

### Tablas vacias (7)

1. battery_schedules — programacion de baterias (pendiente de activar)
2. company_users — relacion empresa-usuario portal (pendiente de activar)
3. consultants — perfiles de consultores (pendiente de activar)
4. demographics_interpretations — interpretaciones IA demograficas (pendiente)
5. password_resets — tokens de reset (se limpian automaticamente)
6. reports — reportes consolidados (posiblemente obsoleta)
7. service_satisfaction_surveys — encuestas de satisfaccion (pendiente)

### Foreign keys (31)

Arbol de dependencias principales:

```
battery_services (centro)
├── battery_schedules
├── calculated_results
├── csv_imports
├── demographics_interpretations
├── demographics_sections
├── individual_results_requests
├── max_risk_results
├── reports
├── service_satisfaction_surveys
├── validation_results
└── workers
    ├── calculated_results
    ├── individual_results_requests
    ├── responses
    └── worker_demographics

users (autenticacion)
├── battery_services (consultant_id)
├── companies (created_by)
├── company_users
├── csv_imports (imported_by)
├── demographics_sections (generated_by)
├── individual_results_requests (reviewed_by, requester_user_id)
├── report_sections (approved_by)
├── service_satisfaction_surveys
└── validation_results (processed_by)

companies
├── battery_services
├── company_users
└── companies (parent_company_id — auto-referencia)

reports
└── report_sections
```

### Observaciones

- **`reports` tiene 0 registros pero `report_sections` tiene 297** — posible inconsistencia: las secciones pueden ser huerfanas o el conteo de InnoDB es aproximado.
- **`company_users` vacia** — el portal cliente puede no estar en uso activo.
- **`consultants` vacia** — los consultores se manejan via tabla `users` con rol `consultor`.

---

## 3. INVENTARIO DE API KEYS Y SERVICIOS EXTERNOS

### Resumen

| Servicio | Variable | Archivos | Estado |
|----------|----------|----------|--------|
| SendGrid | `email.SMTPPass` | 6+ | Activa — SMTP relay con click tracking desactivado |
| OpenAI | `OPENAI_API_KEY` | 5+ | Activa — GPT-4o para interpretaciones IA |
| Encryption | `encryption.key` | 1 | Activa — clave de encriptacion de datos |

### SendGrid

Usado en 6+ archivos para email transaccional: recuperacion de password, notificaciones de baterias, recordatorios, invitaciones a portal cliente, ordenes comerciales.

**Patron:** SMTP relay via CodeIgniter Email library + SendGrid API directo en algunos archivos.

**Archivos principales:**
- `app/Libraries/EmailService.php` — servicio centralizado
- `app/Commands/SendBatteryReminders.php` — recordatorios automaticos
- `app/Controllers/PasswordResetController.php` — recuperacion de password
- `app/Controllers/ClientUserController.php` — invitaciones portal cliente
- `app/Controllers/CommercialController.php` — notificaciones comerciales
- `app/Controllers/BatteryServiceController.php` — notificaciones de bateria

### OpenAI

Usado en 5+ archivos para IA generativa: interpretaciones de resultados, secciones de informe ejecutivo, recomendaciones por dimension de riesgo, analisis demografico.

**Patron:** Servicio centralizado `OpenAIService.php` con cURL a `https://api.openai.com/v1/chat/completions`
**Modelo:** gpt-4o (configurable via `OPENAI_MODEL`)

**Archivos principales:**
- `app/Services/OpenAIService.php` — servicio centralizado
- `app/Services/DemographicsReportService.php` — interpretacion demografica
- `app/Controllers/MaxRiskController.php` — analisis de maximo riesgo
- `app/Controllers/ReportSectionsController.php` — secciones de informe
- `app/Controllers/DemographicsReportController.php` — reportes demograficos

### HALLAZGOS CRITICOS DE SEGURIDAD

**CRITICO — Credenciales en .env commiteado:**

| Credencial | Problema |
|-----------|----------|
| `email.SMTPPass` (SendGrid) | API Key completa en .env, commiteado en git |
| `OPENAI_API_KEY` | API Key completa en .env, commiteado en git |
| `encryption.key` | Clave de encriptacion en .env, commiteado en git |

**CRITICO — Repositorio publico:**

El repositorio `github.com/edielestudiante2023/psirysk` es **PUBLICO**. El `.env` contiene todas las credenciales en texto plano. Las siguientes claves deben rotarse **INMEDIATAMENTE**:

| Variable | Accion |
|----------|--------|
| `email.SMTPPass` (SendGrid) | ROTAR INMEDIATAMENTE |
| `OPENAI_API_KEY` | ROTAR INMEDIATAMENTE |
| `encryption.key` | Regenerar con `php spark key:generate` |
| `database.default.password` (produccion) | Rotar en DigitalOcean |

**ALTO — Script con credenciales de produccion:**

| Archivo | Problema |
|---------|----------|
| `apply_consent_migration_prod.php` | Host y usuario de BD de produccion hardcodeados (password via getenv) |

---

## 4. DOCUMENTACION DEL PROYECTO

### Archivos creados en el repositorio

| Archivo | Descripcion |
|---------|------------|
| `README.md` | Documentacion principal: stack, 15 modulos, 5 roles, estructura, instalacion, cron jobs, deploy |
| `CONTRIBUTING.md` | Guia de contribucion: flujo de ramas, convencion de commits, reglas, proceso de revision |
| `.env.example` | Template con todas las variables de entorno (sin valores reales) |

### README.md incluye

- Stack tecnologico completo (9 componentes)
- 15 modulos con descripcion
- 5 roles de usuario con accesos
- Estructura de carpetas del proyecto
- Requisitos previos e instrucciones de instalacion
- 9 variables de entorno documentadas
- 1 cron job con frecuencia y descripcion
- Instrucciones de deploy
- Links a documentacion adicional en docs/

### CONTRIBUTING.md incluye

- Flujo de ramas (main → develop → feature/ → hotfix/)
- Convencion de commits (feat:, fix:, docs:, refactor:, chore:, test:, style:)
- Convencion de nombres de ramas
- 5 reglas (no push directo, no credenciales, no temporales, no destructivos)
- Proceso de revision con pipeline CI/CD

### .env.example incluye

- Variables de entorno para BD principal
- API Keys de email (SendGrid) con instrucciones de obtencion
- API Key de OpenAI con instrucciones
- Clave de encriptacion (placeholder)
- Variables de produccion (comentadas, opcionales)
- Comentarios explicativos por seccion

---

## 5. RAMAS DE TRABAJO

### Estructura creada

```
main          <- Produccion. Solo codigo validado y estable.
develop       <- Integracion. Aqui se unen los cambios antes de ir a main.
feature/xxx   <- Nuevas funcionalidades. Se crean desde develop.
hotfix/xxx    <- Correcciones urgentes. Se crean desde main.
```

### Estado actual

| Rama | Estado | Commit actual |
|------|--------|--------------|
| main | Existente, en remoto | Produccion estable |
| develop | Creada, pendiente push a remoto | Mismo commit que main |
| cycloid | Legacy — sera reemplazada por develop | Rama de trabajo actual |

### Proteccion de ramas (pendiente en Gitea)

- **main:** protegida, requiere PR, no push directo
- **develop:** protegida, requiere PR desde feature/

### Flujo de trabajo

- Nueva funcionalidad: `develop` → `feature/nombre` → PR a `develop` → PR a `main`
- Hotfix urgente: `main` → `hotfix/nombre` → PR a `main` + PR a `develop`

---

## 6. PIPELINES CI/CD

### Plataforma: Gitea con Gitea Runner (act_runner)

### Pipeline 1: Validar y Deploy a Dev/QA

**Archivo:** `.gitea/workflows/validate-and-deploy-qa.yml`
**Trigger:** Push/PR a develop o feature/*

```
git push → Gitea → Runner → Tests + Trivy + Semgrep → Deploy SSH → LXC (Dev/QA)
```

| Job | Que hace | Bloquea si falla |
|-----|---------|-----------------|
| test | `php -l` en todos los .php de app/ | Si |
| trivy | Escaneo de vulnerabilidades en dependencias (HIGH/CRITICAL) | Si |
| semgrep | Analisis estatico de seguridad (reglas PHP + secrets + security-audit) | Si |
| secrets-scan | Busca API keys hardcodeadas (SendGrid, OpenAI, Anthropic, DB) | Si |
| deploy-qa | SSH al LXC Dev/QA y ejecuta deploy | Solo en push a develop |

### Pipeline 2: Cutover a Produccion

**Archivo:** `.gitea/workflows/cutover-production.yml`
**Trigger:** Push a main (despues de merge de PR desde develop)

```
PR develop → main → Validacion → Trivy → Semgrep → Deploy SSH → Hetzner LXC
                                                                → Verificacion post-deploy
```

| Job | Que hace |
|-----|---------|
| validate | Sintaxis PHP + busqueda de credenciales |
| trivy | Escaneo vulnerabilidades (paralelo con semgrep) |
| semgrep | Analisis estatico seguridad (paralelo con trivy) |
| deploy-production | SSH al Hetzner + deploy + verificacion HTTP post-deploy |

**Todo por pipeline, nada manual.**

### Secrets necesarios en Gitea

**Para Dev/QA:** QA_HOST, QA_USER, QA_SSH_KEY, QA_PATH
**Para Produccion:** PROD_HOST, PROD_USER, PROD_SSH_KEY, PROD_PATH

### Flujo completo

```
feature/xxx → push → Validacion → PR a develop → Validacion → merge
                                                                 ↓
                                          Deploy automatico a LXC Dev/QA
                                                                 ↓
                                              Pruebas en QA (manuales o auto)
                                                                 ↓
                                          PR develop → main → Validacion → merge
                                                                             ↓
                                                     Cutover automatico a Hetzner LXC
                                                                             ↓
                                                          Verificacion post-deploy
                                                                             ↓
                                                              EN PRODUCCION
```

---

## 7. ORGANIZACION DEL REPOSITORIO

### Estado del repositorio

| Aspecto | Estado actual | Accion |
|---------|-------------|--------|
| Visibilidad | PUBLICO en GitHub | Migrar a Gitea privado |
| .gitignore | Actualizado (excluye .env, credenciales, cache, .claude/) | OK |
| .env.example | Creado con todas las variables | OK |
| .env commiteado | Credenciales en texto plano en historial | Limpiar historial |
| Archivos .md sueltos | 34 archivos .md en raiz | Mover a docs/ |

### Archivos .md sueltos en raiz (34 — deberian moverse a docs/)

**Auditorias de scoring:**
- ANALISIS_REDONDEOS.md, AUDITORIA_DISCREPANCIA_HEATMAPS.md, AUDITORIA_ESTRES.md, AUDITORIA_EXTRALABORAL.md, AUDITORIA_INTRALABORAL_A_B.md, ERRORES_CRITICOS_BAREMOS_ENCONTRADOS.md, ESTRES_SCORING_LOGIC.md, ESTRES_VALIDATION_LOGGING.md, INVENTARIO_BAREMOS_COMPLETO.md, INVESTIGACION_FACTOR_388_vs_396.md, PROCESO_DIAMANTE_AUDITORIA.md, PROCESO_ESMERALDA_AUDITORIA.md, RESUMEN_AUDITORIAS_COMPLETO.md, RESUMEN_CALCULOS_WORKER4.md

**Documentacion de modulos:**
- BATTERY_REMINDERS_SETUP.md, DASHBOARD_SATISFACCION_VENDEDORES.md, DOCUMENTACION_MAPA_CALOR.md, ENCUESTA_SATISFACCION.md, FLUJO_ACCESO_BATERIA.md, INDIVIDUAL_RESULTS_ACCESS_SYSTEM.md, INTRALABORAL_DASHBOARD_ARCHITECTURE.md, MODULO_CSV_CONTINGENCIA.md, PLAN_IMPLEMENTACION_PDF_GRAFICOS.md, PROBLEMA_PDF_GAUGES.md, PROYECTO_INFORMES_GLOBALES.md, SOLUCION_ESTADOS_Y_CIERRE.md

**READMEs adicionales:**
- README_BAREMOS.md, README_MAPAS_CALOR_DOMPDF.md, README_PDF_DOMPDF.md

**Progreso e implementacion:**
- PROGRESO_IMPLEMENTACION.md, RESUMEN_FINAL_IMPLEMENTACION.md, SENDGRID_QUICK_START.md, SINCRONIZACION_PRODUCCION.md, PRUEBA_CONSENTIMIENTO.md

### Scripts con credenciales trackeados

| Archivo | Problema |
|---------|----------|
| `apply_consent_migration_prod.php` | Host y usuario de BD produccion hardcodeados |

### Archivos CSV que SI deben quedarse (usados por la aplicacion)

- Templates CSV de importacion de trabajadores (Forma A/B)

---

## 8. HALLAZGOS CRITICOS Y ACCIONES PENDIENTES

### Prioridad CRITICA

| # | Accion | Responsable |
|---|--------|------------|
| 1 | Hacer repo privado o migrar a Gitea | Consultor/Cliente |
| 2 | Rotar SendGrid API Key (`email.SMTPPass`) | Cliente |
| 3 | Rotar OpenAI API Key (`OPENAI_API_KEY`) | Cliente |
| 4 | Regenerar encryption key | Cliente |
| 5 | Rotar password de BD en DigitalOcean | Consultor |
| 6 | Limpiar `.env` del historial de git (`git filter-branch` o BFG) | Consultor |

### Prioridad ALTA

| # | Accion | Responsable |
|---|--------|------------|
| 7 | Push de rama `develop` al remoto | Cliente |
| 8 | Configurar proteccion de ramas en Gitea | Consultor |
| 9 | Configurar secrets en Gitea para pipelines (QA + Prod) | Consultor |
| 10 | Eliminar `apply_consent_migration_prod.php` del repo | Cliente |

### Prioridad MEDIA

| # | Accion | Responsable |
|---|--------|------------|
| 11 | Mover 34 archivos .md de raiz a docs/ | Cliente |
| 12 | Investigar tabla `reports` vacia vs `report_sections` con 297 registros | Cliente |
| 13 | Activar modulo portal cliente (`company_users` vacia) | Cliente |
| 14 | Activar modulo consultores (`consultants` vacia, se usa `users`) | Cliente |
| 15 | Agregar mas cron jobs (recalculos automaticos, limpieza de sesiones) | Cliente |

---

*Documento generado el 2026-04-05. Preparado como entregable del proceso de hardening del repositorio psyrisk.*
