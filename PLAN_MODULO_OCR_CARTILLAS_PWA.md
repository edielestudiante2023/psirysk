# Plan: Módulo OCR de Cartillas + PWA de Captura

**Estado:** Propuesta — pendiente de validación con prueba seca antes de implementar.
**Fecha de redacción:** 2026-04-12

---

## 1. Objetivo y problema que resuelve

Hoy, cuando un consultor aplica las cartillas de la batería de riesgo psicosocial en modalidad **presencial**, las respuestas se diligencian a mano en hojas físicas y luego alguien las **transcribe manualmente** al sistema. Esto:

- Consume horas de digitación por servicio.
- Introduce errores humanos que afectan los baremos finales.
- Es el cuello de botella más grande del flujo presencial.

**Idea:** que el consultor tome **fotos de las cartillas** desde el celular y un módulo basado en **Claude (visión)** identifique automáticamente las marcas X de cada pregunta y cree los `workers` + `responses` en la base de datos, sin digitación manual.

---

## 2. Concepto general

Dos piezas que trabajan juntas:

1. **Backend OCR con Claude API**: un endpoint que recibe una imagen de cartilla, la envía a Claude (`claude-opus-4-6` o `claude-sonnet-4-6`) con un prompt estructurado por tipo de cartilla, y devuelve un JSON con `{numero_pregunta: respuesta_detectada}`.
2. **PWA de captura**: una aplicación web instalable en el celular del consultor, con acceso a la cámara, que permite capturar cartilla por cartilla, previsualizar, procesar contra el backend y confirmar antes de guardar.

Claude tiene **visión nativa** — no se necesita Tesseract ni ningún OCR adicional. Es muy bueno detectando marcas tipo X / casillas rellenas en formularios escaneados o fotografiados.

---

## 3. Flujo del consultor en la PWA

1. **Login** en la PWA con credenciales de psyrisk (mismas que web).
2. Selecciona la **orden de servicio activa** (`battery_service`) en la que va a trabajar.
3. Para cada trabajador presencial:
   - Toca **"Nuevo trabajador"** → llena datos básicos (nombre, cédula, cargo, Forma A o B).
   - Toca **"Capturar cartilla"** → se abre la cámara, foto a foto (página 1, página 2, …).
   - La PWA muestra **preview** de cada foto y permite **retomar** si salió mal (mala luz, hoja torcida, etc.).
   - Toca **"Procesar"** → manda imágenes al backend, Claude extrae respuestas, devuelve JSON.
   - Pantalla de **revisión humana**: muestra cada pregunta con la respuesta detectada y la imagen al lado, con casillas para corregir lo que esté ambiguo.
   - Toca **"Confirmar"** → se guarda como `worker` + `responses` enlazado al `battery_service_id`.
4. **Cola de pendientes** visible en pantalla principal (ej: "12 cartillas procesadas, 3 esperando sincronización").

---

## 4. Stack técnico dentro de psyrisk

- **Frontend PWA**: HTML + Bootstrap (que ya usa el proyecto) + JavaScript vanilla para cámara e IndexedDB. **No se necesita** React ni framework moderno — una página tipo `app/Views/captura/index.php` puede servir como shell de la PWA.
- **`manifest.json`**: define icono, nombre, tema, modo standalone para que sea instalable.
- **`service-worker.js`**: cachea assets y guarda fotos en cola cuando no hay internet.
- **API de Claude**: SDK oficial de Anthropic para PHP (o llamada HTTP directa a `https://api.anthropic.com/v1/messages` con `image` content blocks).
- **Almacenamiento de imágenes**: `writable/uploads/cartillas/{battery_service_id}/{worker_id}/` para auditoría.

---

## 5. Endpoints nuevos requeridos

| Método | Ruta | Propósito |
|---|---|---|
| `GET` | `/captura` | Vista shell de la PWA |
| `GET` | `/captura/manifest.json` | Manifest PWA |
| `GET` | `/captura/service-worker.js` | Service worker |
| `GET` | `/captura/ordenes-activas` | JSON de órdenes asignadas al consultor |
| `POST` | `/captura/procesar-cartilla` | Recibe imagen(es), llama a Claude, devuelve JSON con respuestas detectadas |
| `POST` | `/captura/guardar-worker` | Recibe JSON validado por el consultor y crea `worker` + `responses` reusando lógica existente |

Todos los endpoints requieren sesión activa y validan que el consultor sea dueño de la `battery_service`.

---

## 6. Variantes según complejidad

### Versión mínima (1–2 semanas)
- PWA **solo online** (sin offline ni IndexedDB).
- El consultor necesita señal en el sitio del cliente.
- Más simple, sirve para validar la idea rápido.

### Versión media (3–4 semanas)
- Agrega **offline + cola de sincronización** con IndexedDB.
- El consultor puede capturar sin señal y sincronizar al regresar.
- Buena para campo real en empresas con cobertura mala.

### Versión completa (6–8 semanas)
- Multi-página: capturar las 4 hojas de una Forma A en orden.
- **Reconocimiento automático del tipo de cartilla** (Claude identifica si es Forma A, B, Extralaboral o Estrés antes de extraer).
- **Procesamiento batch**: subir 30 cartillas de una y procesar en background con notificación al terminar.

---

## 7. Consideraciones críticas

### Calidad de foto
- Buena luz, hoja plana, sin sombras, sin recortes.
- La PWA debe **guiar** al consultor con un marco/overlay para encuadrar.
- Marcas ambiguas (dos X, X corregida, marca fuera de la casilla) deben marcarse como **"revisar"** para revisión manual obligatoria.

### Costo por cartilla
- Aproximadamente **USD $0.02–$0.10 por cartilla** con Sonnet, depende de resolución y tipo.
- Una Forma A de 123 preguntas suele requerir 1–2 imágenes.
- Para 50 cartillas por servicio: ~USD $1.00–$5.00 en costos de API. Despreciable comparado con el ahorro de digitación.

### Validación humana obligatoria
- **Nunca** confiar 100% en la IA para datos que afectan baremos.
- La pantalla de revisión "previsualizar y confirmar" es **no negociable**.
- El consultor debe ver imagen + JSON detectado lado a lado y poder corregir antes de guardar.

### Diferencias por tipo de cartilla
- Forma A intralaboral, Forma B intralaboral, Extralaboral, Estrés — cada una tiene estructura distinta.
- **Cada tipo requiere su propio prompt** que describa la estructura esperada y las opciones válidas de respuesta.
- Se puede pedirle a Claude que **primero identifique el tipo** antes de extraer respuestas, lo que reduce errores del consultor al elegir mal el tipo.

### Auditoría
- Guardar siempre la imagen original junto al `worker_id` por si hay disputas posteriores sobre los datos.

---

## 8. Plan recomendado de validación (prueba seca)

**Antes de construir cualquier código del módulo**, hacer una validación rápida fuera del sistema:

1. Escanear/fotografiar **5–10 cartillas reales** con calidad típica de oficina (no estudio fotográfico).
2. Armar un script PHP simple (`scripts/test_ocr_cartillas.php`) que:
   - Tome una imagen como argumento.
   - La envíe a la API de Claude con un prompt para Estrés (la cartilla más corta, ~31 preguntas).
   - Imprima el JSON resultante.
3. Comparar el JSON contra la transcripción manual real.
4. **Medir:**
   - ¿Precisión? (¿qué porcentaje de preguntas detecta correctamente?)
   - ¿Confunde marcas dudosas? ¿Cómo las reporta?
   - ¿Costo real por cartilla?
   - ¿Tiempo de respuesta de la API por cartilla?

**Criterio de avance:**
- ≥ **95% de precisión** con revisión humana mínima → construir el módulo.
- < 95% → ajustar prompt, mejorar guía de captura o **descartar** y ahorrarse semanas de desarrollo.

Tiempo estimado de la prueba seca: **2 horas**.

---

## 9. Próximos pasos sugeridos

1. **Decisión:** ¿se invierte en la prueba seca?
2. Si sí: armar el script de prueba y juntar 5–10 cartillas reales escaneadas (idealmente Estrés primero).
3. Ejecutar la prueba seca y documentar resultados aquí mismo en una sección "Resultados de validación".
4. Si los números cuadran: priorizar **Versión mínima** primero, ponerla en manos de 1–2 consultores piloto, medir tiempo ahorrado real por servicio.
5. Iterar hacia versión media/completa solo si el piloto demuestra valor.

---

## 10. Notas adicionales

- Este módulo **no reemplaza** el flujo virtual existente — convive con él. La modalidad sigue siendo `presencial` o `virtual` en `workers.application_mode`.
- El módulo puede integrarse al campo de facturación P/V que ya muestra el listado comercial finalizado.
- Si el módulo funciona, el consultor podría capturar cartillas en campo y al volver tener el servicio prácticamente listo para cierre, sin necesidad de digitadores intermedios.
