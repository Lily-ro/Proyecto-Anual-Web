<?php
session_start();
require_once(__DIR__ . '/config/db.php');
$token=$_GET['token']??$_POST['token']??'';
$msg=''; $err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $token=trim($_POST['token']??''); $pass=$_POST['password']??''; $conf=$_POST['confirm']??'';
 if(strlen($pass)<6) $err="La contraseña debe tener al menos 6 caracteres.";
 elseif($pass!==$conf) $err="Las contraseñas no coinciden.";
 else{
  try{
   $pdo=eva_pdo();
   $st=$pdo->prepare("SELECT email,expira,usado FROM password_resets WHERE token=:t LIMIT 1");
   $st->execute([':t'=>$token]); $r=$st->fetch();
   if(!$r || $r['usado'] || strtotime($r['expira'])<time()) $err="Token inválido o expirado.";
   else{
    $hash=password_hash($pass,PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE usuarios SET password_hash=:h WHERE email=:e")->execute([':h'=>$hash,':e'=>$r['email']]);
    $pdo->prepare("UPDATE password_resets SET usado=1 WHERE token=:t")->execute([':t'=>$token]);
    $msg="Contraseña restablecida. <a href='index.php'>Iniciar sesión</a>";
   }
  }catch(Throwable $e){ $err="Error interno."; }
 }
}
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Restablecer - EVA</title><style>*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif}body{background:#0b1120;min-height:100vh;display:flex;justify-content:center;align-items:center;padding:20px}.card{background:#111c30;padding:40px;border-radius:16px;width:100%;max-width:440px;color:#c8d0dc}h2{color:white;margin-bottom:12px}input{width:100%;padding:12px;background:#0d1525;border:1px solid #1e2d44;border-radius:8px;color:white;margin-bottom:12px}button{width:100%;padding:13px;background:#2563eb;color:white;border:none;border-radius:8px;font-weight:600;cursor:pointer}a{color:#2563eb;text-decoration:none}.msg{background:#065f46;color:white;padding:10px;border-radius:6px;margin-bottom:12px}.err{background:#dc2626;color:white;padding:10px;border-radius:6px;margin-bottom:12px}</style></head><body><div class="card"><h2>Nueva contraseña</h2><?php if($msg) echo '<div class="msg">'.$msg.'</div>'; if($err) echo '<div class="err">'.htmlspecialchars($err).'</div>'; if(empty($msg)): ?><form method="POST"><input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>"><input type="password" name="password" placeholder="Nueva contraseña" required><input type="password" name="confirm" placeholder="Confirmar contraseña" required><button type="submit">Restablecer</button></form><?php endif; ?><p style="margin-top:16px;text-align:center"><a href="index.php">← Volver al login</a></p></div></body></html>
