# 🏆 Prode Mundial 2026

Una aplicación web de predicciones para la Copa Mundial de Fútbol FIFA 2026. Los participantes pueden pronosticar resultados de partidos, acumular puntos y competir en un ranking en tiempo real.

![Preview Site](https://federicogarcia.ar/prode/assets/preview-site.png)

## Características

✅ **Sistema de autenticación** — Registro y login de usuarios

✅ **Predicciones dinámicas** — Ingresá tus predicciones antes de que empiece cada partido

✅ **Sistema de puntuación automático** — Los puntos se calculan al cargar los resultados reales

✅ **Tabla de posiciones** — Ranking en tiempo real con desempates inteligentes

✅ **Panel de administración** — Carga de resultados y gestión de partidos

✅ **Perfiles de usuario** — Personalización de avatar y contraseña

✅ **Personalización total** — Personaliza el título y colores de la app desde el admin

✅ **Actualizador automático** — Detecta nuevas versiones en GitHub y actualiza la app con un click desde el panel admin

✅ **Responsive design** — Funciona perfecto en mobile y desktop

## Sistema de Puntos

| Resultado | Puntos |
|-----------|--------|
| **Marcador exacto** (ej: predijiste 2-1 y fue 2-1) | **3 puntos** |
| **Resultado correcto** (ej: predijiste 2-1 y fue 3-0, mismo ganador) | **1 punto** |
| **Resultado incorrecto** | **0 puntos** |

**Desempate en tabla:** Puntos totales → exactos → aciertos

## Estructura del Proyecto

```
prode-webapp/
├── index.php            # Login
├── register.php         # Registro de usuarios
├── dashboard.php        # Mis predicciones
├── leaderboard.php      # Tabla de posiciones
├── profile.php          # Perfil de usuario
├── admin.php            # Panel administrativo (incl. actualizador)
├── api.php              # API REST para guardar predicciones
├── auth.php             # Funciones de autenticación
├── db.php               # Conexión a BD y funciones de datos
├── style.css            # Estilos de la aplicación
├── schema.sql           # Schema inicial de BD
├── version.txt          # Versión instalada actualmente
└── INSTRUCCIONES.md     # Instrucciones de instalación
```

## Tecnologías Usadas

- **Backend:** PHP 7.4+ (MySQL/MariaDB)
- **Frontend:** HTML5, CSS3, JavaScript vanilla
- **Base de datos:** MariaDB 11+ / MySQL 8+
- **Servidor:** Apache/Nginx (o PHP built-in para desarrollo)

## Requisitos

- PHP 7.4 o superior (recomendado 8.0+), con extensión `zip` habilitada
- MariaDB 11+ / MySQL 8+
- Un servidor web (Apache, Nginx) o usar `php -S`

## Instalación Rápida

Consulta [INSTRUCCIONES.md](INSTRUCCIONES.md) para instrucciones detalladas de instalación.

### Resumen:
1. Importa `schema.sql` a tu base de datos
2. Configura credenciales en `db.php`
3. Levanta el servidor: `php -S 0.0.0.0:8080`
4. Ingresa como **admin/password** (cambiá la contraseña de inmediato)

## Uso

### Para participantes
1. **Registrate** — Creá tu cuenta
2. **Predice** — Ingresá el resultado esperado para cada partido
3. **Compite** — Gana puntos y subí al ranking
4. **Sigue** — Los partidos se cierran automáticamente cuando empiezan

### Para administradores
1. **Ingresa como admin**
2. **Carga resultados** — Panel Admin → Partidos y Resultados
3. **Gestiona partidos** — Agregá nuevos partidos o reabre los existentes
4. **Personaliza** — Cambiá el título y colores en Configuración de la Aplicación
5. **Actualizá** — Panel Admin → Actualizaciones de la App

## Personalización

### Cambiar el título y colores

Desde el **Panel de Administración**:
1. Ingresa como admin
2. Ve a **🎨 Configuración de la Aplicación**
3. Personaliza:
   - Título de la App
   - Color primario (azul oscuro)
   - Color primario oscuro (para headers)
   - Color primario claro (para fondos)
   - Color de acento (dorado)
4. Haz clic en **Guardar configuración**

Todos los cambios se aplicarán inmediatamente a toda la aplicación.

## Actualizaciones

La app incluye un sistema de actualizaciones automáticas integrado en el panel admin.

### Cómo funciona
- Al abrir el panel admin se consulta la [GitHub API](https://github.com/fedex6/Prode-webapp/releases) para comparar la versión instalada (`version.txt`) con el último release publicado (caché de 1 hora)
- Si hay una versión nueva, aparece un banner con el botón **Instalar**
- Al instalar: se descarga el ZIP del release, se extrae y se copian los archivos sobre la instalación actual
- **Archivos protegidos** (nunca se sobreescriben): `db.php`, `version.txt`, `.htaccess`

### Publicar una nueva versión
1. Hacé los cambios en el repo
2. Creá un nuevo release en GitHub con un tag (ej: `v1.1`)
3. Los usuarios verán el aviso en el panel admin automáticamente

## Base de Datos

### Tablas principales

- **users** — Participantes y administradores
- **matches** — Partidos del torneo
- **predictions** — Predicciones de usuarios
- **settings** — Configuración de la aplicación

## Seguridad

✅ Contraseñas hasheadas con bcrypt

✅ Protección CSRF en formularios

✅ Validación de entrada de datos

✅ SQL injection protection (prepared statements)

✅ Sesiones seguras

## Soporte

Para reportar bugs o sugerencias, abrí un [issue en GitHub](https://github.com/fedex6/Prode-webapp/issues).

---

**Made with ❤️🇦🇷**

[Sitio del proyecto](https://federicogarcia.ar/prode)
