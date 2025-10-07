<?php
// 1. Variables básicas
$edad = 25; // entero
$nombre = "Jose"; // cadena
$activo = true; // booleano

// 2. Producto y mensaje
$producto = "Laptop";
$precio = 3500000;
$cantidad = 12;
echo "El producto $producto cuesta $precio y hay $cantidad unidades disponibles.<br>";

// 3. Intercambio sin variable auxiliar
$a = 5;
$b = 10;
$a = $a + $b;
$b = $a - $b;
$a = $a - $b;
echo "Después del intercambio: a = $a, b = $b<br>";

// 4. Saludo personalizado
$usuario = "Carlos";
$edadUsuario = 30;
echo "Hola " . $usuario . ", tienes " . $edadUsuario . " años.<br>";

// 5. Operaciones matemáticas
$num1 = 20;
$num2 = 6;
echo "Suma: " . ($num1 + $num2) . "<br>";
echo "Resta: " . ($num1 - $num2) . "<br>";
echo "Multiplicación: " . ($num1 * $num2) . "<br>";
echo "División: " . ($num1 / $num2) . "<br>";
echo "Módulo: " . ($num1 % $num2) . "<br>";

// 6. Comparaciones
if ($num1 > $num2) {
    echo "$num1 es mayor que $num2<br>";
} elseif ($num1 == $num2) {
    echo "$num1 es igual a $num2<br>";
} else {
    echo "$num1 es menor que $num2<br>";
}

// 7. Acceso a plataforma
$activo = true;
$edad = 19;
if ($activo && $edad > 18) {
    echo "Acceso permitido<br>";
} else {
    echo "Acceso denegado<br>";
}

// 8. Área de un círculo
define("PI", 3.1416);
$radio = 5;
$area = PI * $radio * $radio;
echo "El área del círculo es: $area<br>";

// 9. Precio final con IVA y descuento
define("IVA", 0.19);
define("DESCUENTO", 0.10);
$precioBase = 100000;
$precioConIVA = $precioBase + ($precioBase * IVA);
$precioFinal = $precioConIVA - ($precioConIVA * DESCUENTO);
echo "Precio final con IVA y descuento: $precioFinal<br>";

// 10. Branding del sistema
define("APP_NOMBRE", "MiSistemaWeb");
echo "Bienvenido a " . APP_NOMBRE . "<br>";
echo "Gracias por usar " . APP_NOMBRE . "<br>";
echo APP_NOMBRE . " está diseñado para ti.<br>";
?>
