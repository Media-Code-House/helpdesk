# Pruebas de Caja Blanca para la Carpeta `auth`

## Índice
1. [login.php](#loginphp)
2. [logout.php](#logoutphp)
3. [register.php](#registerphp)
4. [show_qr.php](#show_qrphp)
5. [verify.php](#verifyphp)

---

## login.php

### Descripción
Archivo encargado de manejar la autenticación de usuarios en el sistema.

### Pruebas
1. **Flujo de inicio de sesión exitoso**
   - Dado: Un usuario con credenciales válidas.
   - Cuando: Envía `email` y `password` correctos.
   - Entonces: Redirige al dashboard y establece la sesión.

2. **Flujo de inicio de sesión fallido**
   - Dado: Credenciales inválidas.
   - Cuando: Envía `email` o `password` incorrectos.
   - Entonces: Devuelve un mensaje de error.

3. **Validación de campos vacíos**
   - Dado: Formulario enviado sin completar `email` o `password`.
   - Entonces: Devuelve error de "Campos obligatorios".

4. **Intento de inicio de sesión con usuario inactivo**
   - Dado: Usuario registrado pero con estado inactivo.
   - Cuando: Intenta iniciar sesión.
   - Entonces: Devuelve mensaje de "Usuario no activo".

---

## logout.php

### Descripción
Archivo encargado de cerrar la sesión del usuario.

### Pruebas
1. **Cerrar sesión correctamente**
   - Dado: Usuario autenticado.
   - Cuando: Accede a este archivo.
   - Entonces: Cierra la sesión y redirige a la página de login.

2. **Acceso directo sin sesión activa**
   - Dado: Usuario no autenticado.
   - Cuando: Accede directamente.
   - Entonces: Redirige a la página de login sin errores.

---

## register.php

### Descripción
Archivo encargado de registrar nuevos usuarios en el sistema.

### Pruebas
1. **Registro exitoso**
   - Dado: Datos válidos (`email`, `password`, `confirm_password`).
   - Cuando: Los datos son enviados correctamente.
   - Entonces: Crea un nuevo usuario y redirige al login.

2. **Registro fallido por contraseñas no coincidentes**
   - Dado: `password` y `confirm_password` diferentes.
   - Entonces: Devuelve error "Las contraseñas no coinciden".

3. **Registro con correo ya existente**
   - Dado: Un correo que ya está registrado.
   - Entonces: Devuelve error "El correo ya está en uso".

4. **Validación de formato de correo**
   - Dado: Un correo con formato inválido.
   - Entonces: Devuelve error "Correo no válido".

---

## show_qr.php

### Descripción
Archivo encargado de generar y mostrar un código QR para autenticación de dos factores (2FA).

### Pruebas
1. **Generación de código QR**
   - Dado: Un usuario autenticado.
   - Cuando: Solicita esta página.
   - Entonces: Genera y muestra el código QR correctamente.

2. **Acceso sin sesión activa**
   - Dado: Usuario no autenticado.
   - Cuando: Accede a este archivo.
   - Entonces: Redirige a la página de login.

3. **Validación de claves secretas**
   - Dado: Usuario sin clave secreta en la base de datos.
   - Entonces: Genera y almacena una nueva clave.

---

## verify.php

### Descripción
Archivo encargado de verificar el código de autenticación de dos factores (2FA).

### Pruebas
1. **Verificación exitosa**
   - Dado: Usuario autenticado y código 2FA válido.
   - Cuando: Envía el código correcto.
   - Entonces: Permite acceso al sistema.

2. **Verificación fallida**
   - Dado: Usuario autenticado pero código 2FA incorrecto.
   - Entonces: Devuelve error "Código incorrecto".

3. **Expiración del código**
   - Dado: Código generado hace más de 30 segundos.
   - Entonces: Devuelve error "Código expirado".

4. **Acceso sin sesión activa**
   - Dado: Usuario no autenticado.
   - Cuando: Envía una solicitud.
   - Entonces: Redirige a la página de login.

---






# Pruebas de Caja Blanca - Módulo Calendario

## Introducción
Este documento describe las pruebas de caja blanca diseñadas para validar los componentes internos del módulo calendario. Estas pruebas verifican el correcto funcionamiento del código, siguiendo cada posible flujo de ejecución dentro de los archivos clave.

---

## Índice de Pruebas
1. [calendario.php](#calendario.php)
2. [cargar_eventos.php](#cargar_eventos.php)
3. [guardar_evento.php](#guardar_evento.php)
4. [marcar_pagado.php](#marcar_pagado.php)
5. [gastos_fijos_agregar.php](#gastos_fijos_agregar.php)

---

## calendario.php

### Objetivo
Verificar la interacción con el frontend, la visualización de eventos y la carga inicial de datos desde el backend.

### Flujos Probados
1. Inicialización del calendario con eventos.
2. Correcta renderización de eventos:
   - Gastos fijos.
   - Estimaciones de incidencias.
   - Eventos.
3. Funcionamiento del botón para agregar eventos:
   - Reseteo del formulario.
   - Apertura del modal.

### Casos de Prueba
- **CP1:** Verificar que el calendario se inicialice sin errores.
- **CP2:** Validar que los eventos se carguen correctamente desde `cargar_eventos.php`.
- **CP3:** Confirmar que al hacer clic en un evento se ejecuten las acciones configuradas.
- **CP4:** Comprobar que el modal de agregar evento abre y cierra correctamente.

---

## cargar_eventos.php

### Objetivo
Asegurar que el archivo devuelva correctamente los eventos en formato JSON.

### Flujos Probados
1. Consultar los gastos fijos y generar eventos para el calendario según la periodicidad.
2. Consultar las estimaciones de incidencias y mapearlas a eventos.
3. Consultar los eventos personalizados y agregarlos a la respuesta.

### Casos de Prueba
- **CP5:** Verificar que los eventos de `gastos_fijos` incluyan las fechas calculadas.
- **CP6:** Validar que las estimaciones de incidencias se incluyan correctamente.
- **CP7:** Confirmar que los eventos personalizados tienen los campos `id`, `title`, `start` y `color`.

---

## guardar_evento.php

### Objetivo
Validar que un nuevo evento sea correctamente guardado en la base de datos.

### Flujos Probados
1. Recepción de datos enviados por AJAX.
2. Validación de campos requeridos (`descripcion`, `fecha`, `hora`).
3. Inserción de los datos en la tabla `eventos`.

### Casos de Prueba
- **CP8:** Verificar que un evento con todos los datos válidos sea guardado.
- **CP9:** Confirmar que se devuelve un error cuando falta un campo requerido.
- **CP10:** Validar que se use la hora en el formato correcto.

---

## marcar_pagado.php

### Objetivo
Confirmar que los gastos fijos puedan marcarse como pagados y reflejarse en la tabla `cuenta`.

### Flujos Probados
1. Verificar que el gasto fijo existe.
2. Registrar la transacción en la tabla `cuenta`.
3. Actualizar el estado del gasto fijo como pagado.

### Casos de Prueba
- **CP11:** Validar que se registre correctamente un gasto fijo pagado.
- **CP12:** Confirmar que se devuelve un error si el `id_gasto` no existe.
- **CP13:** Verificar que la tabla `cuenta` registre la transacción con los valores esperados.

---

## gastos_fijos_agregar.php

### Objetivo
Asegurar que los gastos fijos sean registrados correctamente en la base de datos.

### Flujos Probados
1. Recepción de datos enviados por AJAX.
2. Validación de campos (`descripcion`, `monto_estimado`, `periodo`, `periodicidad`).
3. Inserción de datos en la tabla `gastos_fijos`.

### Casos de Prueba
- **CP14:** Confirmar que un gasto fijo con datos válidos sea registrado.
- **CP15:** Verificar que no se inserte un gasto fijo si falta algún campo obligatorio.
- **CP16:** Validar que el campo `periodicidad` esté dentro del rango de 1 a 28.

---

## Resultados Esperados
1. Todas las pruebas deben ejecutarse sin errores.
2. La salida esperada debe coincidir con los datos de entrada en cada flujo.
3. Los mensajes de error deben ser descriptivos y fáciles de entender.






# Pruebas de Caja Blanca - Módulo Cuentas

## Introducción
Este documento describe las pruebas de caja blanca realizadas en el módulo de cuentas para verificar el correcto flujo interno de datos, la validación de entrada y la ejecución de funciones en cada archivo relacionado.

---

## Índice de Pruebas
1. [cuentas_agregar.php](#cuentas_agregar.php)
2. [cuentas_editar.php](#cuentas_editar.php)
3. [cuentas_graficos.php](#cuentas_graficos.php)
4. [cuentas_historial.php](#cuentas_historial.php)
5. [cuentas_principal.php](#cuentas_principal.php)
6. [cuentas_reportes.php](#cuentas_reportes.php)

---

## cuentas_agregar.php

### Objetivo
Verificar que las transacciones sean agregadas correctamente a la tabla `cuenta`.

### Flujos Probados
1. Recepción y validación de datos del formulario:
   - Campos obligatorios: `banco`, `descripcion`, `valor`, `tipo_transaccion`, `categoria`.
2. Inserción de datos en la tabla `cuenta`.
3. Manejo de errores en caso de datos incompletos o incorrectos.

### Casos de Prueba
- **CP1:** Confirmar que una transacción válida sea registrada.
- **CP2:** Validar que se emita un error si falta un campo obligatorio.
- **CP3:** Verificar que los valores `tipo_transaccion` y `categoria` coincidan con los permitidos.

---

## cuentas_editar.php

### Objetivo
Garantizar que las transacciones existentes puedan ser editadas correctamente.

### Flujos Probados
1. Verificación de la existencia del registro.
2. Actualización de los datos de la transacción en la tabla `cuenta`.
3. Validación de datos enviados por el formulario.

### Casos de Prueba
- **CP4:** Confirmar que los cambios válidos sean actualizados en la base de datos.
- **CP5:** Verificar que se muestre un error si el registro no existe.
- **CP6:** Validar que no se permita editar campos con valores fuera del rango permitido.

---

## cuentas_graficos.php

### Objetivo
Generar gráficos a partir de los datos de ingresos y egresos almacenados en la tabla `cuenta`.

### Flujos Probados
1. Consulta de datos agrupados por `banco` y `tipo_transaccion`.
2. Formateo de los datos para generar gráficos de ingresos y egresos.
3. Manejo de fechas para generar gráficos mensuales.

### Casos de Prueba
- **CP7:** Confirmar que los datos se agrupan correctamente por `banco`.
- **CP8:** Validar que los gráficos mensuales de ingresos y egresos reflejen los datos de la base de datos.
- **CP9:** Verificar que el gráfico muestre valores en cero si no hay datos para un periodo.

---

## cuentas_historial.php

### Objetivo
Mostrar el historial de transacciones con filtros aplicados.

### Flujos Probados
1. Filtrado por campos: `banco`, `tipo_transaccion`, `fecha`, `usuario`.
2. Consulta y paginación de los resultados del historial.
3. Ordenamiento de datos por fecha.

### Casos de Prueba
- **CP10:** Validar que el filtro por `banco` y `tipo_transaccion` devuelva los resultados correctos.
- **CP11:** Confirmar que la paginación limite los resultados mostrados.
- **CP12:** Verificar que el ordenamiento por fecha sea correcto.

---

## cuentas_principal.php

### Objetivo
Visualizar el resumen de cuentas y realizar acciones como editar o eliminar.

### Flujos Probados
1. Consulta de todas las transacciones.
2. Renderización de los datos en la tabla principal.
3. Interacción con los botones de editar y eliminar.

### Casos de Prueba
- **CP13:** Verificar que todas las transacciones se carguen correctamente en la tabla.
- **CP14:** Validar que el botón de eliminar elimine un registro y actualice la tabla.
- **CP15:** Confirmar que el botón de editar abra el formulario con los datos correctos.

---

## cuentas_reportes.php

### Objetivo
Generar reportes detallados de transacciones en formato PDF.

### Flujos Probados
1. Selección de datos para el reporte según filtros.
2. Generación del archivo PDF con los datos formateados.
3. Manejo de errores si no hay datos para generar el reporte.

### Casos de Prueba
- **CP16:** Confirmar que el reporte PDF incluya todos los datos seleccionados.
- **CP17:** Validar que se genere un mensaje de error si no hay datos para el rango seleccionado.
- **CP18:** Verificar que el PDF muestre correctamente los totales y subtotales.

---

## Resultados Esperados
1. Todos los flujos deben ejecutarse correctamente sin errores.
2. Los datos deben reflejarse de forma precisa en las consultas, gráficos y reportes.
3. Las validaciones deben bloquear cualquier entrada inválida y emitir mensajes de error claros.

---

# Pruebas de Caja Blanca - Módulo Incidencias

## Introducción
Este documento detalla las pruebas de caja blanca realizadas sobre el módulo de incidencias. El objetivo es validar la lógica interna y flujos de ejecución de cada archivo dentro del módulo.

---

## Índice de Pruebas
1. [assign.php](#assignphp)
2. [chat.php](#chatphp)
3. [create.php](#createphp)
4. [details.php](#detailsphp)
5. [edit.php](#editphp)
6. [historial.php](#historialphp)
7. [list_chats.php](#list_chatsphp)
8. [list.php](#listphp)
9. [notificaciones.php](#notificacionesphp)
10. [report_chat.php](#report_chatphp)
11. [user_chat.php](#user_chatphp)

---

## assign.php

### Objetivo
Garantizar que las incidencias sean correctamente asignadas a los usuarios.

### Flujos Probados
1. Validación de entrada:
   - ID de la incidencia.
   - Usuario asignado.
2. Actualización de la tabla de asignaciones.
3. Retorno de mensajes de éxito o error.

### Casos de Prueba
- **CP1:** Confirmar que la asignación se registra correctamente en la base de datos.
- **CP2:** Verificar que se manejen errores al asignar una incidencia inexistente.

---

## chat.php

### Objetivo
Permitir la comunicación entre usuarios en el contexto de una incidencia.

### Flujos Probados
1. Carga de mensajes existentes.
2. Envío de nuevos mensajes.
3. Validación de ID de la incidencia y usuario.

### Casos de Prueba
- **CP3:** Validar que se carguen correctamente los mensajes relacionados con la incidencia.
- **CP4:** Verificar que los mensajes enviados se almacenen en la base de datos.

---

## create.php

### Objetivo
Registrar nuevas incidencias en el sistema.

### Flujos Probados
1. Validación de campos obligatorios:
   - Título.
   - Descripción.
   - Prioridad.
2. Inserción en la tabla de incidencias.
3. Manejo de errores por datos faltantes o inválidos.

### Casos de Prueba
- **CP5:** Confirmar que las incidencias válidas se registren correctamente.
- **CP6:** Verificar que se bloqueen incidencias con campos incompletos.

---

## details.php

### Objetivo
Mostrar la información detallada de una incidencia.

### Flujos Probados
1. Consulta de la incidencia por ID.
2. Renderización de los detalles en la interfaz.

### Casos de Prueba
- **CP7:** Validar que los datos correctos de la incidencia se recuperen de la base de datos.
- **CP8:** Verificar que se emita un error si el ID no existe.

---

## edit.php

### Objetivo
Permitir la edición de los datos de una incidencia existente.

### Flujos Probados
1. Carga de datos actuales de la incidencia.
2. Validación de campos editados.
3. Actualización de la base de datos.

### Casos de Prueba
- **CP9:** Confirmar que los datos modificados se actualicen correctamente en la base de datos.
- **CP10:** Verificar que no se permitan valores inválidos o vacíos.

---

## historial.php

### Objetivo
Listar las acciones y cambios realizados sobre una incidencia.

### Flujos Probados
1. Consulta del historial por ID de incidencia.
2. Paginación y ordenamiento de los resultados.

### Casos de Prueba
- **CP11:** Validar que se muestren correctamente todas las acciones realizadas.
- **CP12:** Verificar que los filtros y paginación funcionen adecuadamente.

---

## list_chats.php

### Objetivo
Mostrar una lista de chats relacionados con todas las incidencias asignadas a un usuario.

### Flujos Probados
1. Consulta de chats por usuario.
2. Formateo de la lista para mostrar título, usuario y última interacción.

### Casos de Prueba
- **CP13:** Confirmar que los chats relacionados se recuperen correctamente.
- **CP14:** Validar que la lista se actualice al agregar nuevos mensajes.

---

## list.php

### Objetivo
Listar todas las incidencias con filtros aplicables.

### Flujos Probados
1. Filtrado por:
   - Prioridad.
   - Estado.
   - Fecha de creación.
2. Consulta y renderización de resultados.

### Casos de Prueba
- **CP15:** Validar que los filtros devuelvan los resultados correctos.
- **CP16:** Confirmar que las incidencias se muestren correctamente en la tabla.

---

## notificaciones.php

### Objetivo
Enviar notificaciones a los usuarios relacionados con incidencias.

### Flujos Probados
1. Verificación de usuarios relacionados con una incidencia.
2. Envío de notificaciones a través de correo o sistema interno.

### Casos de Prueba
- **CP17:** Confirmar que las notificaciones se envíen correctamente.
- **CP18:** Verificar que se manejen errores si un usuario no tiene métodos de contacto válidos.

---

## report_chat.php

### Objetivo
Generar reportes de los chats relacionados con una incidencia.

### Flujos Probados
1. Consulta de mensajes por ID de incidencia.
2. Formateo del reporte en formato PDF o texto plano.

### Casos de Prueba
- **CP19:** Validar que el reporte incluya todos los mensajes.
- **CP20:** Confirmar que el archivo generado sea accesible y descargable.

---

## user_chat.php

### Objetivo
Permitir a los usuarios acceder a sus chats activos.

### Flujos Probados
1. Consulta de chats asignados al usuario.
2. Renderización de los chats con estados y últimas interacciones.

### Casos de Prueba
- **CP21:** Confirmar que solo se muestren chats relacionados con el usuario autenticado.
- **CP22:** Validar que los mensajes nuevos se reflejen en tiempo real.

---

# Pruebas de Caja Blanca - Módulo Usuarios

## Introducción
Este documento detalla las pruebas de caja blanca realizadas sobre los archivos del módulo de usuarios. Se revisa la lógica interna y flujos de ejecución para garantizar el correcto funcionamiento del sistema.

---

## Índice de Pruebas
1. [assign_role.php](#assign_rolephp)
2. [list_users.php](#list_usersphp)
3. [dashboard.php](#dashboardphp)
4. [header.php](#headerphp)

---

## assign_role.php

### Objetivo
Asignar roles específicos a los usuarios del sistema.

### Flujos Probados
1. Validación de permisos del usuario actual para realizar la asignación.
2. Verificación de parámetros recibidos (`id_usuario`, `rol`).
3. Actualización de roles en la base de datos.
4. Manejo de errores por:
   - Usuario no encontrado.
   - Rol inválido.

### Casos de Prueba
- **CP1:** Confirmar que un rol válido se asigna correctamente.
- **CP2:** Validar que un usuario inexistente genera un mensaje de error.
- **CP3:** Verificar que los roles se limiten a los valores permitidos (admin, usuario, etc.).

---

## list_users.php

### Objetivo
Listar todos los usuarios del sistema con sus roles y estados.

### Flujos Probados
1. Consulta de usuarios en la base de datos.
2. Renderización de datos en una tabla:
   - Nombre.
   - Correo electrónico.
   - Rol.
   - Estado.
3. Aplicación de filtros (por rol, estado, etc.).
4. Paginación de resultados.

### Casos de Prueba
- **CP4:** Confirmar que la tabla muestra todos los usuarios activos.
- **CP5:** Verificar que los filtros de rol y estado devuelvan los resultados esperados.
- **CP6:** Validar que la paginación funcione correctamente al superar el límite de registros.

---

## dashboard.php

### Objetivo
Mostrar un resumen de las estadísticas del sistema.

### Flujos Probados
1. Consulta de métricas clave:
   - Número total de usuarios.
   - Usuarios activos e inactivos.
   - Distribución de roles.
2. Cálculo de porcentajes y totales.
3. Renderización en gráficos o tablas.

### Casos de Prueba
- **CP7:** Validar que las métricas se calculen correctamente desde la base de datos.
- **CP8:** Confirmar que los gráficos se actualicen con los datos actuales.
- **CP9:** Verificar que se muestren mensajes de error si no hay datos disponibles.

---

## header.php

### Objetivo
Incluir la barra de navegación y validar la sesión del usuario.

### Flujos Probados
1. Validación de la sesión activa.
2. Renderización de opciones del menú según el rol del usuario.
3. Redirección en caso de inactividad o sesión inválida.

### Casos de Prueba
- **CP10:** Confirmar que los usuarios sin sesión activa se redirigen al login.
- **CP11:** Verificar que los elementos del menú cambian según el rol del usuario.
- **CP12:** Validar que los errores de sesión se manejen adecuadamente.

---

