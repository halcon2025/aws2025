<?php
// Parámetros de conexión
$host = "lab-db.cbd8cduyt8bf.us-east-1.rds.amazonaws.com";
$user = "main";
$pass = "lab-password";
$dbname = "lab";

// Conexión global
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("❌ Conexión fallida: " . $conn->connect_error);
}

// Procesar creación de tabla
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["crear_tabla"])) {
    $tabla = $_POST["tabla"];
    $campos = [];

    for ($i = 1; $i <= 4; $i++) {
        $campo = trim($_POST["campo$i"]);
        if ($campo) {
            $campos[] = "`$campo` VARCHAR(100)";
        }
    }

    if (count($campos) > 0) {
        $sql = "CREATE TABLE `$tabla` (id INT AUTO_INCREMENT PRIMARY KEY, " . implode(", ", $campos) . ")";
        $mensaje = $conn->query($sql) === TRUE ? "✅ Tabla '$tabla' creada correctamente." : "❌ Error: " . $conn->error;
    } else {
        $mensaje = "⚠️ Debes ingresar al menos un campo.";
    }
}

// Procesar inserción
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["insertar"])) {
    $tabla = $_POST["tabla_insert"];
    $valores = array_map('trim', explode(",", $_POST["valores"]));
    $placeholders = implode(",", array_fill(0, count($valores), "?"));
    $sql = "INSERT INTO `$tabla` VALUES (NULL, $placeholders)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $tipos = str_repeat("s", count($valores));
        $stmt->bind_param($tipos, ...$valores);
        $mensaje = $stmt->execute() ? "✅ Registro insertado correctamente." : "❌ Error: " . $stmt->error;
    } else {
        $mensaje = "❌ Error en preparación: " . $conn->error;
    }
}

// Procesar consulta
$tabla_consultada = "";
$tabla_resultado = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["consultar"])) {
    $tabla = $_POST["tabla_consulta"];
    $resultado = $conn->query("SELECT * FROM `$tabla`");
    $tabla_consultada = $tabla;

    if ($resultado && $resultado->num_rows > 0) {
        $tabla_resultado .= "<table border='1' cellpadding='5'><tr>";
        while ($field = $resultado->fetch_field()) {
            $tabla_resultado .= "<th>{$field->name}</th>";
        }
        $tabla_resultado .= "</tr>";
        while ($row = $resultado->fetch_assoc()) {
            $tabla_resultado .= "<tr>";
            foreach ($row as $dato) {
                $tabla_resultado .= "<td>$dato</td>";
            }
            $tabla_resultado .= "</tr>";
        }
        $tabla_resultado .= "</table>";
    } else {
        $tabla_resultado = "⚠️ No hay registros o la tabla no existe.";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Gestor de Tabla - Base de Datos lab</title>
  <meta charset="UTF-8">
  <style>
    body { font-family: Arial; margin: 40px; background-color: #f9f9f9; }
    h2 { color: #004080; }
    input, button { margin: 5px; padding: 5px; }
    .seccion { margin-bottom: 30px; padding: 15px; border: 1px solid #ccc; background: #fff; border-radius: 5px; }
    table { background: white; margin-top: 10px; border-collapse: collapse; }
    th, td { padding: 8px; border: 1px solid #ccc; }
  </style>
</head>
<body>

  <h1>🛠️ Gestor de Tablas en MySQL - lab</h1>

  <?php if (!empty($mensaje)) echo "<p><strong>$mensaje</strong></p>"; ?>

  <form method="POST" class="seccion">
    <h2>Crear Tabla</h2>
    Nombre de la tabla: <input type="text" name="tabla" required><br>
    Campo 1: <input type="text" name="campo1" required><br>
    Campo 2: <input type="text" name="campo2"><br>
    Campo 3: <input type="text" name="campo3"><br>
    Campo 4: <input type="text" name="campo4"><br>
    <button type="submit" name="crear_tabla">Crear Tabla</button>
  </form>

  <form method="POST" class="seccion">
    <h2>Insertar Datos</h2>
    Nombre de la tabla: <input type="text" name="tabla_insert" required><br>
    Valores separados por coma: <input type="text" name="valores" placeholder="Ej: Juan, 25, Bogotá"><br>
    <button type="submit" name="insertar">Insertar</button>
  </form>

  <form method="POST" class="seccion">
    <h2>Consultar Tabla</h2>
    Nombre de la tabla: <input type="text" name="tabla_consulta" required><br>
    <button type="submit" name="consultar">Consultar</button>
  </form>

  <?php if ($tabla_resultado) echo "<h2>Resultados de '$tabla_consultada'</h2>" . $tabla_resultado; ?>

</body>
</html>
