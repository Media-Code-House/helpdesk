# HelpDesk - Sistema de Gestión de Incidencias


Sistema diseñado para gestionar incidencias de forma eficiente, permitiendo el registro, seguimiento y resolución de problemas tecnológicos. Incluye notificaciones en tiempo real, reportes dinámicos y manejo de roles de usuarios. Su arquitectura modular permite futuras expansiones.


# Pasos de Instalación

# Instalar dependencias
composer install

# Configurar el archivo .env con las credenciales necesarias
nano .env

# Importar la base de datos
mysql -u usuario -p database_name < database.sql

# Asegurar permisos de escritura en la carpeta public/
chmod -R 775 public/


# Tecnologías Utilizadas
- Frontend: Materialize CSS, FullCalendar.js
- Backend: PHP 7.4+, MySQL
- Integraciones: QR, notificaciones, y reportes dinámicos

## Licencia

Este sistema es propiedad de Media Code House y está protegido bajo licencia registrada en DNDA.  
Su uso, distribución o modificación sin autorización está estrictamente prohibido.  
[Consulta los términos y condiciones completos aquí](TERMINOS_Y_CONDICIONES.md)
