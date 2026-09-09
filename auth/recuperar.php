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
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Recuperar contraseña - EVA</title><style>*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif}body{background:#0b1120;min-height:100vh;display:flex;justify-content:center;align-items:center;padding:20px}.card{background:#111c30;padding:40px;border-radius:16px;width:100%;max-width:440px;color:#c8d0dc}h2{color:white;margin-bottom:12px;font-size:20px}p{font-size:14px;margin-bottom:16px;color:#8892a4}label{display:block;margin-bottom:6px;color:#c8d0dc;font-size:14px}input{width:100%;padding:12px;background:#0d1525;border:1px solid #1e293b;border-radius:8px;color:#e2e8f0;outline:none;margin-bottom:14px}input:focus{border-color:#2563eb}button{width:100%;padding:12px;background:#2563eb;color:white;border:none;border-radius:8px;cursor:pointer;font-weight:600;font-size:15px}button:hover{background:#1d4ed8}.msg{background:#16a34a;color:white;padding:10px;border-radius:6px;margin-bottom:12px;font-size:13px;text-align:center}.err{background:#dc2626;color:white;padding:10px;border-radius:6px;margin-bottom:12px;font-size:13px;text-align:center}a{color:#3b82f6;text-decoration:none;font-size:13px}a:hover{text-decoration:underline}</style></head><body><div class="card"><h2>Recuperar contraseña</h2><p>Ingresa tu email y te enviaremos instrucciones.</p><?php if($msg) echo '<div class="msg">'.$msg.'</div>'; if($err) echo '<div class="err">'.$err.'</div>'; ?><form method="POST"><label>Email</label><input type="email" name="email" placeholder="tu@email.com" required><button type="submit">Enviar instrucciones</button></form><p style="margin-top:16px;text-align:center"><a href="../index.php">Volver al login</a></p></div></body></html>
