# MiManga

MiManga es una aplicación web y mi proyecto FCT para explorar series de manga, consultar sus ediciones y volúmenes publicados, y organizar una colección personal. Cada usuario puede guardar volúmenes en su biblioteca o en su lista de deseos.

Esta aplicación tiene como objetivo auto-abastecerse de datos mediante scrapping y consumición de api, permitiendo crear de forma autónoma datos sin interacción ni mantenimiento humano. 

**Demo:** [mimanga.xafy.es](https://mimanga.xafy.es)

## Funcionalidades

- Buscar manga y explorar carruseles de series populares, mejor valoradas y en tendencia.
- Consultar información de series, autores, géneros y ediciones localizadas.
- Ver los volúmenes asociados a una edición.
- Mantener una biblioteca personal de volúmenes y una lista de deseos.
- Crear una cuenta, verificar el correo y gestionar el perfil.
- Usar la interfaz en español o inglés y cambiar entre tema claro y oscuro.

## Tecnologías y fuentes de datos

- **Backend:** PHP 8.2 o superior y Laravel 11.
- **Interfaz:** Blade, Tailwind CSS, DaisyUI, Alpine.js y Vite.
- **Base de datos:** MySQL, configuración incluida en `.env.example`.
- **Catálogo:** consultas GraphQL a [AniList](https://anilist.co/).
- **Ediciones y volúmenes:** consultas a [ListadoManga](https://www.listadomanga.es/) para datos en español y a [Whakoom](https://www.whakoom.com/) para datos en inglés (requiere cookie de sesión almacenada en .WHAKOOMUSER).

MiManga depende de servicios externos para parte de sus metadatos. Si alguno cambia su web o deja de responder, la búsqueda de ediciones o volúmenes puede devolver datos incompletos.

## Requisitos

- PHP 8.2 o superior con las extensiones necesarias para Laravel y el controlador PDO de tu base de datos.
- [Composer](https://getcomposer.org/).
- [Node.js y npm](https://nodejs.org/).
- MySQL o MariaDB para seguir la configuración de ejemplo.

## Instalación local

Clona el repositorio e instala las dependencias:

```bash
git clone https://github.com/jmaresp673/mimanga.git
cd mimanga
composer install
npm ci
```

Crea el archivo de entorno y genera la clave de la aplicación:

```bash
cp .env.example .env
php artisan key:generate
```

Edita `.env` y configura al menos la conexión a la base de datos. El ejemplo usa MySQL; crea antes una base de datos llamada `mimanga` o cambia `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` según tu instalación.

```dotenv
APP_NAME=MiManga
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mimanga
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

Ejecuta las migraciones. Para cargar también datos de ejemplo, usa `--seed`:

```bash
php artisan migrate --seed
```

El sembrador crea contenido de muestra y una cuenta local de demostración (`admin@example.com`, contraseña `password`). Esas credenciales son solo para desarrollo: no uses el sembrador ni esa cuenta en una instalación pública.

Inicia la aplicación y el servidor de desarrollo de Vite:

```bash
composer run dev
```

Abre [http://localhost:8000](http://localhost:8000).

## Configuración adicional

- **Correo:** configura las variables `MAIL_*` de `.env` para que funcionen la verificación de correo y el restablecimiento de contraseña.
- **reCAPTCHA:** el formulario de registro utiliza reCAPTCHA v2. Configura `RECAPTCHA_SITE_KEY` y `RECAPTCHA_SECRET_KEY` con las claves del entorno correspondiente.
- **Whakoom:** el la app requiere `WHAKOOM_USER` para proporcionar ediciones en inglés consultar `.env` para mas info.
- **Idioma:** la interfaz admite `es` y `en`; `APP_LOCALE` define el idioma inicial.

## Compilar los recursos para producción

```bash
npm run build
```

En un despliegue, configura las variables de entorno reales, el correo, reCAPTCHA y la base de datos; establece `APP_ENV=production` y `APP_DEBUG=false`.
