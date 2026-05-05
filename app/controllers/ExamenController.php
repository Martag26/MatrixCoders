<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/index.php?url=login');
    exit;
}
if (($_SESSION['usuario_rol'] ?? '') !== 'USUARIO') {
    header('Location: ' . BASE_URL . '/index.php?url=crm');
    exit;
}

$usuarioId = (int)$_SESSION['usuario_id'];
$db        = (new Database())->connect();
$cursoId   = isset($_GET['curso']) ? (int)$_GET['curso'] : 0;

if ($cursoId <= 0) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Verificar matrícula (activa o revocada)
$stmtM = $db->prepare("SELECT estado FROM matricula WHERE usuario_id=? AND curso_id=?");
$stmtM->execute([$usuarioId, $cursoId]);
$matriculaEstado = $stmtM->fetchColumn();

if (!$matriculaEstado) {
    header('Location: ' . BASE_URL . '/index.php?url=detallecurso&id=' . $cursoId);
    exit;
}

// Si la matrícula está revocada, mostrar página de acceso perdido
if ($matriculaEstado === 'revocada') {
    $stmtC2 = $db->prepare("SELECT titulo FROM curso WHERE id=?");
    $stmtC2->execute([$cursoId]);
    $cursoRevocado = $stmtC2->fetch(PDO::FETCH_ASSOC);
    require __DIR__ . '/../views/examen/acceso_perdido.php';
    exit;
}

// Verificar que el alumno ha completado todas las lecciones y tareas entregables
$progresoExamen    = 100;
$leccionesVistas   = 0;
$totalLecciones    = 0;
$tareasRestantes   = 0;
$totalTareasEnt    = 0;

try {
    $stmtTotalLec = $db->prepare("
        SELECT COUNT(l.id) FROM leccion l
        JOIN unidad u ON l.unidad_id=u.id WHERE u.curso_id=?
    ");
    $stmtTotalLec->execute([$cursoId]);
    $totalLecciones = (int)$stmtTotalLec->fetchColumn();

    if ($totalLecciones > 0) {
        $stmtVistas = $db->prepare("
            SELECT COUNT(DISTINCT lv.leccion_id) FROM leccion_vista lv
            JOIN leccion l ON l.id=lv.leccion_id
            JOIN unidad u  ON l.unidad_id=u.id
            WHERE u.curso_id=? AND lv.usuario_id=?
        ");
        $stmtVistas->execute([$cursoId, $usuarioId]);
        $leccionesVistas = (int)$stmtVistas->fetchColumn();

        if ($leccionesVistas < $totalLecciones) {
            $progresoExamen     = round(($leccionesVistas / $totalLecciones) * 100);
            $leccionesRestantes = $totalLecciones - $leccionesVistas;
            require __DIR__ . '/../views/examen/bloqueado.php';
            exit;
        }
    }
} catch (Exception $e) { /* tabla no existe todavía — permitir acceso */ }

// Verificar tareas entregables completadas
try {
    $stmtTotTE = $db->prepare("SELECT COUNT(*) FROM tarea_entregable WHERE curso_id=?");
    $stmtTotTE->execute([$cursoId]);
    $totalTareasEnt = (int)$stmtTotTE->fetchColumn();

    if ($totalTareasEnt > 0) {
        $stmtPendTE = $db->prepare("
            SELECT COUNT(*) FROM tarea_entregable te
            LEFT JOIN entrega_entregable ee ON ee.tarea_id=te.id AND ee.alumno_id=?
            WHERE te.curso_id=? AND ee.id IS NULL
        ");
        $stmtPendTE->execute([$usuarioId, $cursoId]);
        $tareasRestantes = (int)$stmtPendTE->fetchColumn();

        if ($tareasRestantes > 0) {
            $progresoExamen     = round(($leccionesVistas / max($totalLecciones, 1)) * 100);
            $leccionesRestantes = 0;
            require __DIR__ . '/../views/examen/bloqueado.php';
            exit;
        }
    }
} catch (Exception $e) { /* tabla no existe todavía */ }

// Crear notificación "examen teórico disponible" si aún no existe
try {
    $stmtExId = $db->prepare("SELECT id FROM examen WHERE curso_id=? AND (tipo='test' OR tipo IS NULL OR tipo='') LIMIT 1");
    $stmtExId->execute([$cursoId]);
    $exIdForNotif = (int)($stmtExId->fetchColumn() ?: 0);
    if ($exIdForNotif) {
        $stmtChkNotif = $db->prepare("SELECT COUNT(*) FROM notificacion WHERE usuario_id=? AND tipo='examen_teorico' AND ref_id=?");
        $stmtChkNotif->execute([$usuarioId, $exIdForNotif]);
        if (!(int)$stmtChkNotif->fetchColumn()) {
            $tituloCursoNotif = $curso['titulo'] ?? '';
            $db->prepare("INSERT INTO notificacion (usuario_id,tipo,titulo,cuerpo,url_accion,ref_id) VALUES(?,?,?,?,?,?)")
               ->execute([$usuarioId, 'examen_teorico',
                   '¡Ya puedes hacer el examen teórico! — ' . $tituloCursoNotif,
                   'Has completado todas las lecciones y tareas. Accede al examen cuando estés listo.',
                   BASE_URL . '/index.php?url=examen&curso=' . $cursoId,
                   $exIdForNotif]);
        }
    }
} catch (Exception $e) {}

// Cargar examen tipo test del curso
$stmtEx = $db->prepare("SELECT * FROM examen WHERE curso_id=? AND (tipo='test' OR tipo IS NULL OR tipo='') LIMIT 1");
$stmtEx->execute([$cursoId]);
$examen = $stmtEx->fetch(PDO::FETCH_ASSOC);
if (!$examen) {
    header('Location: ' . BASE_URL . '/index.php?url=detallecurso&id=' . $cursoId);
    exit;
}

// Check if there's a practical exam for this course
$stmtPrac = $db->prepare("SELECT COUNT(*) FROM tarea_practica WHERE curso_id=?");
$stmtPrac->execute([$cursoId]);
$tieneExamenPractico = (int)$stmtPrac->fetchColumn() > 0;

// Datos del curso
$stmtC = $db->prepare("SELECT * FROM curso WHERE id=?");
$stmtC->execute([$cursoId]);
$curso = $stmtC->fetch(PDO::FETCH_ASSOC);

// Datos del usuario
$stmtU = $db->prepare("SELECT nombre FROM usuario WHERE id=?");
$stmtU->execute([$usuarioId]);
$usuario = $stmtU->fetch(PDO::FETCH_ASSOC);

// Resultado previo del test
$stmtR = $db->prepare("SELECT * FROM resultado_examen WHERE usuario_id=? AND examen_id=?");
$stmtR->execute([$usuarioId, $examen['id']]);
$resultadoPrevio = $stmtR->fetch(PDO::FETCH_ASSOC);

$maxIntentos    = 2;
$intentosUsados = (int)($resultadoPrevio['intentos'] ?? 0);

// Certificado previo
$certificado = null;
if ($resultadoPrevio && $resultadoPrevio['aprobado']) {
    $stmtCert = $db->prepare("SELECT * FROM certificado WHERE usuario_id=? AND curso_id=?");
    $stmtCert->execute([$usuarioId, $cursoId]);
    $certificado = $stmtCert->fetch(PDO::FETCH_ASSOC);
}

// ── Procesar envío del examen ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && !($resultadoPrevio && $resultadoPrevio['aprobado'])
    && $intentosUsados < $maxIntentos) {
    $stmtP = $db->prepare("SELECT * FROM pregunta WHERE examen_id=? ORDER BY orden");
    $stmtP->execute([$examen['id']]);
    $preguntas = $stmtP->fetchAll(PDO::FETCH_ASSOC);

    $correctas = 0;
    $total     = count($preguntas);

    foreach ($preguntas as $p) {
        $opcionId = isset($_POST['p' . $p['id']]) ? (int)$_POST['p' . $p['id']] : 0;
        if ($opcionId) {
            $stmtOp = $db->prepare("SELECT correcta FROM opcion WHERE id=? AND pregunta_id=?");
            $stmtOp->execute([$opcionId, $p['id']]);
            $correctas += (int)($stmtOp->fetchColumn() ?: 0);
        }
    }

    $nota     = $total > 0 ? round(($correctas / $total) * 10, 1) : 0.0;
    $aprobado = $nota >= (float)$examen['nota_minima'] ? 1 : 0;

    // Guardar o actualizar resultado incrementando el contador de intentos
    if ($resultadoPrevio) {
        $db->prepare("
            UPDATE resultado_examen
            SET nota=?, aprobado=?, realizado_en=datetime('now'), intentos=intentos+1
            WHERE usuario_id=? AND examen_id=?
        ")->execute([$nota, $aprobado, $usuarioId, $examen['id']]);
    } else {
        $db->prepare("
            INSERT INTO resultado_examen (usuario_id, examen_id, nota, aprobado, realizado_en, intentos)
            VALUES (?,?,?,?,datetime('now'),1)
        ")->execute([$usuarioId, $examen['id'], $nota, $aprobado]);
    }
    // Recargar para tener el contador actualizado
    $stmtR2 = $db->prepare("SELECT * FROM resultado_examen WHERE usuario_id=? AND examen_id=?");
    $stmtR2->execute([$usuarioId, $examen['id']]);
    $resultadoPrevio = $stmtR2->fetch(PDO::FETCH_ASSOC);
    $intentosUsados  = (int)($resultadoPrevio['intentos'] ?? 0);

    // Revocar matrícula si agota los 2 intentos sin aprobar
    if (!$aprobado && $intentosUsados >= $maxIntentos) {
        $db->prepare("UPDATE matricula SET estado='revocada' WHERE usuario_id=? AND curso_id=?")
           ->execute([$usuarioId, $cursoId]);
        // Notificar al usuario
        try {
            $db->prepare("INSERT INTO notificacion (usuario_id, tipo, titulo, cuerpo, url_accion) VALUES (?,?,?,?,?)")
               ->execute([$usuarioId, 'info',
                   'Has perdido el acceso al curso',
                   'Has agotado los 2 intentos del examen sin aprobar. Has perdido el acceso al curso y al certificado.',
                   BASE_URL . '/index.php?url=detallecurso&id=' . $cursoId
               ]);
        } catch (\Exception $e) {}
    }

    // Generar certificado si aprueba y el curso NO tiene examen práctico
    // (si tiene práctico, el certificado se emite tras la corrección)
    if ($aprobado) {
        $stmtHasPrac = $db->prepare("SELECT COUNT(*) FROM tarea_practica WHERE curso_id=?");
        $stmtHasPrac->execute([$cursoId]);
        $tieneExamenPractico = (int)$stmtHasPrac->fetchColumn() > 0;

        if (!$tieneExamenPractico) {
            $codigo = strtoupper(substr(md5($usuarioId . '-' . $cursoId . '-' . microtime()), 0, 12));
            $db->prepare("INSERT OR IGNORE INTO certificado (usuario_id, curso_id, emitido_en, codigo) VALUES (?,?,datetime('now'),?)")
               ->execute([$usuarioId, $cursoId, $codigo]);
        } else {
            // Notificar que el examen práctico ya está disponible
            try {
                $stmtChkPracNotif = $db->prepare("SELECT COUNT(*) FROM notificacion WHERE usuario_id=? AND tipo='examen_practico' AND ref_id=?");
                $stmtChkPracNotif->execute([$usuarioId, $cursoId]);
                if (!(int)$stmtChkPracNotif->fetchColumn()) {
                    $db->prepare("INSERT INTO notificacion (usuario_id,tipo,titulo,cuerpo,url_accion,ref_id) VALUES(?,?,?,?,?,?)")
                       ->execute([$usuarioId, 'examen_practico',
                           '¡Ya puedes hacer el examen práctico! — ' . ($curso['titulo'] ?? ''),
                           'Has aprobado el examen teórico. El examen práctico final ya está disponible.',
                           BASE_URL . '/index.php?url=examen-practico&curso=' . $cursoId,
                           $cursoId]);
                }
            } catch (Exception $e) {}
        }

        $stmtCert = $db->prepare("SELECT * FROM certificado WHERE usuario_id=? AND curso_id=?");
        $stmtCert->execute([$usuarioId, $cursoId]);
        $certificado = $stmtCert->fetch(PDO::FETCH_ASSOC);
    }

    $resultadoPrevio = ['nota' => $nota, 'aprobado' => $aprobado, 'realizado_en' => date('Y-m-d H:i:s')];

    require __DIR__ . '/../views/examen/resultado.php';
    exit;
}

// ── Si ya tiene resultado, mostrar resultado directamente ────────
if ($resultadoPrevio) {
    require __DIR__ . '/../views/examen/resultado.php';
    exit;
}

// ── Cargar preguntas con opciones ────────────────────────────────
$stmtP = $db->prepare("SELECT * FROM pregunta WHERE examen_id=? ORDER BY orden");
$stmtP->execute([$examen['id']]);
$preguntas = $stmtP->fetchAll(PDO::FETCH_ASSOC);

foreach ($preguntas as &$p) {
    $stmtOp = $db->prepare("SELECT * FROM opcion WHERE pregunta_id=? ORDER BY orden");
    $stmtOp->execute([$p['id']]);
    $p['opciones'] = $stmtOp->fetchAll(PDO::FETCH_ASSOC);
}
unset($p);

require __DIR__ . '/../views/examen/index.php';
