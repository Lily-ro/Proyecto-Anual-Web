<?php
session_start();
require_once(__DIR__ . '/config/db.php');

if(isset($_SESSION['rol'])){
    switch($_SESSION['rol']){
        case 'ADMIN':
            header("Location: admin/index.php");
            exit;
        case 'TECNICO':
            header("Location: tecnico/indextec.php");
            exit;
        case 'USUARIO':
            header("Location: cliente/indexcli.php");
            exit;
    }
}

$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.email, u.password_hash, u.activo, r.nombre AS rol FROM usuarios u INNER JOIN roles r ON u.id_rol = r.id_rol WHERE u.email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    if(!$stmt){ die("Error SQL: " . $conn->error); }
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

            $update = $conn->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuario = ?");
            if($update){ $update->bind_param("i", $usuario['id_usuario']); $update->execute(); }

            switch($usuario['rol']){
                case 'ADMIN':
                    header("Location: admin/index.php");
                    exit;
                case 'TECNICO':
                    header("Location: tecnico/indextec.php");
                    exit;
                case 'USUARIO':
                    header("Location: cliente/indexcli.php");
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
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif}

body{background:#0b1120;min-height:100vh;display:flex;padding:30px;gap:30px}

.login-left{flex:1;display:flex;flex-direction:column;justify-content:center;align-items:center}
.login-left svg{width:140px;height:auto}
.login-left .brand{color:white;font-size:72px;letter-spacing:30px;font-weight:300;margin-top:30px}

.login-right{width:460px;background:#111c30;display:flex;justify-content:center;align-items:center;border-radius:20px;padding:50px 40px;margin-right:120px}

.login-box{width:100%;max-width:340px}

.login-header{text-align:center;margin-bottom:35px}
.login-header .logo-row{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:12px}
.login-header .logo-row svg{width:26px;height:auto}
.login-header .logo-row span{color:#3C75C6;font-size:26px;letter-spacing:5px;font-weight:500}
.login-header p{color:white;font-size:14px;line-height:1.6;font-weight:500}

.form-group{margin-bottom:18px}
.form-group label{color:#c8d0dc;display:block;margin-bottom:8px;font-size:14px;font-weight:500}

.input-wrap{position:relative}
.input-wrap input{width:100%;padding:12px 40px 12px 14px;background:#0d1525;border:1px solid #1e2d44;border-radius:8px;color:white;font-size:14px;outline:none}
.input-wrap input::placeholder{color:#4a5568}
.input-wrap input:focus{border-color:#2563eb}

.eye-btn{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:0;width:20px;height:20px}
.eye-btn svg{width:18px;height:18px;fill:none;stroke:#4a5568;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}

.extras{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;font-size:13px}
.extras label{display:flex;align-items:center;gap:6px;color:#8892a4;cursor:pointer}
.extras input[type="checkbox"]{width:16px;height:16px;accent-color:#2563eb;cursor:pointer}
.extras a{color:#2563eb;text-decoration:none;font-size:13px}
.extras a:hover{text-decoration:underline}

.btn-submit{width:100%;padding:13px;border:none;border-radius:8px;background:#2563eb;color:white;cursor:pointer;font-weight:600;font-size:15px;transition:background .2s}
.btn-submit:hover{background:#1d4ed8}

.error-msg{background:#dc2626;color:white;padding:10px;border-radius:6px;margin-bottom:15px;font-size:13px;text-align:center}

.login-footer{text-align:center;margin-top:24px;color:#8892a4;font-size:13px}
.login-footer a{color:#2563eb;text-decoration:none;font-weight:500}
.login-footer a:hover{text-decoration:underline}

@media(max-width:900px){
    body{flex-direction:column;padding:20px;gap:20px}
    .login-left{display:none}
    .login-right{width:100%;margin-right:0;border-radius:20px;padding:40px 30px}
    .login-box{max-width:400px}
}

@media(max-width:480px){
    body{padding:10px;gap:10px}
    .login-right{padding:30px 20px;border-radius:16px}
    .login-header .logo-row span{font-size:22px;letter-spacing:4px}
    .login-header p{font-size:13px}
    .form-group label{font-size:13px}
    .input-wrap input{padding:11px 38px 11px 12px;font-size:13px}
    .btn-submit{padding:12px;font-size:14px}
    .extras{flex-direction:column;gap:10px;align-items:flex-start}
}
</style>
</head>
<body>

<div class="login-left">
    <svg width="37" height="53" viewBox="0 0 37 53" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="26.9785" cy="43.3208" r="3" fill="#ffffff"/>
        <path d="M22.2598 51.4631C22.5628 51.3284 22.7998 51.0789 22.9188 50.7695C23.0378 50.4601 23.029 50.1161 22.8944 49.8131C22.7598 49.5102 22.5103 49.2731 22.2009 49.1541C21.8914 49.0351 21.5474 49.0439 21.2445 49.1785C19.8615 49.7947 18.1704 49.91 16.5293 49.6893C10.6749 48.8403 5.25149 44.4313 3.33478 38.7933C1.31772 33.0838 3.17894 26.6965 6.53436 21.4902C7.44919 20.0474 8.37331 18.6266 9.31541 17.209C9.93643 16.2742 10.5628 15.3443 11.1927 14.4131C11.8239 13.4796 12.4563 12.5481 13.0865 11.608C15.1972 8.47603 17.2131 5.24513 19.068 1.93363L16.9447 2.02347C21.7025 9.02347 26.4603 16.0235 31.2181 23.0235L31.1919 22.9833C32.9909 26.0258 33.9913 29.8118 33.9443 33.4127C33.9171 35.1216 33.6357 36.8141 33.0732 38.4048C32.9628 38.7174 32.9812 39.061 33.1242 39.3601C33.2673 39.6592 33.5233 39.8892 33.8359 39.9995C34.1485 40.1099 34.4921 40.0915 34.7912 39.9485C35.0903 39.8054 35.3203 39.5494 35.4306 39.2368C35.4306 39.2368 35.4306 39.2368 35.4306 39.2368C36.0899 37.3732 36.4132 35.4047 36.444 33.4537C36.4729 29.3045 35.4424 25.2908 33.3119 21.6583L33.2857 21.6181C28.5279 14.6181 23.7701 7.61813 19.0123 0.618135C18.4281 -0.241428 17.3986 -0.197868 16.889 0.707974C15.062 3.96876 13.1013 7.11201 11.0098 10.216C10.3852 11.1479 9.75453 12.0769 9.12179 13.0126C8.49053 13.9458 7.85971 14.8823 7.23314 15.8255C6.28253 17.2559 5.34771 18.6931 4.4231 20.1514C0.849809 25.6877 -1.38458 32.9081 0.971117 39.6076C3.26484 46.2002 9.2809 51.1272 16.1989 52.1674C18.1639 52.426 20.2912 52.3304 22.2598 51.4631Z" fill="#ffffff"/>
    </svg>
    <div class="brand">EVA</div>
</div>

<div class="login-right">
    <div class="login-box">

        <div class="login-header">
            <div class="logo-row">
                <svg width="37" height="53" viewBox="0 0 37 53" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="26.9785" cy="43.3208" r="3" fill="#3C75C6"/>
                    <path d="M22.2598 51.4631C22.5628 51.3284 22.7998 51.0789 22.9188 50.7695C23.0378 50.4601 23.029 50.1161 22.8944 49.8131C22.7598 49.5102 22.5103 49.2731 22.2009 49.1541C21.8914 49.0351 21.5474 49.0439 21.2445 49.1785C19.8615 49.7947 18.1704 49.91 16.5293 49.6893C10.6749 48.8403 5.25149 44.4313 3.33478 38.7933C1.31772 33.0838 3.17894 26.6965 6.53436 21.4902C7.44919 20.0474 8.37331 18.6266 9.31541 17.209C9.93643 16.2742 10.5628 15.3443 11.1927 14.4131C11.8239 13.4796 12.4563 12.5481 13.0865 11.608C15.1972 8.47603 17.2131 5.24513 19.068 1.93363L16.9447 2.02347C21.7025 9.02347 26.4603 16.0235 31.2181 23.0235L31.1919 22.9833C32.9909 26.0258 33.9913 29.8118 33.9443 33.4127C33.9171 35.1216 33.6357 36.8141 33.0732 38.4048C32.9628 38.7174 32.9812 39.061 33.1242 39.3601C33.2673 39.6592 33.5233 39.8892 33.8359 39.9995C34.1485 40.1099 34.4921 40.0915 34.7912 39.9485C35.0903 39.8054 35.3203 39.5494 35.4306 39.2368C35.4306 39.2368 35.4306 39.2368 35.4306 39.2368C36.0899 37.3732 36.4132 35.4047 36.444 33.4537C36.4729 29.3045 35.4424 25.2908 33.3119 21.6583L33.2857 21.6181C28.5279 14.6181 23.7701 7.61813 19.0123 0.618135C18.4281 -0.241428 17.3986 -0.197868 16.889 0.707974C15.062 3.96876 13.1013 7.11201 11.0098 10.216C10.3852 11.1479 9.75453 12.0769 9.12179 13.0126C8.49053 13.9458 7.85971 14.8823 7.23314 15.8255C6.28253 17.2559 5.34771 18.6931 4.4231 20.1514C0.849809 25.6877 -1.38458 32.9081 0.971117 39.6076C3.26484 46.2002 9.2809 51.1272 16.1989 52.1674C18.1639 52.426 20.2912 52.3304 22.2598 51.4631Z" fill="#3C75C6"/>
                </svg>
                <span>EVA</span>
            </div>
            <p>Sistema inteligente de<br>monitoreo de tanques</p>
        </div>

        <?php if(!empty($error)){ ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php } ?>

        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <div class="input-wrap">
                    <input type="email" name="email" placeholder="tucorreo@gmail.com" required>
                </div>
            </div>
            <div class="form-group">
                <label>Contraseña</label>
                <div class="input-wrap">
                    <input type="password" name="password" id="pass" placeholder="Ingresa tu Contraseña" required>
                    <button type="button" class="eye-btn" onclick="var p=document.getElementById('pass');p.type=p.type==='password'?'text':'password';">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#4a5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
            </div>
            <div class="extras">
                <label><input type="checkbox" name="recordar"> Recordarme</label>
                <a href="#">¿Olvidé mi contraseña?</a>
            </div>
            <button type="submit" class="btn-submit">Iniciar sesión</button>
        </form>

        <div class="login-footer">
            ¿No tenés cuenta? <a href="#">Contáctanos</a>
        </div>

    </div>
</div>

</body>
</html>