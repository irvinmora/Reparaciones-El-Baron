<?php
require_once 'config.php';

class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    
    private $conn;
    
    public function __construct() {
        $this->connectDB();
    }
    
    private function connectDB() {
        try {
            // Intentar conectar directamente a la base de datos
            @$this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
            
            // Si hay error de conexión (base de datos no existe)
            if ($this->conn->connect_error) {
                // Conectar sin base de datos para crearla
                $tempConn = new mysqli($this->host, $this->user, $this->pass);
                
                if ($tempConn->connect_error) {
                    throw new Exception("Error de conexión: " . $tempConn->connect_error);
                }
                
                // Crear base de datos si no existe
                $tempConn->query("CREATE DATABASE IF NOT EXISTS " . $this->dbname);
                $tempConn->close();
                
                // Reconectar con la base de datos creada
                $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
                
                if ($this->conn->connect_error) {
                    throw new Exception("Error de conexión: " . $this->conn->connect_error);
                }
                
                // Crear las tablas solo si es la primera vez
                $this->createTables();
            }
            
            $this->conn->set_charset("utf8");
            
        } catch (Exception $e) {
            die("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }
    
    private function createTables() {
        // Verificar si las tablas ya existen
        $result = $this->conn->query("SHOW TABLES LIKE 'usuarios'");
        
        // Solo crear tablas si no existen
        if ($result->num_rows == 0) {
            // SQL para crear tablas
            $sql = "
            CREATE TABLE IF NOT EXISTS usuarios (
                id INT PRIMARY KEY AUTO_INCREMENT,
                nombre VARCHAR(100) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255),
                google_id VARCHAR(255),
                avatar VARCHAR(255),
                rol ENUM('administrador', 'tecnico') DEFAULT 'tecnico',
                fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
            
            CREATE TABLE IF NOT EXISTS productos (
                id INT PRIMARY KEY AUTO_INCREMENT,
                codigo VARCHAR(50) UNIQUE,
                nombre VARCHAR(100) NOT NULL,
                descripcion TEXT,
                precio DECIMAL(10,2) NOT NULL,
                stock INT DEFAULT 0,
                stock_minimo INT DEFAULT 5,
                fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
            
            CREATE TABLE IF NOT EXISTS clientes (
                id INT PRIMARY KEY AUTO_INCREMENT,
                nombres VARCHAR(100) NOT NULL,
                apellidos VARCHAR(100) NOT NULL,
                telefono VARCHAR(20),
                direccion TEXT,
                email VARCHAR(100),
                fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
            
            CREATE TABLE IF NOT EXISTS reparaciones (
                id INT PRIMARY KEY AUTO_INCREMENT,
                cliente_id INT,
                producto VARCHAR(100) NOT NULL,
                descripcion_problema TEXT,
                costo_total DECIMAL(10,2),
                abono_inicial DECIMAL(10,2) DEFAULT 0,
                saldo_pendiente DECIMAL(10,2),
                estado ENUM('pendiente', 'en_proceso', 'completado', 'entregado') DEFAULT 'pendiente',
                fecha_ingreso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                fecha_entrega DATETIME,
                FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL
            );
            
            CREATE TABLE IF NOT EXISTS pagos_reparacion (
                id INT PRIMARY KEY AUTO_INCREMENT,
                reparacion_id INT,
                monto DECIMAL(10,2),
                fecha_pago TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                tipo_pago ENUM('abono', 'pago_completo') DEFAULT 'abono',
                FOREIGN KEY (reparacion_id) REFERENCES reparaciones(id) ON DELETE CASCADE
            );
            
            CREATE TABLE IF NOT EXISTS ventas (
                id INT PRIMARY KEY AUTO_INCREMENT,
                cliente_id INT,
                total DECIMAL(10,2),
                fecha_venta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL
            );
            
            CREATE TABLE IF NOT EXISTS detalle_venta (
                id INT PRIMARY KEY AUTO_INCREMENT,
                venta_id INT,
                producto_id INT,
                cantidad INT,
                precio_unitario DECIMAL(10,2),
                subtotal DECIMAL(10,2),
                FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
                FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
            );";
            
            // Ejecutar cada consulta por separado
            $queries = explode(';', $sql);
            foreach ($queries as $query) {
                $query = trim($query);
                if (!empty($query)) {
                    try {
                        $this->conn->query($query);
                    } catch (Exception $e) {
                        // Ignorar errores de tabla ya existente
                        if (!strpos($e->getMessage(), 'already exists')) {
                            echo "Error: " . $e->getMessage() . "<br>";
                        }
                    }
                }
            }
            
            // Insertar datos de ejemplo
            $this->insertSampleData();
        }
    }
    
    private function insertSampleData() {
        // Insertar productos de ejemplo si no existen
        $check = $this->conn->query("SELECT COUNT(*) as total FROM productos");
        $row = $check->fetch_assoc();
        
        if ($row['total'] == 0) {
            $sample_products = [
                "INSERT INTO productos (codigo, nombre, descripcion, precio, stock) VALUES ('MOT001', 'Motor para licuadora', 'Motor universal 120V', 45.00, 10)",
                "INSERT INTO productos (codigo, nombre, descripcion, precio, stock) VALUES ('RES002', 'Resistencia para microondas', 'Resistencia 800W', 25.00, 15)",
                "INSERT INTO productos (codigo, nombre, descripcion, precio, stock) VALUES ('CAP003', 'Capacitor para ventilador', 'Capacitor 5uF', 8.50, 20)",
                "INSERT INTO productos (codigo, nombre, descripcion, precio, stock) VALUES ('PLA004', 'Plancha para cabello', 'Plancha cerámica', 35.00, 8)"
            ];
            
            foreach ($sample_products as $sql) {
                $this->conn->query($sql);
            }
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function query($sql) {
        try {
            return $this->conn->query($sql);
        } catch (Exception $e) {
            die("Error en consulta: " . $e->getMessage());
        }
    }
    
    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }
    
    public function escapeString($string) {
        return $this->conn->real_escape_string($string);
    }
    
    public function getLastId() {
        return $this->conn->insert_id;
    }
}

$db = new Database();
$conn = $db->getConnection();
?>