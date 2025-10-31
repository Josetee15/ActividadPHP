<?php
session_start();
require_once __DIR__ . '/data-access/CalendarDataAccess.php';
require_once __DIR__ . '/entities/User.php';

// Ruta del fichero de la BD
$dbFile = __DIR__ . '/calendar.db';

// Crear un objeto para acceso a la BD
$calendarDataAccess = new CalendarDataAccess($dbFile);

//VARIABLES NECESARIAS
//Variable para recoger los errores del usuario
$errors = [];
//Variable para recoger el id del usuario conectado en $_SESSION
$userID = '';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo evento</title>
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

<?php if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //VALIDACION DE CAMPOS DE FORMULARIO
    $titulo = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $descripcion = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $fechaInicio = filter_input(INPUT_POST, 'startDate', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $fechaFin = filter_input(INPUT_POST, 'endDate', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    //Validacion título del evento
    if ($titulo === null || $titulo === '') {
        $errors[] = "Debe introducir un título válido";
    }
    //hay que validar null?? en la descripción si es opcional
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
            //Validación que la fecha de inicio sea más antigua que la fecha de fin 
        } elseif ($fechaFinDate < $fechaInicioDate) {
            $errors[] = "La fecha y hora de finalización del evento debe ser posterior a la de inicio";
        }
    }


    //Si todos los campos son correctos entonces $errors estará vacio
    if (empty($errors)) {
        //CREAMOS NUEVO EVENTO EN LA BD
        $nuevoEvento = new Event($userID, $titulo, $descripcion, $fechaInicio, $fechaFin);
        $createEventBD = $calendarDataAccess->createEvent($nuevoEvento);
        if (!$createEventBD) {
            $errors[] = "Error al crear el evento";
        } else {
            //Se ha creado el evento correctamente, redirigimos a events.php
            header("Location: events.php");
            exit;
        }
    }
} ?>



<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="border rounded bg-light p-5">
            <header>
                <h1 class="m-1 h3 mb-3 fw-normal">Crear nuevo evento</h1>
                <?php require_once 'cabecera.php' ?>
            </header>
            <main class="form-signin w-100 m-auto">
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
                        <input class="form-control" type="text" name="title" id="title" value="<?= $titulo ?? '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="description">Descripción</label>
                        <textarea class="form-control" name="description" id="description"><?= $descripcion ?? '' ?></textarea>
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

            </main>
        </div>

    </div>


</body>

</html>