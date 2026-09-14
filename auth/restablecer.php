<?php
session_start();
require_once(__DIR__ . '/../config/db.php');
try{
 $pdo=eva_pdo();
 $pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, email VARCHAR(255) NOT NULL, token VARCHAR(255) NOT NULL, expira DATETIME NOT NULL, usado TINYINT(1) NOT NULL DEFAULT 0, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, KEY email (email), KEY token (token)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}catch(Throwable $e){}

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
   if(!$r || (int)($r['usado']??0)===1 || strtotime((string)$r['expira'])<time()) $err="Token inválido o expirado.";
   else{
    $hash=password_hash($pass,PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE usuarios SET password_hash=:h WHERE email=:e")->execute([':h'=>$hash,':e'=>$r['email']]);
    $pdo->prepare("UPDATE password_resets SET usado=1 WHERE token=:t")->execute([':t'=>$token]);
<<<<<<< HEAD
    $msg="Contraseña restablecida. <a href='../index.php' style='color:#3b82f6'>Iniciar sesión</a>";
=======
    $msg="Contraseña restablecida correctamente.";
>>>>>>> 9377b99b358083fc68b48733685dd42fd3da9c99
   }
  }catch(Throwable $e){ $err="Error interno."; error_log('restablecer error: '.$e->getMessage()); }
 }
}
?>
<<<<<<< HEAD
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Restablecer - EVA</title><style>*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif}body{background:#0b1120;min-height:100vh;display:flex;justify-content:center;align-items:center;padding:20px}.card{background:#111c30;padding:40px;border-radius:16px;width:100%;max-width:440px;color:#c8d0dc}h2{color:white;margin-bottom:12px;font-size:20px}label{display:block;margin-bottom:6px;color:#c8d0dc;font-size:14px}input{width:100%;padding:12px;background:#0d1525;border:1px solid #1e293b;border-radius:8px;color:#e2e8f0;outline:none;margin-bottom:14px}input:focus{border-color:#2563eb}button{width:100%;padding:12px;background:#2563eb;color:white;border:none;border-radius:8px;cursor:pointer;font-weight:600}button:hover{background:#1d4ed8}.msg{background:#16a34a;color:white;padding:10px;border-radius:6px;margin-bottom:12px;font-size:13px;text-align:center}.err{background:#dc2626;color:white;padding:10px;border-radius:6px;margin-bottom:12px;font-size:13px;text-align:center}</style></head><body><div class="card"><h2>Restablecer contraseña</h2><?php if($msg) echo '<div class="msg">'.$msg.'</div>'; if($err) echo '<div class="err">'.$err.'</div>'; if(!$msg || strpos($msg,'Iniciar')===false){ ?><form method="POST"><input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>"><label>Nueva contraseña</label><input type="password" name="password" required><label>Confirmar contraseña</label><input type="password" name="confirm" required><button type="submit">Actualizar contraseña</button></form><?php } ?><p style="margin-top:14px;text-align:center"><a href="../index.php" style="color:#3b82f6;text-decoration:none">Volver al login</a></p></div></body></html>
=======
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Restablecer contraseña - EVA</title>
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
.eye-btn{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:0;width:20px;height:20px;display:flex;align-items:center;justify-content:center}
.eye-btn svg{width:18px;height:18px;fill:none;stroke:#4a5568;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;transition:stroke .2s}
.eye-btn:hover svg{stroke:#8892a4}
.btn{display:inline-block;padding:12px 24px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-weight:600;font-size:14px;cursor:pointer;text-decoration:none;transition:background .2s}
.btn:hover{background:#1d4ed8}
.msg{background:rgba(76,175,80,0.12);color:#4caf50;padding:10px 14px;border-radius:8px;margin-bottom:16px;font-size:13px;border:1px solid rgba(76,175,80,0.2)}
.msg a{color:#4caf50;font-weight:600}
.err{background:rgba(220,38,38,0.12);color:#f87171;padding:10px 14px;border-radius:8px;margin-bottom:16px;font-size:13px;border:1px solid rgba(220,38,38,0.2)}
.back{display:inline-block;margin-top:20px;color:#2563eb;text-decoration:none;font-size:13px}
.back:hover{text-decoration:underline}
@media(max-width:480px){.card{padding:28px 20px;border-radius:12px}h2{font-size:18px}}
</style>
</head>
<body>
<div class="card">
 <h2>Restablecer contraseña</h2>
 <?php if(!empty($msg)){?>
  <div class="msg"><?php echo $msg;?></div>
  <a class="back" href="../index.php">Ir al login →</a>
 <?php}elseif(!empty($err)){?>
  <div class="err"><?php echo htmlspecialchars($err);?></div>
  <a class="back" href="recuperar.php">← Solicitar nuevo enlace</a>
 <?php}elseif(!empty($token)){?>
  <p>Ingresá tu nueva contraseña.</p>
  <form method="POST">
   <input type="hidden" name="token" value="<?php echo htmlspecialchars($token);?>">
   <div class="input-wrap">
    <input type="password" id="restPass" name="password" placeholder="Nueva contraseña (mín. 6 caracteres)" required>
    <button type="button" class="eye-btn" id="eyeRestBtn">
     <svg id="eyeROpen" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
     <svg id="eyeRClosed" viewBox="0 0 24 24" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
    </button>
   </div>
   <div class="input-wrap">
    <input type="password" name="confirm" placeholder="Confirmar contraseña" required>
   </div>
   <button type="submit" class="btn">Restablecer contraseña</button>
  </form>
 <?php}else{?>
  <div class="err">Token no válido o enlace expirado.</div>
  <a class="back" href="recuperar.php">← Solicitar nuevo enlace</a>
 <?php }?>
</div>
<script>
(function(){
 var btn=document.getElementById('eyeRestBtn');
 if(!btn)return;
 var inp=document.getElementById('restPass');
 var o=document.getElementById('eyeROpen');
 var c=document.getElementById('eyeRClosed');
 btn.addEventListener('click',function(){
  var isPass=inp.type==='password';
  inp.type=isPass?'text':'password';
  o.style.display=isPass?'none':'block';
  c.style.display=isPass?'block':'none';
 });
})();
</script>
</body>
</html>
>>>>>>> 9377b99b358083fc68b48733685dd42fd3da9c99
