<?php
// Código PHP para subir los datos del archivo JSON descargado, a la Base de datos u469674049_p2 en la tabla allAvailablePilots

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

// Verificar si el contenido JSON fue enviado
$jsonData = file_get_contents("php://input");
if (!$jsonData) {
    error_log("No se recibió contenido JSON válido.");
    die("No se recibió contenido JSON válido.");
}

$data = json_decode($jsonData, true);
if ($data === null) {
    error_log("Error al decodificar el JSON.");
    die("Error al decodificar el JSON.");
}

error_log("JSON recibido y decodificado correctamente.");

// Actualizar la tabla Updates con el campo Last updated
if (isset($data['Last updated'])) {
    $lastUpdated = $data['Last updated'];
    $sql_update = "UPDATE Updates SET LastUpdated = ? WHERE ID = 1";
    $stmt_update = $conn->prepare($sql_update);
    if ($stmt_update) {
        $stmt_update->bind_param("s", $lastUpdated);
        if ($stmt_update->execute()) {
            error_log("Campo LastUpdated actualizado correctamente.");
        } else {
            error_log("Error al actualizar LastUpdated: " . $stmt_update->error);
        }
    } else {
        error_log("Error al preparar la consulta para actualizar LastUpdated: " . $conn->error);
    }
}

// Limpiar la tabla allAvailablePilots
$sql_truncate = "TRUNCATE TABLE allAvailablePilots";
if (!$conn->query($sql_truncate)) {
    error_log("Error al vaciar la tabla: " . $conn->error);
    die("Error al vaciar la tabla: " . $conn->error);
}
error_log("Tabla allAvailablePilots vaciada correctamente.");

// Procesar registros de la clave 'drivers'
if (!isset($data['drivers']) || !is_array($data['drivers'])) {
    error_log("El JSON no contiene un array válido de 'drivers'.");
    die("El JSON no contiene un array válido de 'drivers'.");
}

$drivers = $data['drivers'];
$successCount = 0;
$errorCount = 0;

foreach ($drivers as $row) {
    // Validar columnas críticas
    if (empty($row['NAME']) || !isset($row['ID'])) { // NAME no puede ser NULL o vacío, ID debe existir
        error_log("Registro ignorado: faltan columnas críticas. Datos: " . json_encode($row));
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

    // Crear variables separadas para bind_param
    $id = $row['ID'] ?? null;
    $name = $row['NAME'] ?? null;
    $nat = $row['NAT'] ?? null;
    $oa = $row['OA'] ?? null;
    $con = $row['CON'] ?? null;
    $tal = $row['TAL'] ?? null;
    $exp = $row['EXP'] ?? null;
    $agg = $row['AGG'] ?? null;
    $tei = $row['TEI'] ?? null;
    $sta = $row['STA'] ?? null;
    $cha = $row['CHA'] ?? null;
    $mot = $row['MOT'] ?? null;
    $rep = $row['REP'] ?? null;
    $age = $row['AGE'] ?? null;
    $wei = $row['WEI'] ?? null;
    $ret = $row['RET'] ?? null;
    $sal = $row['SAL'] ?? null;
    $fee = $row['FEE'] ?? null;
    $fav = $row['FAV'] ?? null;
    $off = $row['OFF'] ?? null;

    $stmt->bind_param("issiiiiiiiiiiiidddds",
        $id,
        $name,
        $nat,
        $oa,
        $con,
        $tal,
        $exp,
        $agg,
        $tei,
        $sta,
        $cha,
        $mot,
        $rep,
        $age,
        $wei,
        $ret,
        $sal,
        $fee,
        $fav,
        $off
    );

    if (!$stmt->execute()) {
        error_log("Error al insertar registro: " . $stmt->error);
        $errorCount++;
    } else {
        error_log("Registro insertado correctamente: " . json_encode($row));
        $successCount++;
    }
}

error_log("Registros insertados correctamente: $successCount");
error_log("Registros con error: $errorCount");

echo "Tabla actualizada con éxito. Registros insertados: $successCount, Registros con error: $errorCount.";
$conn->close();
error_log("Conexión cerrada.");
?>
