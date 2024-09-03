<?php
// Conectar a la base de datos
$conn = new mysqli('localhost', 'root', '', 'proyecto_pp2');

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Definir el número de registros por página
$registros_por_pagina = 5; // Puedes ajustar este valor según sea necesario

if (isset($_GET['pagina']) && is_numeric($_GET['pagina'])) {
    $pagina_actual = (int)$_GET['pagina'];
} else {
    $pagina_actual = 1;
}

$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Obtener el término de búsqueda enviado por AJAX
$search = isset($_GET['filtro']) ? $_GET['filtro'] : '';
$search_param = "%" . $conn->real_escape_string($search) . "%";

// Realizar la consulta a la base de datos
$sql = "SELECT id_barrio, descripcion_barrio, id_localidad, descripcion_localidad 
        FROM barrio 
        JOIN localidad 
        ON barrio.rela_localidad = localidad.id_localidad
        WHERE descripcion_barrio LIKE ? 
        OR descripcion_localidad LIKE ?
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssii", $search_param, $search_param, $registros_por_pagina, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Crear el HTML de la tabla
$tabla = '<table border="1"><tr><th>ID Barrio</th><th>Barrio</th><th>ID Localidad</th><th>Localidad</th></tr>';
while ($row = $result->fetch_assoc()) {
    $tabla .= '<tr>';
    $tabla .= '<td>' . htmlspecialchars($row['id_barrio']) . '</td>';
    $tabla .= '<td>' . htmlspecialchars($row['descripcion_barrio']) . '</td>';
    $tabla .= '<td>' . htmlspecialchars($row['id_localidad']) . '</td>';
    $tabla .= '<td>' . htmlspecialchars($row['descripcion_localidad']) . '</td>';
    $tabla .= '</tr>';
}
$tabla .= '</table>';

// Contar el número total de registros
$sql_count = "SELECT COUNT(*) as total 
              FROM barrio
              JOIN localidad 
              ON barrio.rela_localidad = localidad.id_localidad
              WHERE descripcion_barrio LIKE ? 
              OR descripcion_localidad LIKE ?";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param("ss", $search_param, $search_param);
$stmt_count->execute();
$result_count = $stmt_count->get_result();
$total_items = $result_count->fetch_assoc()['total'];
$total_pages = ceil($total_items / $registros_por_pagina);

// Devolver la tabla como JSON
echo json_encode(array(
    "tabla" => $tabla,
    "total_pages" => $total_pages,
    "current_page" => $pagina_actual
));

// Cerrar la conexión
$conn->close();
?>
