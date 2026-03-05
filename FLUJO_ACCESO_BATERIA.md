# Flujo de Acceso Grupal a la Batería RPS

## Contexto y Problema Resuelto

Cuando un servicio tiene muchos trabajadores (ej. 400 personas) y se realiza la aplicación en sesiones presenciales o grupos virtuales, el flujo anterior obligaba al consultor a:
- Enviar emails uno a uno o masivamente, esperar que cada persona reciba el correo.
- No había forma de que alguien sin email previo accediera en tiempo real.

Este módulo agrega un **Flujo B** de acceso grupal que coexiste con el flujo original (emails individuales), sin romper nada existente.

---

## Flujo B — Acceso Grupal (nuevo)

```
Consultor abre el servicio en battery_services/view/{id}
        ↓
Hace clic en "Enlace + QR Grupal"
        ↓
Se genera un enlace único del servicio → /bateria/{enlace_acceso}
Se muestra modal con URL copiable + código QR
        ↓
Consultor proyecta el QR en pantalla o comparte la URL
        ↓
Worker escanea QR o escribe la URL en su dispositivo
        ↓
Pantalla: "Ingresa tu número de documento"
        ↓
Sistema busca el documento en workers del servicio
        ↓
    ┌── No encontrado → mensaje de error, reintentar
    ├── Ya completado → vista "ya_completado" con mensaje amable
    └── Encontrado y pendiente → redirect a /assessment/{token_personal}
        ↓
Worker llena su batería normalmente (flujo existente)
```

---

## Flujo A — Email Individual (sin cambios)

El flujo anterior sigue intacto:
- Botón "Enviar email" por trabajador en `workers/index.php`
- Botón "Enviar a todos pendientes" masivo
- Botón "Copiar enlace" individual por fila

---

## Archivos Creados / Modificados

### Nuevos
| Archivo | Descripción |
|---|---|
| `app/Controllers/BateriaPublicaController.php` | Controlador del flujo público. Métodos: `acceso()`, `validarDocumento()` |
| `app/Views/bateria_publica/acceso.php` | Pantalla de ingreso de documento (similar al acceso de votación) |
| `app/Views/bateria_publica/ya_completado.php` | Pantalla para quien ya completó la batería |
| `app/Views/bateria_publica/enlace_invalido.php` | Pantalla para enlace inactivo, expirado o de servicio no en curso |
| `app/Database/Migrations/2026-03-05-000001_AddEnlaceAccesoToBatteryServices.php` | Migración que agrega `enlace_acceso VARCHAR(64) UNIQUE NULL` a `battery_services` |

### Modificados
| Archivo | Cambio |
|---|---|
| `app/Config/Routes.php` | Rutas públicas: `GET /bateria/(:segment)` y `POST /bateria/validar` |
| `app/Config/Routes.php` | Ruta del endpoint: `POST /battery-services/generar-enlace/(:num)` |
| `app/Controllers/BatteryServiceController.php` | Método `generarEnlace($id)` al final del archivo |
| `app/Models/BatteryServiceModel.php` | `enlace_acceso` agregado a `$allowedFields` |
| `app/Views/battery_services/view.php` | Botón "Enlace + QR Grupal" en quick-actions, modal Bootstrap, JS con qrcode.js |

---

## Base de Datos

### Campo agregado
```sql
ALTER TABLE battery_services
ADD COLUMN enlace_acceso VARCHAR(64) NULL DEFAULT NULL UNIQUE AFTER status;
```

- **Local:** aplicado vía `php spark migrate` (batch 14)
- **Producción:** aplicado directamente + registrado en tracker de migraciones (batch 14)

### Lógica del enlace
- Se genera como `md5($id . uniqid('bateria_', true))` → 32 caracteres hexadecimales
- Una vez generado, se reutiliza (no se regenera en cada clic)
- El consultor puede "Regenerar enlace" desde el modal (invalida el anterior)

---

## Validaciones del Controlador

| Condición | Resultado |
|---|---|
| Enlace no existe en BD | Vista `enlace_invalido` |
| Servicio en estado `planificado` | Vista `enlace_invalido` con mensaje específico |
| Servicio en estado `finalizado` | Vista `enlace_invalido` con mensaje específico |
| `link_expiration_date` vencida | Vista `enlace_invalido` |
| Documento no encontrado en el servicio | Redirect con error flash a la pantalla de acceso |
| Worker con `status = completado` | Vista `ya_completado` |
| Worker sin token generado | Redirect con error flash (caso edge) |
| Todo OK | `redirect()->to(base_url('assessment/' . $worker['token']))` |

---

## QR Code

- **Librería:** `qrcodejs` v1.0.0 (CDN, JavaScript puro, sin instalación PHP/Composer)
- **Carga:** solo en `battery_services/view.php`, no afecta otras páginas
- **Función `imprimirQR()`:** abre nueva ventana con imagen del QR + URL para impresión directa
- **Colores del QR:** azul marino `#1a365d` sobre blanco

---

## Seguridad

- Las rutas `/bateria/*` son **públicas** (no requieren sesión)
- El endpoint `POST /battery-services/generar-enlace/:id` requiere sesión y rechaza roles `cliente_empresa` y `cliente_gestor`
- El acceso individual al formulario sigue protegido por el `token` único por trabajador (generado al crear el worker)
- No se expone información personal en la URL pública (solo el hash del servicio)

---

## Anti-doble-respuesta

El campo `workers.status` actúa como control:
- Al entrar por el QR/enlace, si `status = completado` → vista de "ya completado"
- El `AssessmentController` existente también valida el token y el estado antes de permitir respuestas

---

## Notas para Próximas Iteraciones

- [ ] Considerar agregar campo `grupo_sesion` en workers para identificar qué sesión presencial realizó cada trabajador
- [ ] Opción de descargar el QR como imagen PNG desde el modal
- [ ] Mostrar en el modal cuántos workers hay pendientes vs completados en tiempo real
