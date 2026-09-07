<?php
require_once __DIR__ . '/../vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;

function eva_mail_config(): array {
 $cfg = @include __DIR__ . '/mail_config.php';
 if(is_array($cfg) && !empty($cfg['from_email'])) {
  $cfg['reply_to'] = $cfg['reply_to'] ?? 'hola@eva.com.ar';
  $cfg['base_url'] = $cfg['base_url'] ?? 'https://dashboard.elvigilantedeagua.com';
  return $cfg;
 }
 return ['host'=>'smtp.hostinger.com','port'=>465,'username'=>'no-reply@dashboard.elvigilantedeagua.com','password'=>'#VALzona122233','encryption'=>'ssl','from_email'=>'no-reply@dashboard.elvigilantedeagua.com','from_name'=>'EVA - El Vigilante de Agua','reply_to'=>'hola@eva.com.ar','base_url'=>'https://dashboard.elvigilantedeagua.com'];
}

function eva_phpmailer_base(PHPMailer $mail, array $cfg): void {
 $mail->CharSet = 'UTF-8';
 $mail->Timeout = 15;
 $mail->SMTPDebug = 0;
 $mail->isSMTP();
 $mail->Host = $cfg['host'];
 $mail->SMTPAuth = true;
 $mail->Username = $cfg['username'];
 $mail->Password = $cfg['password'];
 $enc = strtolower(trim($cfg['encryption'] ?? 'ssl'));
 if($enc === 'ssl' || $enc === 'smtps' || $enc === 'implicit'){
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
 } elseif($enc === 'tls' || $enc === 'starttls'){
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
 } else {
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
 }
 $mail->Port = (int)($cfg['port'] ?? 465);
 $mail->setFrom($cfg['from_email'] ?? 'no-reply@dashboard.elvigilantedeagua.com', $cfg['from_name'] ?? 'EVA - El Vigilante de Agua');
 if(!empty($cfg['reply_to'])) $mail->addReplyTo($cfg['reply_to'], $cfg['from_name'] ?? 'EVA - El Vigilante de Agua');
 $mail->isHTML(true);
}

function eva_enviar_mail_pedido_procesado($para_email, $para_nombre, $codigo_compra, $producto_nombre, $total) {
 if(!filter_var($para_email, FILTER_VALIDATE_EMAIL)) return false;
 $cfg = eva_mail_config();
 $asunto = 'Pedido '.$codigo_compra.' recibido - EVA El Vigilante de Agua';
 $cuerpo_text = "Hola $para_nombre,\n\nTu pedido $codigo_compra fue procesado correctamente.\nProducto: $producto_nombre\nImporte: $".number_format((float)$total,2,',','.')."\nEstado: Pendiente de aprobacion\n\nCuando se valide el pago, se te enviaran las credenciales por mail a $para_email.\nGuarda este codigo: $codigo_compra\n\nGracias por confiar en EVA.\nhttps://dashboard.elvigilantedeagua.com";
 $cuerpo_html = '<div style="font-family:Inter,Arial,sans-serif;max-width:600px;margin:0 auto;background:#f8fafc;padding:24px;border-radius:12px;border:1px solid #e2e8f0;"><h2 style="color:#0d2c54;margin:0 0 12px;">Pedido recibido!</h2><p style="color:#334155;">Hola <strong>'.htmlspecialchars($para_nombre).'</strong>,</p><p style="color:#334155;">Tu pedido <strong>'.htmlspecialchars($codigo_compra).'</strong> fue procesado correctamente.</p><div style="background:#fff;padding:16px;border-radius:10px;border:1px solid #dbeafe;margin:16px 0;"><p style="margin:4px 0;"><strong>Producto:</strong> '.htmlspecialchars($producto_nombre).'</p><p style="margin:4px 0;"><strong>Importe:</strong> $'.number_format((float)$total,2,',','.').'</p><p style="margin:4px 0;"><strong>Estado:</strong> <span style="background:#fef3c7;color:#92400e;padding:4px 10px;border-radius:20px;font-weight:700;">PENDIENTE</span></p><p style="margin:4px 0;"><strong>Codigo:</strong> '.htmlspecialchars($codigo_compra).'</p></div><p style="color:#2563eb;font-weight:600;">Cuando se valide el pago se te enviaran las credenciales por mail a '.htmlspecialchars($para_email).'.</p><p style="color:#64748b;font-size:13px;">Guarda este codigo: <strong>'.htmlspecialchars($codigo_compra).'</strong></p><p style="color:#64748b;font-size:12px;">Si tenes dudas, responde a este mail o escribi a hola@eva.com.ar</p></div>';
 try {
  $mail = new PHPMailer(true);
  eva_phpmailer_base($mail, $cfg);
  $mail->addAddress($para_email, $para_nombre);
  $mail->Subject = $asunto;
  $mail->Body = $cuerpo_html;
  $mail->AltBody = $cuerpo_text;
  $mail->send();
  error_log("EVA SMTP: pedido enviado a $para_email codigo $codigo_compra");
  return true;
 } catch(\PHPMailer\PHPMailer\Exception $e){
  error_log('EVA SMTP: error enviando pedido a '.$para_email.' - '.$e->getMessage());
  return false;
 } catch(Throwable $e){
  error_log('EVA SMTP: error enviando pedido a '.$para_email.' - '.$e->getMessage());
  return false;
 }
}

function eva_enviar_mail_credenciales($para_email, $para_nombre, $usuario_email, $password_temporal) {
 if(!filter_var($para_email, FILTER_VALIDATE_EMAIL)){
  error_log('EVA SMTP: email invalido '.$para_email);
  return false;
 }
 if(empty($password_temporal)) return false;
 $cfg = eva_mail_config();
 if(empty($cfg['host']) || empty($cfg['username']) || empty($cfg['password']) || empty($cfg['from_email'])){
  error_log('EVA SMTP: config incompleta para envio a '.$para_email);
  return false;
 }
 $asunto = 'Credenciales de acceso - EVA El Vigilante de Agua';
 $baseUrl = $cfg['base_url'] ?? 'https://dashboard.elvigilantedeagua.com';
 $cuerpo_text = "Hola $para_nombre,\n\nTus credenciales de acceso a EVA - El Vigilante de Agua fueron generadas correctamente.\n\nDatos de acceso:\nUsuario: $usuario_email\nContrasena: $password_temporal\n\nPodes ingresar al sistema desde:\n$baseUrl\n\nPor seguridad, te recomendamos cambiar tu contrasena despues de ingresar.\n\nSi no solicitaste estas credenciales, comunicate con el administrador del sistema.\n\nSaludos,\nEVA - El Vigilante de Agua";
 $cuerpo_html = '<div style="font-family:Inter,Arial,sans-serif;max-width:600px;margin:0 auto;background:#f8fafc;padding:24px;border-radius:12px;border:1px solid #e2e8f0;"><div style="text-align:center;margin-bottom:20px;"><h1 style="color:#0d2c54;margin:0;font-size:22px;">EVA - El Vigilante de Agua</h1><p style="color:#2563eb;font-weight:600;margin:4px 0 0;">Credenciales de acceso</p></div><p style="color:#334155;">Hola <strong>'.htmlspecialchars($para_nombre).'</strong>,</p><p style="color:#334155;">Tus credenciales de acceso a <strong>EVA - El Vigilante de Agua</strong> fueron generadas correctamente.</p><div style="background:#fff;padding:18px;border-radius:10px;border:1px solid #dbeafe;margin:18px 0;"><p style="margin:0 0 12px;color:#0d2c54;font-weight:700;">Datos de acceso:</p><p style="margin:6px 0;"><strong>Usuario:</strong> '.htmlspecialchars($usuario_email).'</p><p style="margin:6px 0;"><strong>Contrasena:</strong> <span style="background:#fef3c7;padding:4px 8px;border-radius:6px;font-weight:700;">'.htmlspecialchars($password_temporal).'</span></p></div><p style="text-align:center;margin:20px 0;"><a href="'.htmlspecialchars($baseUrl).'" style="display:inline-block;background:#2563eb;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:700;">INGRESAR A EVA</a></p><p style="color:#dc2626;background:#fef2f2;padding:10px;border-radius:8px;font-size:13px;text-align:center;">Por seguridad, cambia tu contrasena despues de ingresar.</p><p style="color:#64748b;font-size:13px;">Si no solicitaste estas credenciales, comunicate con el administrador del sistema.</p><p style="color:#334155;margin-top:16px;">Saludos,<br><strong>EVA - El Vigilante de Agua</strong></p><p style="color:#94a3b8;font-size:12px;text-align:center;margin-top:16px;">Este correo fue enviado desde '.htmlspecialchars($cfg['from_email']).' - No responder.</p></div>';
 try {
  $mail = new PHPMailer(true);
  eva_phpmailer_base($mail, $cfg);
  $mail->addAddress($para_email, $para_nombre);
  $mail->Subject = $asunto;
  $mail->Body = $cuerpo_html;
  $mail->AltBody = $cuerpo_text;
  $ok = $mail->send();
  if($ok){
   error_log("EVA SMTP: credenciales enviadas a $para_email");
   return true;
  }
  error_log('EVA SMTP: error enviando credenciales a '.$para_email.' - '.$mail->ErrorInfo);
  return false;
 } catch(\PHPMailer\PHPMailer\Exception $e){
  $msg = $e->getMessage();
  if(isset($mail) && !empty($mail->ErrorInfo)) $msg .= ' | '.$mail->ErrorInfo;
  error_log('EVA SMTP: error enviando credenciales a '.$para_email.' - '.$msg);
  return false;
 } catch(Throwable $e){
  error_log('EVA SMTP: error enviando credenciales a '.$para_email.' - '.$e->getMessage());
  return false;
 }
}

function eva_enviar_credenciales(string $destEmail, string $nombre, string $usuario, string $passwordPlano): bool {
 return eva_enviar_mail_credenciales($destEmail, $nombre, $usuario, $passwordPlano);
}
function eva_enviar_nueva_contrasena(string $destEmail, string $nombre, string $usuario, string $passwordPlano): bool {
 return eva_enviar_mail_credenciales($destEmail, $nombre, $usuario, $passwordPlano);
}
function eva_generar_password(int $len=12): string {
 if($len < 10) $len = 12;
 $chars='ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
 $p=''; $max=strlen($chars)-1;
 for($i=0;$i<$len;$i++) $p.=$chars[random_int(0,$max)];
 return $p;
}
