# **Sistema de Venta de Pasajes de Buses Interprovinciales**

Este es un proyecto de sistema web para la gestión y venta de pasajes de una empresa de buses interprovinciales en Perú, similar a Flores Hnos. El sistema está desarrollado en **PHP** puro y se conecta a una base de datos **MySQL (MariaDB)**, haciendo un uso intensivo de Vistas, Triggers y Procedimientos Almacenados (Stored Procedures).

El proyecto está diseñado para ser ejecutado en un entorno de desarrollo local como **XAMPP**.

## **Características Principales**

El sistema se divide en dos módulos principales: el portal de clientes y el panel de administración.

### **Portal de Cliente**

* **Autenticación:** Sistema de registro e inicio de sesión.  
* **Buscador de Viajes:** Permite buscar viajes por Origen, Destino y Fecha de Salida.  
* **Selección de Asientos:** Interfaz gráfica para seleccionar uno o varios asientos en un bus de 1 o 2 pisos.  
* **Flujo de Compra:**  
  * Cálculo de total en tiempo real.  
  * Formulario de pago simulado (no requiere pasarela de pago real).  
  * Generación de un boleto/reserva al finalizar la compra.  
* **Perfil de Usuario:**  
  * Ver y editar datos personales (excepto DNI).  
  * Ver historial de compras y transacciones realizadas.  
  * Reimprimir boletos de compras anteriores.

### **Panel de Administración**

* **Dashboard:** Página principal con estadísticas clave del sistema (Total de Usuarios, Buses, Rutas, Ventas, etc.).  
* **Gestión (CRUD):**  
  * **Gestión de Buses:** Añadir, editar y eliminar buses (CRUD).  
  * **Gestión de Usuarios:** Añadir, editar y eliminar usuarios y administradores.  
  * **Gestión de Rutas:** CRUD de las rutas disponibles (Origen, Destino, Precios).  
  * **Gestión de Viajes:** Programar nuevos viajes (asignando buses y rutas).  
* **Reportes:**  
  * Módulo para generar 5 reportes diferentes basados en los Procedimientos Almacenados, con filtros por fecha y ruta.

## **Tecnologías Utilizadas**

* **Backend:** PHP 8+  
* **Base de Datos:** MySQL / MariaDB (via XAMPP)  
* **Frontend:** HTML5, CSS3, JavaScript (Vanilla)  
* **Iconos:** Google Material Symbols

## **Instalación y Puesta en Marcha (XAMPP)**

Sigue estos pasos para ejecutar el proyecto en tu entorno local:

1. **Inicia XAMPP:** Asegúrate de que los servicios de **Apache** y **MySQL** estén corriendo. (Puerto MySQL por defecto: 3306).  
2. **Clona el Repositorio:** Clona este proyecto dentro de la carpeta htdocs de tu instalación de XAMPP.  
   * Ej: C:\\xampp\\htdocs\\flores-hrnos  
3. **Crea la Base de Datos:**  
   * Ve a http://localhost/phpmyadmin en tu navegador.  
   * Crea una nueva base de datos llamada db\_buses (asegúrate de usar cotejamiento utf8mb4\_general\_ci).  
4. **Importa la Base de Datos:**  
   * Selecciona la base de datos db\_buses.  
   * Ve a la pestaña "Importar".  
   * Importa el archivo principal db\_buses.sql que se encuentra en la raíz o en la carpeta /sql/.  
5. **Importa los Scripts de Corrección y Reportes:**  
   * **¡MUY IMPORTANTE\!** Después de importar db\_buses.sql, debes importar los siguientes archivos (en este orden) desde la carpeta /sql/ para que la aplicación funcione correctamente:  
     1. fix\_sp\_crear\_reserva.sql  
     2. fix\_schema\_pagos.sql  
     3. sp\_reportes\_buses.sql  
     4. datos\_prueba\_rutas.sql (Opcional, para añadir más rutas)  
     5. datos\_prueba\_viajes.sql (Opcional, para añadir más viajes)  
6. **Accede al Proyecto:**  
   * Abre tu navegador y ve a http://localhost/nombre\_de\_la\_carpeta\_del\_proyecto/ (ej. http://localhost/flores-hrnos/).

## **Credenciales de Prueba**

Puedes usar las siguientes cuentas (creadas en db\_buses.sql) para probar el sistema:

### **Administrador**

* **Usuario:** admin1@flores.com  
* **Contraseña:** 123

### **Cliente**

* **Usuario:** pedro@gmail.com  
* **Contraseña:** 123  
*   
  * **Usuario:** ana@gmail.com  
* **Contraseña:** 123

*Este proyecto fue desarrollado con fines académicos y de demostración.*