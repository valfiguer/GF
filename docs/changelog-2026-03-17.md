# GoalFeed — Informe de Cambios del 17/03/2026

**Proyecto:** GoalFeed — Portal web de noticias deportivas
**Fecha:** 17 de marzo de 2026
**Archivos modificados:** 18 archivos de código + limpieza de ~780 imágenes del repositorio
**Total commits:** 15

---

## Resumen Ejecutivo

Jornada dedicada a resolver problemas críticos de la plataforma: secciones de ligas vacías (Copa del Rey, UEFA, FIFA), navegación con recargas completas de página, inestabilidad del feed de noticias y errores en el sistema de comentarios. También se optimizó el proceso de despliegue reduciendo el tiempo de deploy de ~20 minutos a menos de 2 minutos.

---

## 1. Optimización del Despliegue (Deploy)

| Commit | Descripción |
|--------|-------------|
| `1742a36` | Eliminación de 646 imágenes (65 MB) del repositorio Git — son contenido dinámico generado por el pipeline |
| `96228fe` | Deploy cambiado de automático (push) a manual (workflow_dispatch). Eliminado flag `--delete` que borraba archivos del servidor |
| `8b11ea2` | Añadido flag `--only-newer` al FTP para no re-subir archivos sin cambios. Excluidas imágenes estáticas (logos ya en servidor) |

**Resultado:** Tiempo de deploy reducido de ~20 min a <2 min. Eliminado riesgo de borrar archivos de producción accidentalmente.

---

## 2. Sistema de Comentarios — Bug Crítico Resuelto

| Commit | Descripción |
|--------|-------------|
| `80249b1` | Fix del manejo de errores en la API de comentarios (devolvía HTML en vez de JSON al fallar) |
| `8ed066d` | Aplicación de fixes a los archivos PHP de producción (anteriormente aplicados solo a los archivos Python que no se usan en producción) |
| `731f6c8` | Script de diagnóstico temporal para identificar el error 500 en comentarios |
| `1c6afd1` | **Causa raíz encontrada:** faltaba la columna `user_id` en la tabla `web_comments`. Corregido con ALTER TABLE |
| `384c170` | Eliminación del script de diagnóstico tras resolver el problema |

**Causa raíz:** La tabla `web_comments` fue creada antes de que el schema incluyera la columna `user_id`, por lo que el INSERT fallaba con error 500.

---

## 3. Secciones de Ligas Vacías (Copa del Rey, UEFA, FIFA)

| Commit | Descripción |
|--------|-------------|
| `cad23e4` | Expansión de `TEAM_LEAGUE_MEMBERSHIP`: equipos españoles ahora incluyen `copadelrey`, equipos top incluyen `uefa` y `fifa`. Actualizado tanto en Python (`config.py`) como en PHP (`migrate_and_backfill.php`) |
| `6fecb86` | **Fix definitivo de UEFA:** el clasificador asignaba solo 1 liga por equipo por artículo. Un artículo de "Barcelona en Champions" solo creaba la entrada `(barcelona, champions)`, nunca `(barcelona, uefa)`. Corregido para insertar entradas para TODAS las ligas del equipo |

**Cambio técnico clave:**
- Antes: `classify_teams()` → 1 entrada por equipo (solo la liga contextual)
- Ahora: `classify_teams()` → N entradas por equipo (todas las ligas de su membership)
- Ejemplo: Un artículo sobre Barcelona genera entradas para: `laliga`, `champions`, `copadelrey`, `uefa`, `fifa`

**Archivos:** `processor/classify.py`, `migrate_and_backfill.php`, `config.py`

---

## 4. Estabilización del Feed "Más Noticias"

| Commit | Descripción |
|--------|-------------|
| `cad23e4` | Reemplazado `RAND() * 5` por `(id % 7)` en la fórmula de scoring del feed. Los artículos ahora mantienen el mismo orden en cada recarga |
| `cad23e4` | Artículos relacionados ahora priorizan la misma categoría del artículo actual |

**Antes:** Cada recarga mostraba un orden diferente (aleatorio).
**Ahora:** Orden determinista y estable con variedad integrada.

**Archivos:** `models/ArticleRepository.php`, `controllers/ArticleController.php`

---

## 5. Navegación AJAX (SPA-like) — Sin Recargas de Página

| Commit | Descripción |
|--------|-------------|
| `cad23e4` | Implementación inicial: soporte `_fragment` en `View.php` + función `initAjaxPagination()` en JS + atributos `data-ajax-page` en enlaces de paginación |
| `6fecb86` | Fix: uso de `outerHTML` en vez de `innerHTML` para evitar contenedores anidados `#ajax-content` |
| `df6c109` | Cache-busting automático para JS y CSS usando `filemtime()` en la URL del recurso |
| `c79a1ef` | Extensión del AJAX a los enlaces del team nav con actualización del estado activo |
| `aeadfea` | Eliminación del `scrollIntoView` que causaba el salto al inicio en cada cambio de página |
| `c716640` | **Fix crítico:** `team.php` no tenía `id="ajax-content"`, lo que causaba que tras la primera navegación AJAX a un equipo, toda la navegación posterior dejara de funcionar |

**Funcionamiento:**
1. El usuario hace clic en paginación o en un equipo
2. JavaScript intercepta el clic (`preventDefault`)
3. `fetch()` carga solo el contenido de la página (sin header/footer/nav)
4. El contenido se reemplaza in-situ sin recargar la página completa
5. `history.pushState()` actualiza la URL del navegador
6. El botón "Atrás" del navegador funciona correctamente

**Archivos:** `core/View.php`, `static/js/main.js`, `views/home.php`, `views/league.php`, `views/team.php`, `views/partials/team_nav.php`, `views/base.php`

---

## 6. UX del Team Nav (Barra de Equipos)

| Commit | Descripción |
|--------|-------------|
| `3e9a862` | Mejora visual: bordes difuminados y scroll suave en la barra de equipos |
| `80249b1` | Flechas izquierda/derecha para navegar la barra horizontalmente |
| `aeadfea` | Auto-scroll horizontal para centrar el equipo seleccionado tras navegación AJAX |
| `c716640` | Auto-scroll al equipo activo en la carga inicial de la página |

---

## 7. Clasificación de Deportes

| Commit | Descripción |
|--------|-------------|
| `80249b1` | Añadidas keywords para "other" sport (Olimpiadas, tenis, ciclismo, etc.) para evitar que noticias no-fútbol se clasifiquen como fútbol. Status por defecto cambiado de RUMOR a CONFIRMADO |

---

## Resumen de Archivos Modificados

| Archivo | Tipo de cambio |
|---------|---------------|
| `static/js/main.js` | AJAX pagination, team nav arrows, auto-scroll, cache-busting |
| `static/css/style.css` | Estilos para team nav (fade edges, arrows) |
| `core/View.php` | Soporte `_fragment` para respuestas AJAX |
| `views/base.php` | Cache-busting en JS/CSS |
| `views/home.php` | `id="ajax-content"` + `data-ajax-page` |
| `views/league.php` | `id="ajax-content"` + `data-ajax-page` |
| `views/team.php` | `id="ajax-content"` + `data-ajax-page` |
| `views/partials/team_nav.php` | `data-ajax-page` en enlaces de equipos |
| `models/ArticleRepository.php` | Feed determinista + related por categoría |
| `controllers/ArticleController.php` | Paso de categoría a getRelated() |
| `controllers/ApiController.php` | Try/catch en POST comentarios |
| `processor/classify.py` | Multi-liga por equipo |
| `config.py` | TEAM_LEAGUE_MEMBERSHIP expandido |
| `config.php` | Sport "other" display |
| `migrate_and_backfill.php` | Multi-liga + TRUNCATE para re-clasificación |
| `.github/workflows/deploy.yml` | Deploy manual + exclusiones |
| `.gitignore` | Exclusión de imágenes dinámicas |

---

## Pendiente

- **Bot de recolección de noticias:** El proceso `main.py` no está corriendo en producción. Requiere configuración del entorno virtual Python y variables de entorno en el servidor (BOT_TOKEN, DB credentials). Se recomienda configurar un cron de auto-reinicio.
