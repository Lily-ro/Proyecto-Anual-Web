<?php
require_once __DIR__ . '/../vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
function eva_mail_config(): array {
 return ['from_email'=>'no-reply@dashboard.elvigilantedeagua.com','from_name'=>'EVA - El Vigilante del Agua','base_url'=>'https://dashboard.elvigilantedeagua.com'];
}
function eva_enviar_credenciales(string $destEmail, string $nombre, string $usuario, string $passwordPlano): bool {
 $cfg=eva_mail_config();
 $mail=new PHPMailer(true);
 $mail->CharSet='UTF-8';
 $mail->setFrom($cfg['from_email'],$cfg['from_name']);
 $mail->addAddress($destEmail,$nombre);
 $mail->isHTML(true);
 $mail->Subject='Tus credenciales de acceso - EVA El Vigilante del Agua';
 $base=rtrim($cfg['base_url'],'/');
 $mail->Body="<div style='font-family:Segoe UI,Arial,sans-serif;max-width:600px;margin:0 auto;background:#f8fafc;padding:24px;border-radius:12px'><h2 style='color:#2563eb'>Bienvenido a EVA, ".htmlspecialchars($nombre)."</h2><p>Tu cuenta ha sido creada. Estas son tus credenciales de acceso:</p><table style='width:100%;background:white;border-radius:8px;padding:16px;border:1px solid #e2e8f0'><tr><td style='padding:8px;color:#64748b'>Usuario:</td><td style='padding:8px;font-weight:600'>".htmlspecialchars($usuario)."</td></tr><tr><td style='padding:8px;color:#64748b'>Contraseña temporal:</td><td style='padding:8px;font-weight:600;background:#fef3c7'>".htmlspecialchars($passwordPlano)."</td></tr></table><p><a href='{$base}/index.php' style='display:inline-block;background:#2563eb;color:white;padding:12px 24px;border-radius:8px;text-decoration:none;margin-top:16px'>Ingresar al sistema</a></p><p style='color:#64748b;font-size:13px;margin-top:20px'>Por seguridad, cambia tu contraseña luego del primer acceso desde tu perfil.</p><p style='color:#94a3b8;font-size:12px'>Este correo fue enviado desde {$cfg['from_email']} - No responder.</p></div>";
 $mail->AltBody="Hola {$nombre}, Usuario: {$usuario} Contraseña: {$passwordPlano} Accede en {$base}/index.php";
 $ok=$mail->send();
 if(!$ok) throw new \Exception($mail->ErrorInfo ?: 'Error al enviar correo');
 return true;
}
function eva_enviar_nueva_contrasena(string $destEmail, string $nombre, string $usuario, string $passwordPlano): bool {
 $cfg=eva_mail_config();
 $mail=new PHPMailer(true);
 $mail->CharSet='UTF-8';
 $mail->setFrom($cfg['from_email'],$cfg['from_name']);
 $mail->addAddress($destEmail,$nombre);
 $mail->isHTML(true);
 $mail->Subject='Tus credenciales fueron actualizadas - EVA El Vigilante del Agua';
 $base=rtrim($cfg['base_url'],'/');
 $mail->Body="<div style='font-family:Segoe UI,Arial,sans-serif;max-width:600px;margin:0 auto;background:#f8fafc;padding:24px;border-radius:12px'><h2 style='color:#2563eb'>Hola ".htmlspecialchars($nombre).",</h2><p>Un administrador actualizó tus credenciales de acceso en EVA.</p><table style='width:100%;background:white;border-radius:8px;padding:16px;border:1px solid #e2e8f0'><tr><td style='padding:8px;color:#64748b'>Usuario:</td><td style='padding:8px;font-weight:600'>".htmlspecialchars($usuario)."</td></tr><tr><td style='padding:8px;color:#64748b'>Nueva contraseña:</td><td style='padding:8px;font-weight:600;background:#fef3c7'>".htmlspecialchars($passwordPlano)."</td></tr></table><p><a href='{$base}/index.php' style='display:inline-block;background:#2563eb;color:white;padding:12px 24px;border-radius:8px;text-decoration:none;margin-top:16px'>Ingresar al sistema</a></p><p style='color:#64748b;font-size:13px;margin-top:20px'>Podés cambiar tu contraseña cuando quieras desde tu perfil.</p><p style='color:#94a3b8;font-size:12px'>Este correo fue enviado desde {$cfg['from_email']} - No responder.</p></div>";
 $mail->AltBody="Hola {$nombre}, tus credenciales fueron actualizadas. Usuario: {$usuario} Nueva contraseña: {$passwordPlano} Accede en {$base}/index.php";
 $ok=$mail->send();
 if(!$ok) throw new \Exception($mail->ErrorInfo ?: 'Error al enviar correo');
 return true;
}
function eva_generar_password(int $len=10): string {
 $chars='ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
 $p=''; for($i=0;$i<$len;$i++) $p.=$chars[random_int(0,strlen($chars)-1)];
 return $p;
}
