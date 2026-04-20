# SMEM – Guía de Instalación (Solo XAMPP)
## Pasos: Copiar → Importar SQL → Abrir

---

## PASO 1 — Copiar archivos a XAMPP

Copia la carpeta `smem` completa a:

```
C:\xampp\htdocs\smem\
```

Estructura resultante:
```
C:\xampp\htdocs\smem\
├── index.html          ← Aplicación principal
├── database.sql        ← Script de base de datos
└── api\
    └── api.php         ← Backend PHP (no tocar)
```

---

## PASO 2 — Iniciar XAMPP

1. Abre **XAMPP Control Panel**
2. Haz clic en **Start** en **Apache**
3. Haz clic en **Start** en **MySQL**

---

## PASO 3 — Crear la base de datos (una sola vez)

1. Abre tu navegador y ve a: **http://localhost/phpmyadmin**
2. Clic en la pestaña **SQL**
3. Abre el archivo `database.sql` con el Bloc de notas
4. Copia todo el contenido y pégalo en phpMyAdmin
5. Clic en **Ejecutar / Go**

✅ Verás: *"Base de datos SMEM creada exitosamente"*

---

## PASO 4 — Abrir la aplicación

Ve a: **http://localhost/smem/index.html**

¡Listo! El sistema ya tiene datos de prueba y la base de datos activa.

---

## Rutas de la API (referencia)

| Recurso     | URL ejemplo                              |
|-------------|------------------------------------------|
| Ping        | `api/api.php?resource=ping`              |
| Registros   | `api/api.php?resource=registros`         |
| Estadísticas| `api/api.php?resource=stats`             |
| Alertas     | `api/api.php?resource=alertas`           |
| Especies    | `api/api.php?resource=especies`          |

---

## Solución de problemas

**"Sin conexión a MySQL"** → Verifica que MySQL esté corriendo en XAMPP  
**Error 404 en API** → Confirma que los archivos están en `C:\xampp\htdocs\smem\`  
**Página en blanco** → Verifica que Apache esté corriendo en XAMPP  
