CÓDIGO INCORPORANDO ETIQUETAS HTML SEMÁNTICAS PARA FAVORECER:

Accesibilidad (screen readers, navegación clara).
Mantenimiento (estructura limpia y entendible).
SEO (mejor relevancia).

CAMBIOS DESTACADOS:  
<HEADER> PARA LA CABECERA DEL SITIO. ✨
<nav> para los menús de navegación. 💚
<main> para el contenido principal. 🚀
<section> para bloques de contenido con aria-label. 💡
<article> y <footer> dentro de cada evento. 🌟
<ul> y <li> para listas de eventos. 📍

MODULARIZACION DEL PROYECTO:
/event-system/
│
├── index.php                (Controlador principal)
├── config.php               (Configuración de conexión DB)
├── functions.php            (Funciones reutilizables)
│
├── /partials/
│   ├── header.php           (Apertura HTML + Head + Header)
│   ├── nav.php              (Menú de navegación)
│   ├── search-form.php      (Formulario de búsqueda)
│   ├── event-list.php       (Listado de eventos)
│   ├── event-detail.php     (Vista detalle de un evento)
│   ├── footer.php           (Cierre de HTML + Footer)
│
└── /assets/ (opcional)
    ├── /css/                (Estilos adicionales)
    └── /js/                 (JS personalizado)


BASE DE DATOS:
-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS events_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE events_db;

-- Tabla de categorías
CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL
);

-- Tabla de eventos
CREATE TABLE events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  location VARCHAR(255) NOT NULL,
  event_date DATETIME NOT NULL,
  image_url VARCHAR(255),
  latitude DECIMAL(10,8),
  longitude DECIMAL(11,8),
  category_id INT,
  FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Insertar categorías de ejemplo
INSERT INTO categories (name) VALUES 
('Conciertos'),
('Exposiciones'),
('Conferencias'),
('Talleres');

-- Insertar eventos de ejemplo
INSERT INTO events (title, description, location, event_date, image_url, latitude, longitude, category_id) VALUES
('Concierto de Rock', 'Gran concierto de rock en el centro de la ciudad.', 'Auditorio Ciudad', '2025-05-10 21:00:00', 'https://via.placeholder.com/600x400.png?text=Concierto', 43.361914, -5.849388, 1),
('Exposición de Arte', 'Exposición de pintura contemporánea.', 'Museo de Arte Moderno', '2025-05-15 10:00:00', 'https://via.placeholder.com/600x400.png?text=Exposición', 43.366889, -5.833407, 2),
('Charla sobre Tecnología', 'Evento sobre IA y desarrollo web.', 'Centro Tecnológico', '2025-05-20 18:30:00', 'https://via.placeholder.com/600x400.png?text=Charla', 43.367124, -5.850687, 3),
('Taller de Fotografía', 'Curso intensivo de fotografía urbana.', 'Casa de Cultura', '2025-05-25 17:00:00', 'https://via.placeholder.com/600x400.png?text=Taller', 43.363558, -5.847365, 4);

