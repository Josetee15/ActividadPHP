<?php
session_start();
$errors = [];
$registroExitoso = false; //bandera para no mostrar el form en dicho caso, solo el mensaje de Usuario Creado
require_once __DIR__ . '/data-access/CalendarDataAccess.php';
require_once __DIR__ . '/entities/User.php';

// Ruta del fichero de la BD
$dbFile = __DIR__ . '/calendar.db';

// Crear un objeto para acceso a la BD
$calendarDataAccess = new CalendarDataAccess($dbFile);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
</head>


<?php if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //Validación campos formulario
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $nombre = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $apellidos = filter_input(INPUT_POST, 'lastName', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $fechaNacimiento = filter_input(INPUT_POST, 'birthDate', FILTER_SANITIZE_SPECIAL_CHARS);
    $contrasenia = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $repetirContrasenia = filter_input(INPUT_POST, 'passwordConfirm', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    //Validacion email
    if ($email === null || $email === '') {
        $errors[] = "Debe introducir un email válido";
    }
    //Validamos que el email introducido no exista en la BD
    $userDB = $calendarDataAccess->getUserByEmail($email);
    if ($userDB !== null) {
        $errors[] = "Ya existe un usuario registrado con ese email";
        //Si el usuario ya está registrado no seguimos comprobando el resto de errores
    } else {


        //Validación nombre
        if ($nombre === null || $nombre === '') {
            $errors[] = "Debe introducir un nombre válido";
        }

        //Validacion apellidos
        if ($apellidos === null || $apellidos === '') {
            $errors[] = "Debe introducir un apellido válido";
        }

        //Validacion fecha nacimiento
        if ($fechaNacimiento === null || $fechaNacimiento === '') {
            $errors[] = "Debe introducir una fecha de nacimiento válida";
        } else {
            $fechaNacimientoDate = DateTime::createFromFormat('Y-m-d', $fechaNacimiento);
            //PROBAR si es necesaria la segunda parte
            if (!$fechaNacimientoDate || $fechaNacimientoDate->format('Y-m-d') !== $fechaNacimiento) {
                $errors[] = "Debe introducir una fecha de nacimiento válida";
            }
        }

        //Validación contrasenia
        $pattern = '/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/';
        if (($contrasenia === null || $contrasenia === '') ||  ($repetirContrasenia === null ||  $repetirContrasenia === '')) {
            $errors[] = "Debe introducir una contraseña válida";
        } elseif (!preg_match($pattern, $contrasenia)) {
            $errors[] = "Debe introducir una contraseña válida";
        } elseif ($contrasenia !== $repetirContrasenia) {
            $errors[] = "Las contraseñas introducidas no coinciden";
        }
    }

    //Si todos los datos introducidos son CORRECTOS entonces $erros debe estar vacio
    if (empty($errors)) {

        //CREACION DE NUEVO USUARIO

        //Codificación de contraseña con hash
        $hashedPassword = password_hash($contrasenia, PASSWORD_DEFAULT);
        $newUser = new User($email, $hashedPassword, $nombre, $apellidos, $fechaNacimiento, null);
        $createUserBD = $calendarDataAccess->createUser($newUser);
        if (!$createUserBD) {
            $errors[] = "Error al crear el usuario";
        } else {
            //Usamos un marcador para mostrar el mensaje de exito o el formulario según lo que haya sucedido en el resgistro
            $registroExitoso = true;
        }
    } ?>

<?php } ?>



<?php if (!empty($_SESSION['user'])) {
    //si tiene sesión iniciada redireccion a events.php
    header("Location: events.php");
    exit;
} ?>
<!-- Solo mostramos el formulario en caso de que el registro no haya sido exitoso, es decir,
 al inicio cuando no se ha registrado aún, o cuando no se ha registrado por errores en el form-->

<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="border rounded bg-light p-5">
            <header>
                <h1 class="m-1 h3 mb-3 fw-normal">Registro usuario</h1>
            </header>
            <main class="form-signin w-100 m-auto">
                <?php if ($registroExitoso): ?>
                    <h3 class="text-success">Usuario creado</h3>
                    <a class="link-secondary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover small" href="login.php">Iniciar sesión</a>
                <?php else : ?>
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
                            <label class="form-label" for="email">Email</label>
                            <input class="form-control" type="email" name="email" id="email" value="<?= $email ?? '' ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="name">Nombre</label>
                            <input class="form-control" type="text" name="name" id="name" pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ ]+$" value="<?= $nombre ?? '' ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="lastName">Apellidos</label>
                            <input class="form-control" type="text" name="lastName" id="lastName" pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ ]+$" value="<?= $apellidos ?? '' ?>" required>

                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="birthDate">Fecha de nacimiento</label>
                            <input class="form-control" type="date" name="birthDate" id="birthDate" value="<?= $fechaNacimiento ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="password">Contreseña</label>
                            <input class="form-control" type="password" name="password" id="password" minlength="8" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="passwordConfirm">Repetir Contreseña</label>
                            <input class="form-control" type="password" name="passwordConfirm" id="passwordConfirm" minlength="8" required>
                        </div>
                        <button class="btn btn-primary" type="submit">Aceptar</button>

                    </form>

            </main>
        </div>

    </div>


</body>

<?php endif ?>

</html>