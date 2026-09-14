<?php
session_start();
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../config/mail.php');
$msg=''; $err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $email=trim($_POST['email']??'');
 if(!filter_var($email,FILTER_VALIDATE_EMAIL)) $err="Email inválido.";
 else{
   try{
   $pdo=eva_pdo();
   try{$pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, email VARCHAR(255) NOT NULL, token VARCHAR(255) NOT NULL, expira DATETIME NOT NULL, usado TINYINT(1) NOT NULL DEFAULT 0, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, KEY email (email), KEY token (token)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");}catch(Throwable $e){}
   $st=$pdo->prepare("SELECT id_usuario,nombre,apellido FROM usuarios WHERE email=:e LIMIT 1");
   $st->execute([':e'=>$email]); $u=$st->fetch();
   if($u){
    $token=bin2hex(random_bytes(32));
    $pdo->prepare("INSERT INTO password_resets (email,token,expira) VALUES (:e,:t,DATE_ADD(NOW(),INTERVAL 1 HOUR))")->execute([':e'=>$email,':t'=>$token]);
     $link=(eva_mail_config()['base_url']??'https://dashboard.elvigilantedeagua.com').'/auth/restablecer.php?token='.$token;
    $html='<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;background:#f8fafc;padding:24px;border-radius:12px;border:1px solid #e2e8f0;"><h2 style="color:#0d2c54">Recuperar contraseña - EVA</h2><p>Hola '.htmlspecialchars($u['nombre']).',</p><p>Solicitaste restablecer tu contraseña.</p><p><a href="'.htmlspecialchars($link).'" style="display:inline-block;background:#2563eb;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:700">Restablecer contraseña</a></p><p style="color:#64748b;font-size:13px">Este enlace expira en 1 hora. Si no solicitaste esto, ignorá este correo.</p></div>';
    $txt="Hola {$u['nombre']},\nSolicitaste restablecer tu contraseña.\nLink: $link\nExpira en 1 hora.";
    try{
     $cfg=eva_mail_config();
     $mail=new PHPMailer\PHPMailer\PHPMailer(true);
      require_once __DIR__.'/../vendor/phpmailer/src/Exception.php';
      require_once __DIR__.'/../vendor/phpmailer/src/PHPMailer.php';
      require_once __DIR__.'/../vendor/phpmailer/src/SMTP.php';
     eva_phpmailer_base($mail,$cfg);
     $mail->addAddress($email,$u['nombre'].' '.$u['apellido']);
     $mail->Subject='Recuperar contraseña - EVA El Vigilante de Agua';
     $mail->Body=$html; $mail->AltBody=$txt;
     $mail->send();
    }catch(Throwable $e){ error_log('recuperar mail: '.$e->getMessage()); }
   }
   $msg="Si el email existe, recibirás un correo con instrucciones.";
  }catch(Throwable $e){ $err="Error interno."; error_log($e->getMessage()); }
 }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Recuperar contraseña - EVA</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif}
body{background:#0b1120;min-height:100vh;display:flex;justify-content:center;align-items:center;padding:20px}
.card{background:#111c30;padding:40px;border-radius:16px;width:100%;max-width:440px;color:#c8d0dc}
h2{color:white;margin-bottom:12px;font-size:20px}
p{font-size:14px;margin-bottom:16px;color:#8892a4}
.input-wrap{position:relative;margin-bottom:16px}
.input-wrap input{width:100%;padding:12px 14px;background:#0d1525;border:1px solid #1e2d4a;border-radius:8px;color:#e2e8f0;font-size:14px;outline:none;transition:border-color .2s}
.input-wrap input::placeholder{color:#4a5568}
.input-wrap input:focus{border-color:#2563eb}
.btn{display:inline-block;padding:12px 24px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-weight:600;font-size:14px;cursor:pointer;text-decoration:none;transition:background .2s}
.btn:hover{background:#1d4ed8}
.msg{background:rgba(76,175,80,0.12);color:#4caf50;padding:10px 14px;border-radius:8px;margin-bottom:16px;font-size:13px;border:1px solid rgba(76,175,80,0.2)}
.err{background:rgba(220,38,38,0.12);color:#f87171;padding:10px 14px;border-radius:8px;margin-bottom:16px;font-size:13px;border:1px solid rgba(220,38,38,0.2)}
.back{display:inline-block;margin-top:20px;color:#2563eb;text-decoration:none;font-size:13px}
.back:hover{text-decoration:underline}
@media(max-width:480px){.card{padding:28px 20px;border-radius:12px}h2{font-size:18px}}
</style>
</head>
<body>
<div class="card">
 <h2>Recuperar contraseña</h2>
 <p>Ingresá tu email y te enviaremos las instrucciones para restablecer tu contraseña.</p>
 <?php if(!empty($msg)){?><div class="msg"><?php echo $msg;?></div><?php }?>
 <?php if(!empty($err)){?><div class="err"><?php echo htmlspecialchars($err);?></div><?php }?>
 <?php if(empty($msg)){?>
 <form method="POST">
  <div class="input-wrap">
   <input type="email" name="email" placeholder="tucorreo@gmail.com" required autofocus>
  </div>
  <button type="submit" class="btn">Enviar instrucciones</button>
 </form>
 <?php }?>
 <a class="back" href="../index.php">← Volver al login</a>
</div>
</body>
</html>
