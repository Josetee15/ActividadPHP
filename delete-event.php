<?php
session_start();
require_once __DIR__ . '/data-access/CalendarDataAccess.php';
require_once __DIR__ . '/entities/User.php';

// Ruta del fichero de la BD
$dbFile = __DIR__ . '/calendar.db';

// Crear un objeto para acceso a la BD
$calendarDataAccess = new CalendarDataAccess($dbFile);

//VARIABLES NECESARIAS
$userID = ''; //para recoger el id del usuario conectado en $_SESSION

//Variable para guardar los errores generados
$errors = [];

//Evento a eliminar
$eventoBD;

//Errores de seguridad
$errorSeguridad = false;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar evento</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
</head>


<?php if (empty($_SESSION['user'])) {
    //Si no hay una sesión iniciada, se redirige a la página de login -> index.php
    header("Location: index.php");
    exit;
} else {
    //recogemos el usuario conectado en la sesión
    $userID = $_SESSION['user'];
} ?>

<?php if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    //Recogemos el id del evento recibido por la URL
    $eventoID = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($eventoID === null || $eventoID === false) {
        $errorSeguridad = true;
    } else {
        //Buscamos a que idUsuario de la BD corresponde este evento
        $eventoBD = $calendarDataAccess->getEventById($eventoID);
        //Commprobamos que el evento recibido existe
        if ($eventoBD === null) {
            $errorSeguridad = true;
        } else {
            $usuarioEvento = $eventoBD->getUserId();
            //Comprobamos que dicho evento corresponde al usuario conectado en la SESIÓN
            if ($usuarioEvento !== $userID) {
                $errorSeguridad = true;
            }
        }
    }
} ?>

<?php if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $botonSeleccionado = $_POST['boton'] ?? '';
    if ($botonSeleccionado == 'eventos') {
        header("Location: events.php");
        exit;
    } elseif ($botonSeleccionado == 'eliminar') {
        //Recogemos el id del evento recibido por la URL aunque recibamos el POST del formulario
        $eventoID = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        //Eliminamos el evento en la BD
        $eliminarEvento = $calendarDataAccess->deleteEvent($eventoID);
        //Comprobamos la salida del statement para ver si se ha eliminado o no
        if (!$eliminarEvento) {
            $errors[] = "Error al eliminar el evento";
        } else {
            header("Location: events.php");
            exit;
        }
    }
} ?>



<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="border rounded bg-light p-5">
            <header>
                <h1 class="m-1 h3 mb-3 fw-normal">Eliminar evento</h1>
                <?php require_once 'cabecera.php' ?>
            </header>
            <main class="form-signin w-100 m-auto">
                <?php if ($errorSeguridad): ?>
                    <h3>No se puede acceder al evento porque no existe o porque no tiene permisos para verlo</h3>
                    <a href="events.php">Volver al listado de eventos</a>

                <?php else: ?>
                    <form method="post">
                        <div class="mb-3">
                            <h5>¿Seguro que desea eliminar el evento?</h5>
                        </div>
                        <button type="submit" class="btn btn-secondary" name="boton" value="eventos">No, volver al listado de eventos</button>
                        <button type="submit" class="btn btn-primary" name="boton" value="eliminar">Sí, eliminar el evento</button>
                    </form>
                <?php endif ?>
            </main>
        </div>
    </div>

</body>

</html>