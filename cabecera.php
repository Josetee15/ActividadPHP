<?php
$userBD = $calendarDataAccess->getUserById($userID);
$nombre = $userBD->getFirstName();
$apellidos = $userBD->getLastName();
?>

<nav class="py-2 bg-body-tertiary border-bottom">
    <div class="container d-flex justify-content-center flex-wrap">
        <ul class="nav">
            <li class="navbar-text text-success"><?= $nombre ?> <?= $apellidos ?></li>
            <li class="nav-item"><a href="logout.php" class="nav-link px-2 link-danger link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">Desconectar</a></li>
            <li class="nav-item"><a href="#!" class="nav-link  px-2 link-secondary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">Cambiar contraseña</a></li>
            <li class="nav-item"><a href="#!" class="nav-link  px-2 link-secondary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">Mi Perfil</a></li>
        </ul>
    </div>
</nav>