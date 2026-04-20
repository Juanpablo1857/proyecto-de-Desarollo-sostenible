<?php
// =====================================================
// SMEM – API Backend (api.php)
// Coloca este archivo en: C:\xampp\htdocs\smem\api\api.php
// =====================================================

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

// ── Conexión a la BD ──────────────────────────────────
$host   = 'localhost';
$dbname = 'smem_db';
$user   = 'root';
$pass   = '';          // XAMPP por defecto no tiene contraseña

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Conexión fallida: ' . $e->getMessage()]);
    exit();
}

// ── Router ────────────────────────────────────────────
$method   = $_SERVER['REQUEST_METHOD'];
$resource = $_GET['resource'] ?? '';
$id       = $_GET['id'] ?? null;
$body     = json_decode(file_get_contents('php://input'), true) ?? [];

function ok($data = [], $msg = 'OK') {
    echo json_encode(['success' => true, 'message' => $msg, 'data' => $data]);
}
function err($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $msg]);
}

// ─────────────────────────────────────────────────────
// REGISTROS
// ─────────────────────────────────────────────────────
if ($resource === 'registros') {

    if ($method === 'GET') {
        $search = $_GET['search'] ?? '';
        $zona   = $_GET['zona']   ?? '';
        $estado = $_GET['estado'] ?? '';
        $sql = "SELECT * FROM registros WHERE 1=1";
        $params = [];
        if ($search) { $sql .= " AND (codigo LIKE ? OR tecnico LIKE ? OR zona LIKE ?)"; array_push($params, "%$search%", "%$search%", "%$search%"); }
        if ($zona)   { $sql .= " AND zona = ?";   $params[] = $zona; }
        if ($estado) { $sql .= " AND estado = ?"; $params[] = $estado; }
        $sql .= " ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        ok($stmt->fetchAll());
    }

    elseif ($method === 'POST') {
        // Generar código autom.
        $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(codigo,5) AS UNSIGNED)) AS mx FROM registros");
        $row  = $stmt->fetch();
        $next = ($row['mx'] ?? 0) + 1;
        $codigo = 'REC-' . str_pad($next, 3, '0', STR_PAD_LEFT);

        $stmt = $pdo->prepare("INSERT INTO registros
            (codigo,fecha,zona,latitud,longitud,temperatura,ph,salinidad,oxigeno,corriente,tecnico,estado,notas)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $codigo,
            $body['fecha']       ?? date('Y-m-d'),
            $body['zona']        ?? 'Zona Norte',
            $body['latitud']     ?? '0.0000° N',
            $body['longitud']    ?? '0.0000° W',
            $body['temperatura'] ?? 19.0,
            $body['ph']          ?? 8.0,
            $body['salinidad']   ?? 35.0,
            $body['oxigeno']     ?? 7.0,
            $body['corriente']   ?? 2.0,
            $body['tecnico']     ?? 'Técnico',
            $body['estado']      ?? 'Pendiente',
            $body['notas']       ?? ''
        ]);
        $new = $pdo->query("SELECT * FROM registros WHERE id = " . $pdo->lastInsertId())->fetch();
        ok($new, 'Registro guardado');
    }

    elseif ($method === 'PUT' && $id) {
        $stmt = $pdo->prepare("UPDATE registros SET
            fecha=?, zona=?, latitud=?, longitud=?,
            temperatura=?, ph=?, salinidad=?, oxigeno=?, corriente=?,
            tecnico=?, estado=?, notas=?
            WHERE id=?");
        $stmt->execute([
            $body['fecha']       ?? date('Y-m-d'),
            $body['zona']        ?? 'Zona Norte',
            $body['latitud']     ?? '0.0000° N',
            $body['longitud']    ?? '0.0000° W',
            $body['temperatura'] ?? 19.0,
            $body['ph']          ?? 8.0,
            $body['salinidad']   ?? 35.0,
            $body['oxigeno']     ?? 7.0,
            $body['corriente']   ?? 2.0,
            $body['tecnico']     ?? '',
            $body['estado']      ?? 'Pendiente',
            $body['notas']       ?? '',
            $id
        ]);
        $upd = $pdo->query("SELECT * FROM registros WHERE id=$id")->fetch();
        ok($upd, 'Registro actualizado');
    }

    elseif ($method === 'DELETE' && $id) {
        $pdo->prepare("DELETE FROM registros WHERE id=?")->execute([$id]);
        ok([], 'Registro eliminado');
    }

    else { err('Método no permitido', 405); }

// ─────────────────────────────────────────────────────
// ALERTAS
// ─────────────────────────────────────────────────────
} elseif ($resource === 'alertas') {

    if ($method === 'GET') {
        $stmt = $pdo->query("SELECT * FROM alertas ORDER BY created_at DESC LIMIT 20");
        ok($stmt->fetchAll());
    }
    elseif ($method === 'POST') {
        $stmt = $pdo->prepare("INSERT INTO alertas (tipo,mensaje,zona) VALUES (?,?,?)");
        $stmt->execute([$body['tipo'] ?? 'info', $body['mensaje'] ?? '', $body['zona'] ?? '']);
        ok([], 'Alerta creada');
    }
    elseif ($method === 'PUT' && $id) {
        $pdo->prepare("UPDATE alertas SET leida=1 WHERE id=?")->execute([$id]);
        ok([], 'Alerta marcada como leída');
    }
    else { err('Método no permitido', 405); }

// ─────────────────────────────────────────────────────
// ESPECIES
// ─────────────────────────────────────────────────────
} elseif ($resource === 'especies') {

    if ($method === 'GET') {
        $stmt = $pdo->query("SELECT * FROM especies ORDER BY avistamientos DESC");
        ok($stmt->fetchAll());
    }
    elseif ($method === 'POST') {
        $stmt = $pdo->prepare("INSERT INTO especies (nombre,nombre_cientifico,estado_conservacion,avistamientos,tendencia,zona) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$body['nombre']??'',$body['nombre_cientifico']??'',$body['estado_conservacion']??'Vulnerable',$body['avistamientos']??0,$body['tendencia']??'stable',$body['zona']??'']);
        ok([], 'Especie registrada');
    }
    elseif ($method === 'PUT' && $id) {
        $stmt = $pdo->prepare("UPDATE especies SET avistamientos=avistamientos+1, ultima_vez=CURDATE() WHERE id=?");
        $stmt->execute([$id]);
        ok([], 'Avistamiento registrado');
    }
    else { err('Método no permitido', 405); }

// ─────────────────────────────────────────────────────
// ESTADÍSTICAS (dashboard)
// ─────────────────────────────────────────────────────
} elseif ($resource === 'stats') {

    $total     = $pdo->query("SELECT COUNT(*) FROM registros")->fetchColumn();
    $validados = $pdo->query("SELECT COUNT(*) FROM registros WHERE estado='Validado'")->fetchColumn();
    $pendientes= $pdo->query("SELECT COUNT(*) FROM registros WHERE estado='Pendiente'")->fetchColumn();
    $revision  = $pdo->query("SELECT COUNT(*) FROM registros WHERE estado='En Revisión'")->fetchColumn();
    $avgPh     = $pdo->query("SELECT ROUND(AVG(ph),2) FROM registros")->fetchColumn();
    $avgTemp   = $pdo->query("SELECT ROUND(AVG(temperatura),1) FROM registros")->fetchColumn();
    $avgSal    = $pdo->query("SELECT ROUND(AVG(salinidad),2) FROM registros")->fetchColumn();
    $avgOxy    = $pdo->query("SELECT ROUND(AVG(oxigeno),1) FROM registros")->fetchColumn();
    $especies  = $pdo->query("SELECT SUM(avistamientos) FROM especies")->fetchColumn();
    $alertas   = $pdo->query("SELECT COUNT(*) FROM alertas WHERE leida=0")->fetchColumn();
    $activos   = $pdo->query("SELECT COUNT(*) FROM puntos_monitoreo WHERE estado='active'")->fetchColumn();
    $totalPts  = $pdo->query("SELECT COUNT(*) FROM puntos_monitoreo")->fetchColumn();

    // pH últimos 9 días para la gráfica
    $phTrend = $pdo->query("SELECT DATE_FORMAT(fecha,'%d %b') as lbl, ROUND(AVG(ph),2) as ph
        FROM registros GROUP BY fecha ORDER BY fecha DESC LIMIT 9")->fetchAll();

    ok([
        'total'     => (int)$total,
        'validados' => (int)$validados,
        'pendientes'=> (int)$pendientes,
        'revision'  => (int)$revision,
        'avgPh'     => $avgPh,
        'avgTemp'   => $avgTemp,
        'avgSal'    => $avgSal,
        'avgOxy'    => $avgOxy,
        'especies'  => (int)$especies,
        'alertas'   => (int)$alertas,
        'activos'   => (int)$activos,
        'totalPts'  => (int)$totalPts,
        'phTrend'   => array_reverse($phTrend)
    ]);

// ─────────────────────────────────────────────────────
// PUNTOS DE MONITOREO
// ─────────────────────────────────────────────────────
} elseif ($resource === 'puntos') {

    if ($method === 'GET') {
        $stmt = $pdo->query("SELECT * FROM puntos_monitoreo ORDER BY nombre");
        ok($stmt->fetchAll());
    }
    else { err('Método no permitido', 405); }

// ─────────────────────────────────────────────────────
// PING / test de conexión
// ─────────────────────────────────────────────────────
} elseif ($resource === 'ping') {
    ok(['time' => date('Y-m-d H:i:s'), 'db' => 'smem_db'], 'Conexión exitosa ✅');

} else {
    err('Recurso no encontrado. Usa: registros, alertas, especies, stats, puntos, ping', 404);
}
