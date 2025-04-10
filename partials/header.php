<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistema de Gestión de Eventos (MySQLi Procedural)</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" integrity="sha256-kLaT2GOSpHechhsozzB+flnD+zUyjE2LlfWPgU04xyI=" crossorigin=""/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">


  <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js" integrity="sha256-WBkoXOwTeyKclOHuWtc+i2uENFpDZ9YPdf5Hf+D7ewM=" crossorigin=""></script>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
</head>
<body class="bg-gray-50 dark:bg-gray-900">
  <?php
  // Configuración de la base de datos
  $host = 'localhost';
  $dbname = 'events_db';
  $user = 'root';
  $pass = 'root'; // Asegúrate de que esta es tu contraseña correcta


  // Establecer conexión con la base de datos usando MySQLi procedural
  $conn = mysqli_connect($host, $user, $pass, $dbname);


  // Verificar la conexión
  if (mysqli_connect_errno()) {
      echo "Error de conexión a la base de datos: " . mysqli_connect_error();
      die();
  }


  // Establecer el conjunto de caracteres a UTF-8
  if (!mysqli_set_charset($conn, "utf8mb4")) {
      printf("Error al cargar el conjunto de caracteres utf8mb4: %s\n", mysqli_error($conn));
      // Considera finalizar aquí si el conjunto de caracteres es crítico
  }


  // Obtener el modo de vista actual (lista, cuadrícula, tabla, mapa, calendario)
  $view_mode = isset($_GET['view']) ? $_GET['view'] : 'list';


  // Obtener la consulta de búsqueda si está presente
  $search_query = isset($_GET['search']) ? trim($_GET['search']) : ''; // Eliminar espacios en blanco


  // Obtener el filtro de categoría si está presente
  $category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;


  // Obtener el ID del evento para la vista de detalle
  $event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;


  // Obtener categorías para el filtro usando mysqli_query
  $categories = [];
  $sql_categories = "SELECT id, name FROM categories ORDER BY name";
  $result_categories = mysqli_query($conn, $sql_categories);


  if ($result_categories) {
      $categories = mysqli_fetch_all($result_categories, MYSQLI_ASSOC);
      mysqli_free_result($result_categories); // Liberar el conjunto de resultados
  } else {
      echo "Error al obtener las categorías: " . mysqli_error($conn);
      // Decide si deseas finalizar aquí o continuar sin categorías
  }


  // Consulta base para eventos
  $query = "SELECT e.*, c.name as category_name
            FROM events e
            LEFT JOIN categories c ON e.category_id = c.id
            WHERE 1";
  $params = [];
  $types = ""; // Cadena para los tipos de parámetros (i=entero, s=cadena, d=doble, b=blob)


  // Agregar condición de búsqueda si se proporciona una consulta de búsqueda
  if (!empty($search_query)) {
      $query .= " AND (e.title LIKE ? OR e.description LIKE ?)";
      $search_param = "%" . $search_query . "%";
      $params[] = $search_param; // Agregar parámetro para el título
      $params[] = $search_param; // Agregar parámetro para la descripción
      $types .= "ss"; // Dos parámetros de tipo cadena
  }


  // Agregar filtro de categoría si se selecciona
  if ($category_filter > 0) {
      $query .= " AND e.category_id = ?";
      $params[] = $category_filter; // Agregar parámetro de ID de categoría
      $types .= "i"; // Un parámetro de tipo entero
  }


  // Agregar orden por fecha
  $query .= " ORDER BY e.event_date";


  // Preparar y ejecutar la consulta para eventos usando declaraciones preparadas de MySQLi
  $events = [];
  $stmt = mysqli_prepare($conn, $query);


  if ($stmt) {
      // Vincular parámetros si existen
      if (!empty($params)) {
          // Usar el operador splat (...) para pasar elementos del array como argumentos individuales
          if (!mysqli_stmt_bind_param($stmt, $types, ...$params)) {
              echo "Error al vincular parámetros: " . mysqli_stmt_error($stmt);
              die();
          }
      }


      // Ejecutar la declaración
      if (mysqli_stmt_execute($stmt)) {
          // Obtener el conjunto de resultados
          $result_events = mysqli_stmt_get_result($stmt);
          if ($result_events) {
              // Obtener todos los resultados
              $events = mysqli_fetch_all($result_events, MYSQLI_ASSOC);
              // El objeto de resultados se libera implícitamente cuando se cierra la declaración
          } else {
              echo "Error al obtener el conjunto de resultados: " . mysqli_stmt_error($stmt);
          }
      } else {
          echo "Error al ejecutar la declaración: " . mysqli_stmt_error($stmt);
      }


      // Cerrar la declaración
      mysqli_stmt_close($stmt);
  } else {
      echo "Error al preparar la declaración: " . mysqli_error($conn);
      die(); // Error crítico si no se puede preparar la declaración
  }
  ?>


  <div class="container mx-auto px-4 py-8">
      <header class="mb-8">
          <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-4">Sistema de Gestión de Eventos (MySQLi Procedural)</h1>


          <div class="flex flex-col md:flex-row items-center justify-between space-y-4 md:space-y-0 mb-6">
              <form action="" method="GET" class="w-full md:w-auto flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-4">
                  <input type="hidden" name="view" value="<?php echo htmlspecialchars($view_mode); ?>">
                  <div class="flex-grow">
                      <input type="text" name="search" placeholder="Buscar eventos..."
                             value="<?php echo htmlspecialchars($search_query); ?>"
                             class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-base">
                  </div>
                  <div class="w-full md:w-auto">
                      <select name="category" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-base">
                          <option value="0">Todas las categorías</option>
                          <?php foreach ($categories as $category): ?>
                              <option value="<?php echo $category['id']; ?>" <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                                  <?php echo htmlspecialchars($category['name']); ?>
                              </option>
                          <?php endforeach; ?>
                      </select>
                  </div>
                  <div>
                      <button type="submit" class="w-full md:w-auto px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                          Buscar
                      </button>
                  </div>
              </form>


              <div class="flex space-x-2">
                  <?php
                      // Función auxiliar para construir la cadena de consulta para los enlaces de vista
                      function build_view_link_params($current_search, $current_category) {
                          $link_params = '';
                          if (!empty($current_search)) {
                              $link_params .= '&search='.urlencode($current_search);
                          }
                          if ($current_category > 0) {
                              $link_params .= '&category='.$current_category;
                          }
                          return $link_params;
                      }
                      $link_extra_params = build_view_link_params($search_query, $category_filter);
                  ?>
                  <a href="?view=list<?php echo $link_extra_params; ?>"
                     class="px-4 py-2 rounded-lg <?php echo $view_mode == 'list' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                      Lista
                  </a>
                  <a href="?view=grid<?php echo $link_extra_params; ?>"
                     class="px-4 py-2 rounded-lg <?php echo $view_mode == 'grid' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                      Cuadrícula
                  </a>
                  <a href="?view=table<?php echo $link_extra_params; ?>"
                     class="px-4 py-2 rounded-lg <?php echo $view_mode == 'table' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                      Tabla
                  </a>
                  <a href="?view=map<?php echo $link_extra_params; ?>"
                     class="px-4 py-2 rounded-lg <?php echo $view_mode == 'map' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                      Mapa
                  </a>
                  <a href="?view=calendar<?php echo $link_extra_params; ?>"
                     class="px-4 py-2 rounded-lg <?php echo $view_mode == 'calendar' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                      Calendario
                  </a>
              </div>
          </div>
      </header>