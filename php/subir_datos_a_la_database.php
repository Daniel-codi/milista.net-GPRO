<?php
// Habilitar CORS para permitir solicitudes desde otros dominios
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json"); // Enviar respuesta como JSON

// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$response = ["status" => "success", "messages" => []];

// Configuración de conexión a la base de datos
$servername = "srv1603.hstgr.io";
$username = "u469674049_Admin";
$password = "-YeshuaSalvador316";
$database = "u469674049_p2";

// Conexión a la base de datos
$conn = new mysqli($servername, $username, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    $response["status"] = "error";
    $response["messages"][] = "Conexión fallida: " . $conn->connect_error;
    echo json_encode($response);
    exit;
}
$response["messages"][] = "Conexión exitosa a la base de datos.";

// Verificar si el contenido JSON fue enviado
$jsonData = file_get_contents("php://input");
if (!$jsonData) {
    $response["status"] = "error";
    $response["messages"][] = "No se recibió contenido JSON válido.";
    echo json_encode($response);
    exit;
}

$data = json_decode($jsonData, true);
if ($data === null) {
    $response["status"] = "error";
    $response["messages"][] = "Error al decodificar el JSON.";
    echo json_encode($response);
    exit;
}

$response["messages"][] = "JSON recibido y decodificado correctamente.";


// Limpiar la tabla allAvailablePilots
$sql_truncate = "TRUNCATE TABLE allAvailablePilots";
if (!$conn->query($sql_truncate)) {
    $response["status"] = "error";
    $response["messages"][] = "Error al vaciar la tabla allAvailablePilots: " . $conn->error;
    echo json_encode($response);
    exit;
}
$response["messages"][] = "Tabla allAvailablePilots vaciada correctamente.";

// Procesar registros de la clave 'drivers'
if (!isset($data['drivers']) || !is_array($data['drivers'])) {
    $response["status"] = "error";
    $response["messages"][] = "El JSON no contiene un array válido de 'drivers'.";
    echo json_encode($response);
    exit;
}

$drivers = $data['drivers'];
$successCount = 0;
$errorCount = 0;

foreach ($drivers as $row) {
    if (empty($row['NAME']) || !isset($row['ID'])) {
        $errorCount++;
        continue;
    }

    $sql = "INSERT INTO allAvailablePilots (ID, NAME, NAT, OA, CON, TAL, EXP, AGG, TEI, STA, CHA, MOT, REP, AGE, WEI, RET, SAL, FEE, FAV, OFF) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $errorCount++;
        continue;
    }

    $stmt->bind_param("issiiiiiiiiiiiidddds",
        $row['ID'], $row['NAME'], $row['NAT'], $row['OA'], $row['CON'], $row['TAL'],
        $row['EXP'], $row['AGG'], $row['TEI'], $row['STA'], $row['CHA'], $row['MOT'],
        $row['REP'], $row['AGE'], $row['WEI'], $row['RET'], $row['SAL'], $row['FEE'],
        $row['FAV'], $row['OFF']
    );

    if (!$stmt->execute()) {
        $errorCount++;
    } else {
        $successCount++;
    }
}

$response["messages"][] = "Registros insertados correctamente en allAvailablePilots: $successCount";
$response["messages"][] = "Registros con error en allAvailablePilots: $errorCount";

// Actualizar la tabla Updates con el campo Last updated, para la Tabla allAvailablePilots
if (isset($data['Last updated'])) {
    $lastUpdated = $data['Last updated'];
    $sql_update = "UPDATE Updates SET LastUpdated = ? WHERE TableName = 'allAvailablePilots'";
    $stmt_update = $conn->prepare($sql_update);
    if ($stmt_update) {
        $stmt_update->bind_param("s", $lastUpdated);
        if ($stmt_update->execute()) {
            $response["messages"][] = "Campo LastUpdated actualizado correctamente en allAvailablePilots.";
        } else {
            $response["messages"][] = "Error al actualizar LastUpdated en allAvailablePilots: " . $stmt_update->error;
        }
    } else {
        $response["messages"][] = "Error al preparar la consulta para actualizar LastUpdated en allAvailablePilots: " . $conn->error;
    }
}

// Limpiar la tabla availableProdigies antes de actualizarla
$sql_truncate_prodigies = "TRUNCATE TABLE availableProdigies";
if (!$conn->query($sql_truncate_prodigies)) {
    $response["status"] = "error";
    $response["messages"][] = "Error al vaciar la tabla availableProdigies: " . $conn->error;
}

// Insertar datos en availableProdigies
$sql_insert = "INSERT INTO availableProdigies (ID, NAME, NAT, OA, CON, TAL, EXP, AGG, TEI, STA, CHA, MOT, REP, AGE, WEI, RET, SAL, FEE, FAV, OFF, Merits) 
SELECT ap.ID, ap.NAME, ap.NAT, ap.OA, ap.CON, ap.TAL, ap.EXP, ap.AGG, ap.TEI, ap.STA, ap.CHA, ap.MOT, ap.REP, ap.AGE, ap.WEI, ap.RET, ap.SAL, ap.FEE, ap.FAV, ap.OFF, lp.Merits 
FROM allAvailablePilots ap 
JOIN listProdigies lp ON ap.ID = lp.ID";

if ($conn->query($sql_insert)) {
    $response["messages"][] = "Tabla availableProdigies actualizada correctamente.";
} else {
    $response["messages"][] = "Error al actualizar availableProdigies: " . $conn->error;
}


// Actualizar la tabla Updates con el campo Last updated, para la Tabla availableProdigies
if (isset($data['Last updated'])) {
    $lastUpdated = $data['Last updated'];
    $sql_update = "UPDATE Updates SET LastUpdated = ? WHERE TableName = 'availableProdigies'";
    $stmt_update = $conn->prepare($sql_update);
    if ($stmt_update) {
        $stmt_update->bind_param("s", $lastUpdated);
        if ($stmt_update->execute()) {
            $response["messages"][] = "Campo LastUpdated actualizado correctamente en availableProdigies.";
        } else {
            $response["messages"][] = "Error al actualizar LastUpdated en availableProdigies: " . $stmt_update->error;
        }
    } else {
        $response["messages"][] = "Error al preparar la consulta para actualizar LastUpdated en availableProdigies: " . $conn->error;
    }
}


// Cerrar conexión
echo json_encode($response);
$conn->close();
?>
