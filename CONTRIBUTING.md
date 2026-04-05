# Guia de Contribucion — PsyRisk

## Flujo de ramas

```
main          <- Produccion. Solo codigo validado y estable.
develop       <- Integracion. Cambios se unen aqui antes de ir a main.
feature/xxx   <- Nuevas funcionalidades. Se crean desde develop.
hotfix/xxx    <- Correcciones urgentes. Se crean desde main.
```

### Nueva funcionalidad

1. Crear rama desde `develop`: `git checkout -b feature/modulo-descripcion develop`
2. Desarrollar y hacer commits siguiendo la convencion
3. Push: `git push -u origin feature/modulo-descripcion`
4. Crear PR hacia `develop`
5. Esperar validacion del pipeline CI/CD
6. Merge a `develop` tras aprobacion
7. Cuando `develop` este estable, crear PR hacia `main`

### Hotfix urgente

1. Crear rama desde `main`: `git checkout -b hotfix/bug-descripcion main`
2. Corregir y hacer commit
3. Crear PR hacia `main` Y hacia `develop`
4. Merge tras aprobacion

---

## Convencion de commits

Formato: `tipo: descripcion breve en minusculas`

| Tipo | Uso |
|------|-----|
| `feat:` | Nueva funcionalidad |
| `fix:` | Correccion de bug |
| `docs:` | Solo documentacion |
| `refactor:` | Cambio de codigo sin cambio funcional |
| `chore:` | Mantenimiento, dependencias, configuracion |
| `test:` | Agregar o modificar tests |
| `style:` | Formato, espacios, sin cambio de logica |

Ejemplos:

```
feat: agregar modulo de encuesta de satisfaccion
fix: corregir calculo de dimension estres en forma B
docs: actualizar instrucciones de deploy en README
refactor: extraer logica de scoring a servicio dedicado
chore: actualizar dependencias de composer
```

---

## Convencion de nombres de ramas

| Tipo | Formato | Ejemplo |
|------|---------|---------|
| Feature | `feature/modulo-descripcion` | `feature/demograficos-export-excel` |
| Hotfix | `hotfix/bug-descripcion` | `hotfix/scoring-estres-forma-b` |
| Release | `release/vX.Y.Z` | `release/v2.1.0` |

---

## Reglas

1. **No push directo a `main`** — siempre via PR desde `develop`
2. **No push directo a `develop`** — siempre via PR desde `feature/`
3. **No credenciales en el codigo** — usar variables de entorno (.env)
4. **No archivos temporales** — no commitear scripts de prueba, .txt de notas, stackdumps
5. **No operaciones destructivas en produccion** — no DELETE sin WHERE, no DROP, no TRUNCATE

---

## Proceso de revision

1. El desarrollador crea PR con descripcion clara de los cambios
2. El pipeline CI/CD ejecuta automaticamente:
   - Verificacion de sintaxis PHP (`php -l`)
   - Escaneo de vulnerabilidades (Trivy)
   - Analisis estatico de seguridad (Semgrep)
   - Busqueda de credenciales hardcodeadas
3. Si el pipeline pasa, un revisor aprueba el PR
4. Merge a la rama destino
5. Deploy automatico segun la rama (develop → QA, main → produccion)
