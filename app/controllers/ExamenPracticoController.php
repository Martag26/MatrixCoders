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

// Verify enrollment
$stmtM = $db->prepare("SELECT COUNT(*) FROM matricula WHERE usuario_id=? AND curso_id=? AND estado IN ('activa','completado')");
$stmtM->execute([$usuarioId, $cursoId]);
if (!(int)$stmtM->fetchColumn()) {
    header('Location: ' . BASE_URL . '/index.php?url=detallecurso&id=' . $cursoId);
    exit;
}

// The theory exam must be passed before accessing the practical exam
try {
    $stmtTh = $db->prepare("SELECT id FROM examen WHERE curso_id=? AND (tipo='test' OR tipo IS NULL OR tipo='') LIMIT 1");
    $stmtTh->execute([$cursoId]);
    $theoryId = $stmtTh->fetchColumn();
    if ($theoryId) {
        $stmtTRes = $db->prepare("SELECT aprobado FROM resultado_examen WHERE usuario_id=? AND examen_id=?");
        $stmtTRes->execute([$usuarioId, $theoryId]);
        $resTest = $stmtTRes->fetch(PDO::FETCH_ASSOC);
        if (!$resTest || !$resTest['aprobado']) {
            header('Location: ' . BASE_URL . '/index.php?url=examen&curso=' . $cursoId . '&pendiente_teoria=1');
            exit;
        }
    }
} catch (Exception $e) {}

// Load course
$stmtC = $db->prepare("SELECT * FROM curso WHERE id=?");
$stmtC->execute([$cursoId]);
$curso = $stmtC->fetch(PDO::FETCH_ASSOC);
if (!$curso) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Load practical exam metadata
$stmtEx = $db->prepare("SELECT * FROM examen WHERE curso_id=? AND tipo='practico' LIMIT 1");
$stmtEx->execute([$cursoId]);
$examenPractico = $stmtEx->fetch(PDO::FETCH_ASSOC);

// Load practical tasks
try {
    $stmtT = $db->prepare("SELECT * FROM tarea_practica WHERE curso_id=? ORDER BY orden,id");
    $stmtT->execute([$cursoId]);
    $tareas = $stmtT->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $tareas = [];
}

if (empty($tareas)) {
    header('Location: ' . BASE_URL . '/index.php?url=detallecurso&id=' . $cursoId);
    exit;
}

// Load existing submissions for this student
$entregasExistentes = [];
try {
    $stmtE = $db->prepare("SELECT * FROM entrega_practica WHERE alumno_id=? AND curso_id=?");
    $stmtE->execute([$usuarioId, $cursoId]);
    foreach ($stmtE->fetchAll(PDO::FETCH_ASSOC) as $e) {
        $entregasExistentes[$e['tarea_id']] = $e;
    }
} catch (Exception $e) {}

// Check if all tasks already submitted
$totalTareas      = count($tareas);
$totalEntregadas  = count($entregasExistentes);
$todasEntregadas  = $totalEntregadas >= $totalTareas;

// Comprobar si todas las entregas están revisadas y generar certificado automáticamente
$certificadoPractico = null;
$practicoAprobado    = false;
$practicoReprobado   = false;
$notaMediaFinal      = 0.0;
$notaMinimaPracFinal = 0.0;
if ($todasEntregadas) {
    $notaMinimaPrac = (float)($examenPractico['nota_minima'] ?? 5.0);
    $notaMedia      = 0;
    $revisadas      = 0;
    $todasRevisadas = true;

    foreach ($entregasExistentes as $ent) {
        if (!(int)$ent['revisado']) { $todasRevisadas = false; break; }
        $notaMedia += (float)($ent['nota'] ?? 0);
        $revisadas++;
    }

    if ($todasRevisadas && $revisadas === $totalTareas) {
        $notaMedia = $revisadas > 0 ? round($notaMedia / $revisadas, 1) : 0;
        $aprobadoPorMedia = $notaMedia >= $notaMinimaPrac;
        $notaMediaFinal      = $notaMedia;
        $notaMinimaPracFinal = $notaMinimaPrac;

        if ($aprobadoPorMedia) {
            $practicoAprobado = true;

            // Generar certificado si no existe
            $stmtCertEx = $db->prepare("SELECT * FROM certificado WHERE usuario_id=? AND curso_id=?");
            $stmtCertEx->execute([$usuarioId, $cursoId]);
            $certificadoPractico = $stmtCertEx->fetch(PDO::FETCH_ASSOC);

            if (!$certificadoPractico) {
                $codigoPrac = strtoupper(substr(md5($usuarioId . '-prac-' . $cursoId . '-' . microtime()), 0, 12));
                $db->prepare("INSERT OR IGNORE INTO certificado (usuario_id, curso_id, emitido_en, codigo) VALUES (?,?,datetime('now'),?)")
                   ->execute([$usuarioId, $cursoId, $codigoPrac]);
                $stmtCertEx2 = $db->prepare("SELECT * FROM certificado WHERE usuario_id=? AND curso_id=?");
                $stmtCertEx2->execute([$usuarioId, $cursoId]);
                $certificadoPractico = $stmtCertEx2->fetch(PDO::FETCH_ASSOC);
            }

            // Marcar matrícula como completada
            $db->prepare("UPDATE matricula SET estado='completado' WHERE usuario_id=? AND curso_id=? AND estado='activa'")
               ->execute([$usuarioId, $cursoId]);
            // Notificar aprobación (solo una vez; el CRM puede haberla enviado ya)
            try {
                $chkC = $db->prepare("SELECT COUNT(*) FROM notificacion WHERE usuario_id=? AND tipo='curso_completado' AND ref_id=?");
                $chkC->execute([$usuarioId, $cursoId]);
                if (!(int)$chkC->fetchColumn()) {
                    $db->prepare("INSERT INTO notificacion (usuario_id, tipo, titulo, cuerpo, url_accion, ref_id) VALUES (?,?,?,?,?,?)")
                       ->execute([$usuarioId, 'curso_completado',
                           '🎓 ¡Has completado el curso!',
                           '¡Enhorabuena! Has superado el examen práctico de "' . ($curso['titulo'] ?? '') . '" con un ' . number_format($notaMedia, 1) . '/10. Tu certificado ya está disponible.',
                           BASE_URL . '/index.php?url=examen-practico&curso=' . $cursoId,
                           $cursoId,
                       ]);
                }
            } catch (\Exception $e) {}
        } else {
            // Suspendido en el práctico: revocar matrícula
            $practicoReprobado = true;
            $db->prepare("UPDATE matricula SET estado='revocada' WHERE usuario_id=? AND curso_id=?")
               ->execute([$usuarioId, $cursoId]);
            try {
                $chkF = $db->prepare("SELECT COUNT(*) FROM notificacion WHERE usuario_id=? AND tipo='curso_fallido' AND ref_id=?");
                $chkF->execute([$usuarioId, $cursoId]);
                if (!(int)$chkF->fetchColumn()) {
                    $db->prepare("INSERT INTO notificacion (usuario_id, tipo, titulo, cuerpo, url_accion, ref_id) VALUES (?,?,?,?,?,?)")
                       ->execute([$usuarioId, 'curso_fallido',
                           '❌ Has perdido el acceso al curso',
                           'No has superado el examen práctico de "' . ($curso['titulo'] ?? '') . '" (nota media: ' . number_format($notaMedia, 1) . '/10). Deberás volver a matricularte para intentarlo de nuevo.',
                           BASE_URL . '/index.php?url=detallecurso&id=' . $cursoId,
                           $cursoId,
                       ]);
                }
            } catch (\Exception $e) {}
        }
    }
}

// Primera lección (para el botón Volver al curso del sidebar)
$stmtFL = $db->prepare("SELECT l.id FROM leccion l JOIN unidad u ON l.unidad_id=u.id WHERE u.curso_id=? ORDER BY u.orden,u.id,l.orden,l.id LIMIT 1");
$stmtFL->execute([$cursoId]);
$primeraLeccionId = (int)$stmtFL->fetchColumn();

// Unidades y lecciones para el sidebar
$unidades = [];
try {
    $stmtUn = $db->prepare("SELECT * FROM unidad WHERE curso_id=? ORDER BY orden,id");
    $stmtUn->execute([$cursoId]);
    $unidades = $stmtUn->fetchAll(PDO::FETCH_ASSOC);
    foreach ($unidades as &$u) {
        $stmtLec = $db->prepare("SELECT id,titulo FROM leccion WHERE unidad_id=? ORDER BY orden,id");
        $stmtLec->execute([$u['id']]);
        $u['lecciones'] = $stmtLec->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($u);
} catch (Exception $e) { $unidades = []; }

// Lecciones vistas por el alumno
$leccionesVistasPrac = [];
try {
    $stmtLV = $db->prepare("SELECT lv.leccion_id FROM leccion_vista lv JOIN leccion l ON l.id=lv.leccion_id JOIN unidad u ON l.unidad_id=u.id WHERE u.curso_id=? AND lv.usuario_id=?");
    $stmtLV->execute([$cursoId, $usuarioId]);
    foreach ($stmtLV->fetchAll(PDO::FETCH_COLUMN) as $lid) $leccionesVistasPrac[$lid] = true;
} catch (Exception $e) {}

// Check deadline
$plazoSuperado = false;
if ($examenPractico && !empty($examenPractico['fecha_entrega'])) {
    $plazoSuperado = (new DateTime()) > (new DateTime($examenPractico['fecha_entrega']));
}

// Load user info
$stmtU = $db->prepare("SELECT nombre FROM usuario WHERE id=?");
$stmtU->execute([$usuarioId]);
$usuario = $stmtU->fetch(PDO::FETCH_ASSOC);

// ── Handle POST: save submissions ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    if ($plazoSuperado) {
        echo json_encode(['ok' => false, 'error' => 'El plazo de entrega ha finalizado']);
        exit;
    }

    $tareaId  = (int)($_POST['tarea_id'] ?? 0);
    $respTxt  = trim($_POST['respuesta_texto'] ?? '');

    if (!$tareaId) {
        echo json_encode(['ok' => false, 'error' => 'ID de tarea inválido']);
        exit;
    }

    // Verify task belongs to this course
    $stmtVer = $db->prepare("SELECT id FROM tarea_practica WHERE id=? AND curso_id=?");
    $stmtVer->execute([$tareaId, $cursoId]);
    if (!$stmtVer->fetchColumn()) {
        echo json_encode(['ok' => false, 'error' => 'Tarea no encontrada']);
        exit;
    }

    $archivoPath = null;
    if (!empty($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $file    = $_FILES['archivo'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf','doc','docx','zip','rar','txt','png','jpg','jpeg','mp4','py'];
        if (!in_array($ext, $allowed)) {
            echo json_encode(['ok' => false, 'error' => 'Tipo de archivo no permitido']);
            exit;
        }
        if ($file['size'] > 50 * 1024 * 1024) {
            echo json_encode(['ok' => false, 'error' => 'Archivo muy grande (máx. 50 MB)']);
            exit;
        }
        $destDir = __DIR__ . '/../../public/uploads/practicos/';
        if (!is_dir($destDir)) mkdir($destDir, 0755, true);
        $nombreArchivo = 'u' . $usuarioId . '_t' . $tareaId . '_' . time() . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $destDir . $nombreArchivo)) {
            $archivoPath = BASE_URL . '/uploads/practicos/' . $nombreArchivo;
        }
    }

    if (!$respTxt && !$archivoPath) {
        echo json_encode(['ok' => false, 'error' => 'Debes escribir una respuesta o adjuntar un archivo']);
        exit;
    }

    try {
        $db->prepare("
            INSERT INTO entrega_practica (alumno_id, tarea_id, curso_id, respuesta_texto, archivo)
            VALUES (?,?,?,?,?)
            ON CONFLICT(alumno_id, tarea_id) DO UPDATE SET
              respuesta_texto=excluded.respuesta_texto,
              archivo=COALESCE(excluded.archivo, archivo),
              entregado_en=datetime('now'),
              revisado=0
        ")->execute([$usuarioId, $tareaId, $cursoId, $respTxt, $archivoPath]);
    } catch (Exception $e) {
        // Fallback for SQLite versions without ON CONFLICT DO UPDATE
        $check = $db->prepare("SELECT id FROM entrega_practica WHERE alumno_id=? AND tarea_id=?");
        $check->execute([$usuarioId, $tareaId]);
        if ($check->fetchColumn()) {
            $db->prepare("UPDATE entrega_practica SET respuesta_texto=?,archivo=COALESCE(?,archivo),entregado_en=datetime('now'),revisado=0 WHERE alumno_id=? AND tarea_id=?")
               ->execute([$respTxt, $archivoPath, $usuarioId, $tareaId]);
        } else {
            $db->prepare("INSERT INTO entrega_practica (alumno_id,tarea_id,curso_id,respuesta_texto,archivo) VALUES(?,?,?,?,?)")
               ->execute([$usuarioId, $tareaId, $cursoId, $respTxt, $archivoPath]);
        }
    }

    // Recount
    $stmtCount = $db->prepare("SELECT COUNT(*) FROM entrega_practica WHERE alumno_id=? AND curso_id=?");
    $stmtCount->execute([$usuarioId, $cursoId]);
    $nuevasEntregadas = (int)$stmtCount->fetchColumn();

    // Notificar al instructor: recoge el nombre del alumno y el título de la tarea
    try {
        $nombreAlumno  = trim((string)($_SESSION['usuario_nombre'] ?? 'Un alumno'));
        $stmtTitTarea  = $db->prepare("SELECT titulo FROM tarea_practica WHERE id=?");
        $stmtTitTarea->execute([$tareaId]);
        $titTarea = $stmtTitTarea->fetchColumn() ?: 'Tarea práctico';

        // Reúne todos los instructores del curso (principal + tabla curso_instructor)
        $instructorIds = [];
        $stmtInst = $db->prepare("SELECT instructor_id FROM curso WHERE id=? AND instructor_id IS NOT NULL");
        $stmtInst->execute([$cursoId]);
        $primary = $stmtInst->fetchColumn();
        if ($primary) $instructorIds[] = (int)$primary;

        try {
            $stmtCI = $db->prepare("SELECT usuario_id FROM curso_instructor WHERE curso_id=?");
            $stmtCI->execute([$cursoId]);
            foreach ($stmtCI->fetchAll(PDO::FETCH_COLUMN) as $id) {
                $instructorIds[] = (int)$id;
            }
        } catch (Exception $e) {}

        $instructorIds = array_unique($instructorIds);
        $esCompleto    = $nuevasEntregadas >= $totalTareas;
        $tituloCursoN  = $curso['titulo'] ?? 'el curso';
        $crmUrl        = BASE_URL . '/index.php?url=crm&seccion=comunicacion&curso_id=' . $cursoId;

        foreach ($instructorIds as $instId) {
            if ($esCompleto) {
                $titulo = $nombreAlumno . ' ha completado el examen práctico';
                $cuerpo = $nombreAlumno . ' ha entregado todas las tareas del examen práctico de «' . $tituloCursoN . '». Ya puedes revisarlas.';
            } else {
                $titulo = $nombreAlumno . ' ha entregado una tarea práctica';
                $cuerpo = $nombreAlumno . ' ha entregado «' . $titTarea . '» en «' . $tituloCursoN . '».';
            }
            $db->prepare("INSERT INTO notificacion (usuario_id, tipo, titulo, cuerpo, url_accion, ref_id) VALUES (?,?,?,?,?,?)")
               ->execute([$instId, 'entrega_practica', $titulo, $cuerpo, $crmUrl, $tareaId]);
        }
    } catch (Exception $e) {}

    echo json_encode([
        'ok'          => true,
        'mensaje'     => 'Entrega guardada correctamente',
        'entregadas'  => $nuevasEntregadas,
        'total'       => $totalTareas,
        'completado'  => $nuevasEntregadas >= $totalTareas,
    ]);
    exit;
}

require __DIR__ . '/../views/examen/practico.php';
