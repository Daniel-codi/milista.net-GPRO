<?php
// Código PHP para subir los datos del archivo .CSV descargado, a la Base de datos u469674049_p2 en la tabla allAvailablePilots

// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuración de conexión a la base de datos
$servername = "srv1603.hstgr.io"; // nombre del servidor MySQL remoto
$username = "u469674049_Admin"; // Usuario
$password = "-YeshuaSalvador316"; // Contraseña
$database = "u469674049_p2"; // Nombre de la base de datos

// Conexión a la base de datos
$conn = new mysqli($servername, $username, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    error_log("Conexión fallida: " . $conn->connect_error);
    die("Conexión fallida: " . $conn->connect_error);
}
error_log("Conexión exitosa a la base de datos.");

// Limpiar la tabla existente
$sql_truncate = "TRUNCATE TABLE allAvailablePilots";
if (!$conn->query($sql_truncate)) {
    error_log("Error al vaciar la tabla: " . $conn->error);
    die("Error al vaciar la tabla: " . $conn->error);
}
error_log("Tabla allAvailablePilots vaciada correctamente.");

// Verificar si el archivo CSV fue enviado
if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
    error_log("Archivo CSV recibido correctamente.");
    $fileTmpPath = $_FILES['csv_file']['tmp_name'];
    $csvData = file_get_contents($fileTmpPath);

    if (!$csvData) {
        error_log("El archivo CSV está vacío o no se pudo leer correctamente.");
        die("El archivo CSV está vacío o no se pudo leer correctamente.");
    }

    $lines = explode("\n", $csvData);
    error_log("Número total de líneas en el archivo: " . count($lines));

    // Procesar el encabezado y las filas
    $headers = str_getcsv(array_shift($lines), ";"); // Leer encabezados
    error_log("Encabezados procesados: " . implode(", ", $headers));

    $successCount = 0;
    $errorCount = 0;

    foreach ($lines as $line) {
        if (trim($line) === '') continue; // Saltar líneas vacías

        $row = str_getcsv($line, ";");
        if (count($row) !== count($headers)) {
            error_log("Fila ignorada por integridad: " . implode(", ", $row));
            $errorCount++;
            continue; // Validar integridad de la fila
        }

        // Validar columnas críticas
        if (empty($row[1])) { // NAME no puede ser NULL o vacío
            error_log("Fila ignorada porque NAME está vacío: " . implode(", ", $row));
            $errorCount++;
            continue;
        }

        // Mapear columnas a las de la tabla
        $sql = "INSERT INTO allAvailablePilots (ID, NAME, NAT, OA, CON, TAL, EXP, AGG, TEI, STA, CHA, MOT, REP, AGE, WEI, RET, SAL, FEE, FAV, OFF) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            error_log("Error al preparar la consulta: " . $conn->error);
            $errorCount++;
            continue;
        }

        $stmt->bind_param("issiiiiiiiiiiiidddds",
            $row[0], // ID
            $row[1], // NAME
            $row[2], // NAT
            $row[3], // OA
            $row[4], // CON
            $row[5], // TAL
            $row[6], // EXP
            $row[7], // AGG
            $row[8], // TEI
            $row[9], // STA
            $row[10], // CHA
            $row[11], // MOT
            $row[12], // REP
            $row[13], // AGE
            $row[14], // WEI
            $row[15], // RET
            $row[16], // SAL
            $row[17], // FEE
            $row[18], // FAV
            $row[19]  // OFF
        );

        if (!$stmt->execute()) {
            error_log("Error al insertar fila: " . $stmt->error);
            $errorCount++;
            echo "Error al insertar fila: " . $stmt->error . "\n";
        } else {
            error_log("Fila insertada correctamente: " . implode(", ", $row));
            $successCount++;
        }
    }

    error_log("Filas insertadas correctamente: $successCount");
    error_log("Filas con error: $errorCount");

    echo "Tabla actualizada con éxito. Filas insertadas: $successCount, Filas con error: $errorCount.";
    error_log("Tabla allAvailablePilots actualizada con éxito.");
} else {
    error_log("No se recibió un archivo CSV válido.");
    echo "No se recibió un archivo CSV válido.";
}

$conn->close();
error_log("Conexión cerrada.");
?>
