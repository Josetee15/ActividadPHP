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

//Evento a modificar
$eventoBD;

//Errores de seguridad
$errorSeguridad = false;

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar evento</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
</head>

<?php if (empty($_SESSION['user'])) {
    //Si no hay una sesión iniciada, se redirige a la página de login -> index.php
    header("Location: index.php");
    exit;
} else {
    //Recogemos el usuario conectado en la sesion
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
        //Recogo los datos del evento en las variables
        $titulo = $eventoBD->getTitle();
        $descripcion = $eventoBD->getDescription();
        $fechaInicio = $eventoBD->getStartDate();
        $fechaFin = $eventoBD->getEndDate();
    }
} ?>

<?php if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //Recogemos el id del evento recibido por la URL aunque recibamos el POST del formulario
    $eventoID = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    //Buscamos a que idUsuario de la BD corresponde este evento
    $eventoBD = $calendarDataAccess->getEventById($eventoID);


    //VALIDACION DE CAMPOS DE FORMULARIO
    $titulo = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $descripcion = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $fechaInicio = filter_input(INPUT_POST, 'startDate', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $fechaFin = filter_input(INPUT_POST, 'endDate', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    //Validacion nombre evento
    if ($titulo === null || $titulo === '') {
        $errors[] = "Debe introducir un nombre válido";
    }
    //Validación fecha de inicio
    if ($fechaInicio === null || $fechaInicio === '') {
        $errors[] = "Debe introducir una fecha de inicio válida";
    } else {
        $fechaInicioDate = DateTime::createFromFormat('Y-m-d\TH:i', $fechaInicio);
        //PROBAR si es necesaria la segunda parte
        if (!$fechaInicioDate || $fechaInicioDate->format('Y-m-d\TH:i') !== $fechaInicio) {
            $errors[] = "Debe introducir una fecha de inicio válida";
        }
    }
    //Validación fecha de fin
    if ($fechaFin === null || $fechaFin === '') {
        $errors[] = "Debe introducir una fecha de fin válida";
    } else {
        $fechaFinDate = DateTime::createFromFormat('Y-m-d\TH:i', $fechaFin);
        //PROBAR si es necesaria la segunda parte
        if (!$fechaFinDate || $fechaFinDate->format('Y-m-d\TH:i') !== $fechaFin) {
            $errors[] = "Debe introducir una fecha de fin válida";
        }
    }
    //Validación que la fecha de inicio sea más antigua que la fecha de fin
    if ($fechaFinDate < $fechaInicioDate) {
        $errors[] = "La fecha y hora de finalización del evento debe ser posterior a la de inicio";
    }

    //Si ha pasado la validación entonces editamos el evento en la BD
    if (empty($errors)) {
        //Modificamos los datos con los introducidos por el usuario
        $eventoBD->setTitle($titulo);
        $eventoBD->setDescription($descripcion);
        $eventoBD->setStartDate($fechaInicio);
        $eventoBD->setEndDate($fechaFin);
        //Actualizamos el evento en la BD
        $editarEvento = $calendarDataAccess->updateEvent($eventoBD);
        if (!$editarEvento) {
            $errors[] = "Error al editar el evento";
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
                <h1 class="m-1 h3 mb-3 fw-normal">Editar evento</h1>
                <?php require_once 'cabecera.php' ?>
            </header>
            <main class="form-signin w-100 m-auto">
                <?php if ($errorSeguridad): ?>
                    <h3>No se puede acceder al evento porque no existe o porque no tiene permisos para verlo</h3>
                    <a href="events.php">Volver al listado de eventos</a>

                <?php else: ?>
                    <?php if (!empty($errors)): ?>
                        <div class="m-2 p-3 text-danger-emphasis bg-danger-subtle border border-danger-subtle rounded-3">
                            <h3>Errores:</h3>
                            <ul>
                                <?php foreach ($errors as $error) : ?>
                                    <li><?= $error ?></li>
                                <?php endforeach ?>
                            </ul>
                        </div>
                    <?php endif ?>
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label" for="title">Título</label>
                            <input class="form-control" type="text" name="title" id="title" value="<?= $titulo ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="description">Descripción</label>
                            <textarea class="form-control" name="description" id="description"><?= $descripcion ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="startDate">Fecha y hora de inicio</label>
                            <input class="form-control" type="datetime-local" name="startDate" id="startDate" value="<?= $fechaInicio ?>" required>

                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="endDate">Fecha y hora de fin</label>
                            <input class="form-control" type="datetime-local" name="endDate" id="endDate" value="<?= $fechaFin ?>" required>
                        </div>
                        <div class="text-end">
                            <button class="btn btn-primary" type="submit">Aceptar</button>
                        </div>
                    </form>
                <?php endif ?>

            </main>
        </div>
    </div>
</body>

</html>