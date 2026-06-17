<?php

session_start();

require_once(__DIR__ . '/config/db.php');


if(isset($_SESSION['rol'])){

    switch($_SESSION['rol']){

        case 'ADMIN':
            header("Location: admin/indexadmin.html");
            exit;

        case 'TECNICO':
            header("Location: tecnico/indextec.html");
            exit;

        case 'USUARIO':
            header("Location: cliente/indexcli.html");
            exit;
    }
}

$error = '';



if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $sql = "
        SELECT
            u.id_usuario,
            u.nombre,
            u.apellido,
            u.email,
            u.password_hash,
            u.activo,
            r.nombre AS rol
        FROM usuarios u
        INNER JOIN roles r
            ON u.id_rol = r.id_rol
        WHERE u.email = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);

    if(!$stmt){
        die("Error SQL: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if($resultado->num_rows === 1){

        $usuario = $resultado->fetch_assoc();

        if($usuario['activo'] != 1){

            $error = "Usuario deshabilitado.";

        }elseif(password_verify($password, $usuario['password_hash'])){

            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['apellido'] = $usuario['apellido'];
            $_SESSION['email'] = $usuario['email'];
            $_SESSION['rol'] = $usuario['rol'];

            $update = $conn->prepare("
                UPDATE usuarios
                SET ultimo_acceso = NOW()
                WHERE id_usuario = ?
            ");

            if($update){
                $update->bind_param("i", $usuario['id_usuario']);
                $update->execute();
            }

            switch($usuario['rol']){

                case 'ADMIN':
                    header("Location: admin/indexadmin.html");
                    exit;

                case 'TECNICO':
                    header("Location: tecnico/indextec.html");
                    exit;

                case 'USUARIO':
                    header("Location: cliente/indexcli.html");
                    exit;

                default:
                    $error = "Rol inválido.";
            }

        }else{

            $error = "Contraseña incorrecta.";

        }

    }else{

        $error = "Usuario no encontrado.";

    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>EVA | Iniciar Sesión</title>

</head>
<body>

<div class="container">

    <div class="left">

        <div class="logo">

            <h1>EVA</h1>

            <p>
                Sistema Inteligente de
                Monitoreo de Tanques
            </p>

        </div>

    </div>

    <div class="right">

        <div class="login-box">

            <h2>Bienvenido</h2>

            <p>
                Ingrese sus credenciales
            </p>

            <?php if(!empty($error)){ ?>

                <div class="error">
                    <?php echo $error; ?>
                </div>

            <?php } ?>

            <form method="POST">

                <div class="form-group">

                    <label>Email</label>

                    <input
                        type="email"
                        name="email"
                        required>

                </div>

                <div class="form-group">

                    <label>Contraseña</label>

                    <input
                        type="password"
                        name="password"
                        required>

                </div>

                <button type="submit">
                    Iniciar Sesión
                </button>

            </form>

        </div>

    </div>

</div>

</body>
</html>