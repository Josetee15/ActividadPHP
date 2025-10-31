<?php
session_start();
require_once __DIR__ . '/data-access/CalendarDataAccess.php';
require_once __DIR__ . '/entities/User.php';

// Ruta del fichero de la BD
$dbFile = __DIR__ . '/calendar.db';

// Crear un objeto para acceso a la BD
$calendarDataAccess = new CalendarDataAccess($dbFile);

//inicializamos el id usuario
$userID = '';

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log out</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
</head>
</head>

<!-- Si no hay sesión iniciada, redirigimos a LOGIN -->
<?php if (empty($_SESSION['user'])) {
    header("Location: index.php");
    exit;
} else {
    $userID = $_SESSION['user'];
} ?>

<!-- Si recibo el formulario por POST debo ver que botón se ha pulsado -->
<?php if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $botonSeleccionado = $_POST['boton'] ?? '';
    if ($botonSeleccionado == 'eventos') {
        header("Location: events.php");
        exit;
    } elseif ($botonSeleccionado == 'logout') {
        session_regenerate_id();
        session_destroy();
        header("Location: index.php");
        exit;
    }
}
?>

<body>

    <body class="m-auto">
        <div class="container d-flex justify-content-center align-items-center vh-100">
            <div class="border rounded bg-light p-5">
                <header>
                    <h1 class="m-1 h3 mb-3 fw-normal">Cerrar sesión</h1>
                    <?php require_once 'cabecera.php' ?>
                </header>
                <!--formulario de cierre de sesión-->
                <main class="form-signin w-100 m-auto">
                    <form method="post">
                        <div class="mb-3 mt-2">
                            <h5>¿Seguro que desea desconectar?</h5>
                        </div>
                        <button type="submit" class="btn btn-secondary" name="boton" value="eventos">No, volver al listado de eventos</button>
                        <button type="submit" class="btn btn-primary" name="boton" value="logout">Sí, desconectar</button>
                    </form>

                </main>
            </div>
        </div>

    </body>

</html>