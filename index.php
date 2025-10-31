<?php
session_start();
$errors = [];
$emailUser = '';
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
    <title>Log in</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
</head>

<?php
/**Si el formulario se ha enviado, es decir tenemos una petición
 * POST 
 * Validamos los datos introducidos por el usuario
 */
//tenemos que ver si hay una cookie fijada y por tanto el email debe estar relleno -->
if (isset($_COOKIE['userCookie'])) {
    $userDB = $calendarDataAccess->getUserById($_COOKIE['userCookie']);
    $emailUser = $userDB->getEmail();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //Validación campos
    $emailUser = filter_input(INPUT_POST, 'user', FILTER_VALIDATE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $userDataBase = $calendarDataAccess->getUserByEmail($emailUser);


    if ($userDataBase === null) {
        $errors[] = "El email introducido no corresponde a ningún usuario";
    } else {

        if ($userDataBase->getEmail() !== $emailUser) {
            $errors[] = "El email introducido no corresponde a ningún usuario";
        }
        //Tenemos que comprobar tb el password para ello debemos codificar la que recibimos del usuario
        if (!password_verify($password, $userDataBase->getPassword())) {
            $errors[] = "La contraseña introducida no es correcta";
        }
    }

    //Si ambos son correctos
    if (empty($errors)) {
        //Se ha marcado recuerdame -> FIJAR COOKIE (guarda el ID Usuario no el email)
        if (isset($_POST['checkRecuerdame'])) {
            setcookie("userCookie", $userDataBase->getId(), time() + 3600, '/');
        }
        //Se fijará el nombre o idUsuario en la sesión
        $_SESSION['user'] = $userDataBase->getId();
        session_regenerate_id();
        //Se redirige a events.php
        header("Location: events.php");
        exit;
    }
}
?>

<?php if (!empty($_SESSION['user'])) : ?>
    <!--redireccion a events.php-->
<?php header("Location: events.php");
    exit;
else: ?>

    <body class="m-auto">
        <div class="container d-flex justify-content-center align-items-center vh-100">
            <div class="border rounded bg-light p-5">
                <header>
                    <h1 class="m-1 h3 mb-3 fw-normal">Inicio de sesión</h1>
                </header>
                <!--Aqui iría el formulario de registro-->
                <main class="form-signin w-100 m-auto">
                    <!-- Si no hay sesión iniciada, mostramos el formulario de LOGIN -->

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger mb-3">
                            <p>Se han producido los siguientes errores:</p>
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach ?>
                            </ul>
                        </div>
                    <?php endif ?>
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label" for="user">Usuario</label>
                            <input class="form-control" type="email" name="user" id="user" value="<?= $emailUser ?>" placeholder="ejemplo@email.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="password">Contraseña</label>
                            <input class="form-control" type="password" name="password" id="password" required>
                        </div>
                        <div class="form-check text-start my-3">
                            <input class="form-check-input" type="checkbox" name="checkRecuerdame" value="remember-me" id="checkRecuerdame">
                            <label class="form-check-label" for="checkRecuerdame">Recuérdame</label>
                        </div>
                        <div class="text-center">
                            <a class="link-secondary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover small" href="register.php">¿No tienes cuenta? Regístrate aquí</a>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary btn-lg mt-4">Aceptar</button>
                        </div>
                    </form>

                </main>
            </div>
        </div>

    </body>

<?php endif ?>



</html>