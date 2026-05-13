# Prode Mundial 2026 — Instrucciones de instalación

## Requisitos
- PHP 7.4 o superior (recomendado 8.3+)
- MariaDB 11+ / MySQL 8+
- Un servidor web (Apache, Nginx) o `php -S` para desarrollo local

## Instalación

### 1. Crear la base de datos
Ejecutá el script SQL en tu servidor MariaDB:

```bash
mysql -u root -p < schema.sql
```

O desde phpMyAdmin: importar el archivo `schema.sql`.

### 2. Configurar la conexión
Editá `db.php` y ajustá las credenciales:

```php
define('DB_HOST', 'localhost');
define('DB_USER', '--USER--');       // tu usuario
define('DB_PASS', '--PASSWORD');           // tu contraseña
define('DB_NAME', 'prode_mundial');
```

### 3. Levantar el servidor
Con PHP built-in (para desarrollo/red local):

```bash
php -S 0.0.0.0:8080
```

Luego entrás desde cualquier PC de la oficina a `http://IP-del-servidor:8080`

Con XAMPP/WAMP: copiá la carpeta a `htdocs/` y abrí `http://localhost/prode` o el nombre de carpeta que le hayas puesto.

### 4. Credenciales del administrador
- **Usuario:** `admin`
- **Contraseña:** `password`

⚠️ Cambiá la contraseña del admin apenas ingreses.

---

## Uso

### Para participantes
1. Registrarse con usuario y contraseña
2. Ingresar a "Mis Predicciones" y cargar el resultado esperado para cada partido
3. **¡Las predicciones cierran automáticamente cuando empieza el partido!**
4. Ver el ranking en "Tabla de Posiciones"

### Para el admin
1. Ingresar como `admin`
2. En "Panel de Admin" → cargar los resultados reales después de cada partido
3. Los puntos se calculan automáticamente al cargar cada resultado
4. Se pueden agregar nuevos partidos (octavos, cuartos, etc.) desde el panel

---

## Sistema de puntos
| Resultado | Puntos |
|-----------|--------|
| Marcador exacto (ej: predijiste 2-1 y fue 2-1) | **3 puntos** |
| Resultado correcto (ej: predijiste 2-1 y fue 3-0) | **1 punto** |
| Resultado incorrecto | **0 puntos** |

Desempate en tabla: puntos totales → exactos → aciertos.

---

## Personalización de la Aplicación

### Cambiar el título de la App

1. Ingresa como administrador
2. Ve al **Panel de Admin** 
3. En la sección **🎨 Configuración de la Aplicación**, editá el campo "Título de la App"
4. Haz clic en **Guardar configuración**

El nuevo título aparecerá inmediatamente en:
- Navegador (pestaña del browser)
- Header de todas las páginas (logo)
- Páginas de login/registro

### Personalizar colores

En la misma sección de configuración podés cambiar 5 colores principales:

| Color | Uso |
|-------|-----|
| **Color Primario** | Headers, botones, links, badges |
| **Color Primario Oscuro** | Hover de botones, texto en headers |
| **Color Primario Claro** | Fondos suaves, highlights |
| **Color de Acento** | Acentos en logo, botones especiales |

**Cómo cambiar:**
1. Haz clic en el selector de color (círculo colorido)
2. Elige el color deseado
3. Haz clic en **Guardar configuración**

Los cambios se aplican en toda la aplicación al instante.
