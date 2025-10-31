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

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>

<?php if (empty($_SESSION['user'])) {
    //Si no hay una sesión iniciada, se redirige a la página de login -> index.php
    header("Location: index.php");
    exit;
} ?>

<!-- Debemos de ver que usuario está conectado -->
<?php
$userID = $_SESSION['user']; //recogemos el ID de usuario
//Buscamos los eventos de dicho usuario en la BD
$events = $calendarDataAccess->getEventsByUserId($userID);

?>

<body class="m-auto">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="border rounded bg-light p-5">
            <header>
                <h1 class="m-1 h3 mb-3 fw-normal">Eventos</h1>
                <?php require_once 'cabecera.php' ?>
            </header>
            <main class="form-signin w-100 m-auto">


                <a href="new-event.php">Nuevo evento</a>
                <!-- Mostraremos un mensaje si el usuario no tiene eventos en la BD -->
                <?php if (empty($events)): ?>
                    <h3>El usuario no tiene eventos registrados</h3>
                <?php else: ?>
                    <!-- Mostramos la tabla de eventos del usuario -->
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Descripción</th>
                                <th scope="col">Fecha inicio</th>
                                <th scope="col">Fecha fin</th>
                                <th scope="col">Operaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $event): ?>
                                <tr>
                                    <td><?= $event->getId() ?></td>
                                    <td><?= $event->getTitle() ?></td>
                                    <td><?= $event->getDescription() ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($event->getStartDate())) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($event->getEndDate())) ?></td>
                                    <td class="text-center"><a href="edit-event.php?id=<?= $event->getId() ?>" class="me-2"><i class="fa-solid fa-pen-to-square" style="color: #319ef2;" title="Editar"></i><span class="visually-hidden">Editar evento</span></a><a href="delete-event.php?id=<?= $event->getId() ?>"><i class="fa-solid fa-trash" style="color: #ee2f2f;" title="Eliminar"></i><span class="visually-hidden">Eliminar evento</span></a></td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                <?php endif ?>
                <a href="new-event.php">Nuevo evento</a>


            </main>
        </div>
    </div>

</body>

</html>