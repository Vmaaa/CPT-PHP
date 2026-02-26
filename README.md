# CPT-PHP - Sistema de Gestión de Trabajos Terminales (IPN)

Sistema integral para la gestión y seguimiento de **Trabajos Terminales** (proyectos de titulación) en el **Instituto Politécnico Nacional (IPN)**. Inspirado en funcionalidades de plataformas educativas como Google Classroom, permite la administración de proyectos, asignaciones, tiempos y usuarios en un entorno centralizado.

## 📋 Características Principales

- **Gestión de Usuarios**: Control de roles (administradores, asesores, alumnos) con módulos completos de registro e inicio de sesión.
- **Administración de Trabajos Terminales (Clases)**: Creación y gestión de proyectos, con etapas y períodos de tiempo definidos.
- **Sistema de Tareas y Asignaciones**: Funcionalidad similar a Classroom para la creación, entrega y revisión de avances o documentos.
- **Manejo de Archivos**: Carga y gestión de documentos asociados a los proyectos (tesis, reportes, etc.).
- **Visualización de Períodos**: Vista detallada de las etapas y plazos de cada trabajo terminal.
- **Preparado para Contenedores**: Incluye configuración lista para ejecutarse con Docker y Docker Compose.

## 🛠️ Stack Tecnológico

- **Backend**: PHP
- **Frontend**: JavaScript
- **Base de Datos**: MariaDB
- **Contenedorización**: Docker, Docker Compose
- **Dependencias PHP**: Gestionadas con Composer

## 🚀 Instalación y Configuración

### Requisitos Previos
- Git
- Docker y Docker Compose (Recomendado)
- O alternativamente: Servidor web (Apache/Nginx), PHP 8.0+ y MySQL

### Instalación Rápida con Docker (Recomendada)

1.  **Clonar el repositorio:**
    ```bash
    git clone https://github.com/Vmaaa/CPT-PHP.git
    cd CPT-PHP
    ```

2.  **Iniciar los contenedores:**
    ```bash
    docker-compose up -d
    ```
    Este comando construirá la imagen (basada en el `Dockerfile`) e iniciará el servicio. La inicialización de la base de datos se maneja desde el directorio `db_init/`.

3.  **Acceder a la aplicación:**
    Abre tu navegador y visita `http://localhost:8080` (o el puerto que hayas configurado).

### Instalación Manual (sin Docker)

1.  **Clonar** el repositorio en el directorio raíz de tu servidor web.
2.  **Instalar dependencias** con Composer:
    ```bash
    composer install
    ```
3.  **Configurar la base de datos:**
    - Crea una base de datos MySQL.
    - Importa los scripts SQL que se encuentran en la carpeta `db_init/` para crear las tablas necesarias.
4.  **Configurar la conexión:**
    - Revisa y ajusta los archivos de configuración en la carpeta `config/` con los datos de tu base de datos (usuario, contraseña, host, nombre de DB).
5.  **Asegurar permisos** de escritura en las carpetas necesarias para la subida de archivos (por ejemplo, `uploads/`, según `.gitignore`).

## ⚙️ Uso Básico

1.  **Registro de Administrador Inicial**: La primera vez, el sistema permite registrar una cuenta de administrador principal.
2.  **Creación de Proyectos/Clases**: Como administrador o asesor, podrás crear nuevos "Trabajos Terminales" (clases) y definir sus etapas.
4.  **Seguimiento de Tareas**: Crear asignaciones, establecer fechas de entrega y revisar los archivos subidos por los alumnos.

## 📁 Estructura del Proyecto

```
CPT-PHP/
├── api/                # Lógica de endpoints, Api REST
├── assets/             # Recursos estáticos (CSS, imágenes)
├── config/             # Archivos de configuración de conexiones https
├── db_init/            # Scripts SQL para la inicialización de la BD
├── functions/          # Lógica del negocio, trabajado con Singleton
├── inc/                # Archivos de inclusión (headers, footers, barras laterales)
├── pages/              # Vistas y módulos de la aplicación
├── js/                 # Archivos js correspondientes a cada página 
├── utils/              # Utilidades varias (librerías, helpers)
├── .gitignore
├── docker-compose.yml  # Orquestación de contenedores Docker
├── Dockerfile          # Instrucciones para construir la imagen Docker
├── index.php           # Punto de entrada principal
├── composer.json       # Dependencias PHP
└── README.md
```

## 📚 Explicación de las Etapas del Trabajo Terminal (TT)

En el contexto del IPN, un Trabajo Terminal suele dividirse en etapas que permiten un seguimiento progresivo. El sistema gestiona estas etapas mediante **períodos (stages)**. Cada etapa puede tener fechas de inicio y fin, y estar asociada a entregables específicos.

### Etapas típicas que el sistema puede manejar:

| Etapa | Descripción | Entregable esperado |
|-------|-------------|---------------------|
| En construcción

El sistema permite:
- Crear etapas personalizadas para cada TT.
- Asignar fechas límite.
- Subir archivos asociados a cada etapa (visibles en la carpeta `uploads/`).
- Visualizar el progreso en la página de detalles de la clase (`class_details`).

## 🌐 Endpoints de la API

La carpeta `/api` contiene los puntos de acceso para las peticiones asíncronas, que utiliza el frontend. A continuación se listan los endpoints principales.

### Autenticación y usuarios
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| `POST` | `/api/login.php` | Inicia sesión de usuario. |
| `POST` | `/api/register.php` | Registra un nuevo usuario (alumno, asesor). |
| `GET`  | `/api/user.php` | Obtiene datos del usuario autenticado. |
| `POST` | `/api/logout.php` | Cierra sesión. |


> **Formato de respuesta**: Los endpoints devuelven JSON con al menos los campos `success` (boolean) y `data` o `message`. Ejemplo:
```json
{
  "success": true,
  "data": { ... }
}
```

## 🔧 Documentación Técnica del Código

### Arquitectura general
El proyecto sigue un patrón **MVC (Modelo-Vista-Controlador)**:
- **Modelo**: La lógica de base de datos se encuentra principalmente en `/functions` y `/api`.
- **Vista**: Las páginas visibles están en `/pages` y los includes (header, footer) en `/inc`.
- **Controlador**: Los archivos en `/api` actúan como controladores, recibiendo peticiones, procesando datos y devolviendo respuestas.

### Flujo de autenticación
1. El usuario inicia sesión a través de `/api/login.php`.
2. Se valida contra la base de datos y se devuelve un JWT como Cookie HTTP Only.
3. Cada página protegida verifica la existencia de la sesión al inicio (`/utils/token/pre_validate.php`).

### Base de datos
- Los scripts de inicialización están en `/db_init`. Se espera que contengan las tablas necesarias:
  - `users`: id, nombre, email, password_hash, rol, etc.
  - `classes` (TT): id, titulo, descripcion, fechas, id_asesor, etc.
  - `stages`: id, class_id, nombre, fecha_inicio, fecha_fin, descripcion.
  - `tasks`: id, class_id, titulo, descripcion, fecha_entrega, etc.
  - `submissions`: id, task_id, user_id, archivo, fecha_subida.
  - `files`: id, class_id, stage_id, user_id, nombre_archivo, ruta, etc.
- Las relaciones se manejan con claves foráneas.

### Manejo de archivos
- Los archivos subidos se almacenan en la carpeta `uploads/`.
- La estructura dentro de `uploads/` organiza por clase y usuario:
  ```
  uploads/
  ├── class_1/
  │   ├── user_5_archivo.pdf
  │   └── ...
  └── class_2/
      └── ...
  ```
- El registro de cada archivo se guarda en las columnas de cada objeto con la ruta relativa.


### Dependencias (composer.json)
- `phpmailer/phpmailer` para envío de correos (notificaciones).
  

## 👥 Contribuidores

- **Vmaaa** (Iker Antonio Pluma Amaro)
- **Brosscom27** (Mario Cordova)

---

**Nota**: Este proyecto se encuentra en desarrollo activo. Para reportar problemas o sugerir mejoras, por favor abre un issue en el repositorio.
```
