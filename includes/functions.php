<?php
require_once 'db.php';

function getConfigEmpresa() {
    global $conn;
    // Asegurar que la tabla existe
    $conn->query("CREATE TABLE IF NOT EXISTS config_empresa (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nombre VARCHAR(100) NOT NULL,
        telefono VARCHAR(20) DEFAULT '',
        direccion TEXT NOT NULL
    )");
    
    // Verificar si la columna ruc existe (para retrocompatibilidad si la tabla se creó antes)
    $res_col = $conn->query("SHOW COLUMNS FROM config_empresa LIKE 'ruc'");
    if ($res_col && $res_col->num_rows == 0) {
        $conn->query("ALTER TABLE config_empresa ADD COLUMN ruc VARCHAR(20) DEFAULT '' AFTER nombre");
    }
    
    $res = $conn->query("SELECT * FROM config_empresa LIMIT 1");
    if ($res && $res->num_rows > 0) {
        return $res->fetch_assoc();
    } else {
        // Insertar por defecto
        $conn->query("INSERT INTO config_empresa (nombre, telefono, direccion, ruc) VALUES ('El Barón', '0987296574', 'Mercado 4 de mayo', '')");
        return [
            'nombre' => 'El Barón',
            'telefono' => '0987296574',
            'direccion' => 'Mercado 4 de mayo',
            'ruc' => ''
        ];
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    if (!isset($_SESSION['user_id'])) return false;
    
    // Si el rol no está en la sesión, intentamos cargarlo (para sesiones antiguas)
    if (!isset($_SESSION['user_role'])) {
        global $conn;
        $id = $_SESSION['user_id'];
        $res = $conn->query("SELECT rol FROM usuarios WHERE id = $id");
        if ($res && $res->num_rows > 0) {
            $u = $res->fetch_assoc();
            $_SESSION['user_role'] = $u['rol'];
        }
    }
    
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'administrador';
}

function redirect($url) {
    header("Location: " . SITE_URL . $url);
    exit;
}

function generateSticker($data, $tipo = 'reparacion') {
    $fecha = date('d/m/Y H:i');
    $versiculo = VERSICULO_DEFAULT;
    $empresa = getConfigEmpresa();
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Comprobante - El Barón</title>
        <link rel="icon" href="assets/img/logo.png" type="image/png">
        <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: #f0f0f0;
                margin: 0;
                padding: 20px;
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            .sticker {
                width: 3.5in;
                background: white;
                padding: 15px;
                border: 1px solid #ccc;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                margin-bottom: 20px;
                word-wrap: break-word;
                overflow-wrap: break-word;
            }
            .header {
                text-align: center;
                border-bottom: 2px solid ' . COLOR_SECONDARY . ';
                padding-bottom: 10px;
                margin-bottom: 10px;
            }
            .header h2 {
                color: ' . COLOR_PRIMARY . ';
                margin: 0;
                font-size: 24px;
                text-transform: uppercase;
            }
            .content p {
                margin: 5px 0;
                font-size: 13px;
                line-height: 1.3;
            }
            .total-box {
                background: #f9f9f9;
                border: 1px solid #ddd;
                padding: 10px 15px;
                margin-top: 10px;
                text-align: right;
            }
            .total-box p {
                margin: 5px 0;
            }
            .footer {
                text-align: center;
                margin-top: 15px;
                font-size: 11px;
                border-top: 1px dashed #ccc;
                padding-top: 10px;
            }
            .gracias { font-weight: bold; font-size: 14px; margin-bottom: 5px; }
            .versiculo { font-style: italic; color: #555; margin-bottom: 5px; }
            
            .btn-group {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
                justify-content: center;
            }
            .btn {
                padding: 10px 15px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-weight: bold;
                color: white;
                font-size: 13px;
                text-decoration: none;
                display: flex;
                align-items: center;
                gap: 5px;
            }
            .btn-print { background: ' . COLOR_PRIMARY . '; }
            .btn-pdf { background: #e74c3c; }
            .btn-wa { background: #25d366; }
            .btn-close { background: #7f8c8d; }
            
            @media print { .btn-group { display: none; } }
        </style>
    </head>
    <body';
    
    // Si se pasa auto_print=1, imprimir al cargar
    if (isset($_GET['autoprint'])) $html .= ' onload="window.print()"';
    $html .= '>';
    
    $sticker_id = 'sticker-' . $tipo . '-' . $data['id'];
    
    // Contenido del sticker
    $html .= '<div class="sticker" id="sticker-content">
                <div class="header">
                    <h2>' . htmlspecialchars($empresa['nombre']) . '</h2>
                    ' . (!empty($empresa['ruc']) ? '<p style="font-size: 11px; margin: 2px 0;">RUC/CI: ' . htmlspecialchars($empresa['ruc']) . '</p>' : '') . '
                    <p style="font-size: 11px; margin: 2px 0;">' . htmlspecialchars($empresa['direccion']) . (!empty($empresa['telefono']) ? ' - Tel: ' . htmlspecialchars($empresa['telefono']) : '') . '</p>
                </div>
                <div class="content">
                    <p><strong>Fecha:</strong> ' . $fecha . '</p>
                    <p><strong>Comprobante:</strong> #' . $data['id'] . '</p>
                    <p><strong>Cliente:</strong> ' . $data['nombres'] . ' ' . $data['apellidos'] . '</p>
                    <p><strong>Tel/Dir:</strong> ' . $data['telefono'] . ' / ' . $data['direccion'] . '</p>
                    <hr style="border: 0; border-top: 1px solid #eee;">
                    <p><strong>Tipo de electrodoméstico:</strong> ' . $data['producto'] . '</p>';
    
    if ($tipo == 'reparacion') {
        $html .= '<p><strong>Problema:</strong> ' . $data['descripcion'] . '</p>
                  <div class="total-box">
                    <p>Costo total: $' . number_format($data['costo_total'], 2) . '</p>
                    <p>Abono: $' . number_format($data['abono'], 2) . '</p>
                    <p style="font-size: 15px; font-weight: bold; color: ' . COLOR_SECONDARY . ';">Saldo pendiente: $' . number_format($data['saldo'], 2) . '</p>
                  </div>';
    } else {
        $html .= '<div class="total-box">
                    <p style="font-size: 16px; font-weight: bold;">COSTO TOTAL: $' . number_format($data['total'], 2) . '</p>
                  </div>';
    }
    
    $html .= '  </div>
                <div class="footer">
                    <p class="gracias">¡Gracias por preferirnos!</p>
                    <p class="versiculo">' . $versiculo . '</p>
                    <p style="font-size: 9px; color: #999;">Generado por: ' . (isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Sistema El Barón') . '</p>
                </div>
            </div>';
    
    $html .= '
        <div class="btn-group">
            <button class="btn btn-print" onclick="window.print()">🖨️ Imprimir</button>
            <button class="btn btn-pdf" onclick="descargarPDF()">📄 Descargar PDF</button>
            <button class="btn btn-wa" onclick="compartirWhatsApp()">📱 WhatsApp</button>
            <button class="btn btn-close" onclick="cerrarVentana()">✖️ Cerrar/Volver</button>
        </div>
        
        <script>
        function descargarPDF() {
            const element = document.getElementById("sticker-content");
            const opt = {
                margin: 0.1,
                filename: "Comprobante_' . $data['id'] . '.pdf",
                image: { type: "jpeg", quality: 0.98 },
                html2canvas: { scale: 3 },
                jsPDF: { unit: "in", format: [4, 6], orientation: "portrait" }
            };
            html2pdf().set(opt).from(element).save();
        }
        
        async function compartirWhatsApp() {
            const element = document.getElementById("sticker-content");
            const opt = {
                margin: 0.1,
                filename: "Comprobante_' . $data['id'] . '.pdf",
                image: { type: "jpeg", quality: 0.98 },
                html2canvas: { scale: 3 },
                jsPDF: { unit: "in", format: [4, 6], orientation: "portrait" }
            };
            
            // Generar el PDF como blob
            const pdfBlob = await html2pdf().set(opt).from(element).outputPdf("blob");
            
            // Si el navegador soporta compartir archivos (Web Share API Nivel 2)
            if (navigator.canShare && navigator.canShare({ files: [new File([pdfBlob], "Comprobante.pdf", { type: "application/pdf" })] })) {
                try {
                    const file = new File([pdfBlob], "Comprobante_' . $data['id'] . '.pdf", { type: "application/pdf" });
                    await navigator.share({
                        files: [file],
                        title: "Comprobante El Barón",
                        text: "Gracias por preferirnos. Adjunto su comprobante."
                    });
                } catch (err) {
                    console.error("Error al compartir:", err);
                    window.open("https://wa.me/' . ($data['telefono'] ?? '') . '?text=" + encodeURIComponent("Hola, le envío su comprobante: ' . SITE_URL . '/generar_sticker.php?id=' . $data['id'] . '&tipo=' . $tipo . '"), "_blank");
                }
            } else {
                // Fallback: descargar y abrir WhatsApp
                descargarPDF();
                alert("El PDF se ha descargado. Ahora puede adjuntarlo en WhatsApp.");
                window.open("https://wa.me/' . ($data['telefono'] ?? '') . '?text=" + encodeURIComponent("Hola, le envío su comprobante de ' . $tipo . '"), "_blank");
            }
        }
        
        function cerrarVentana() {
            if (window.opener || window.history.length === 1) {
                window.close();
            }
            // Fallback si no se puede cerrar (porque no fue abierta por script)
            window.location.href = "dashboard.php";
        }
        </script>
    </body>
    </html>';
    
    return $html;
}

function actualizarStock($producto_id, $cantidad, $operacion = 'restar') {
    global $conn;
    
    if ($operacion == 'restar') {
        $sql = "UPDATE productos SET stock = stock - ? WHERE id = ?";
    } else {
        $sql = "UPDATE productos SET stock = stock + ? WHERE id = ?";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $cantidad, $producto_id);
    return $stmt->execute();
}

function buscarProductos($termino) {
    global $conn;
    $termino = "%$termino%";
    $sql = "SELECT * FROM productos WHERE nombre LIKE ? OR codigo LIKE ? LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $termino, $termino);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>