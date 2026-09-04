<?php
namespace PHPMailer\PHPMailer;
class PHPMailer {
 public $Host=''; public $SMTPAuth=false; public $Username=''; public $Password=''; public $SMTPSecure=''; public $Port=0;
 public $From=''; public $FromName=''; public $Subject=''; public $Body=''; public $AltBody=''; public $isHTML=false;
 public $CharSet='UTF-8'; public $ErrorInfo='';
 private $to=[]; private $isSMTP=false;
 public function __construct($exceptions=false){}
 public function isSMTP(){ $this->isSMTP=true; }
 public function setFrom($email,$name=''){ $this->From=$email; $this->FromName=$name; }
 public function addAddress($email,$name=''){ $this->to[]=$email; }
 public function isHTML($v){ $this->isHTML=(bool)$v; }
 public function send(){
   if(empty($this->to)){ $this->ErrorInfo='No recipient'; return false; }
   if(empty($this->From)) $this->From='no-reply@dashboard.elvigilantedeagua.com';
   $to=implode(',',$this->to);
   $headers="From: {$this->FromName} <{$this->From}>\r\n";
   $headers.="Reply-To: {$this->From}\r\n";
   $headers.="Return-Path: {$this->From}\r\n";
   $headers.="MIME-Version: 1.0\r\n";
   $headers.="Content-Type: ".($this->isHTML?"text/html":"text/plain")."; charset={$this->CharSet}\r\n";
   $headers.="X-Mailer: EVA\r\n";
   $headers.="Date: ".date(DATE_RFC2822)."\r\n";
   $headers.="Message-ID: <".uniqid()."@dashboard.elvigilantedeagua.com>\r\n";
   $subject=$this->Subject;
   $body=$this->Body;
   if(function_exists('mb_encode_mimeheader')) $subject=mb_encode_mimeheader($subject,'UTF-8','B',"\r\n");
   $params="-f {$this->From}";
   error_log("EVA mail attempt to {$to} from {$this->From} subject {$subject}");
   $ok=@mail($to,$subject,$body,$headers,$params);
   if(!$ok){ error_log("EVA mail() with -f failed, retry without -f"); $ok=@mail($to,$subject,$body,$headers); }
   if(!$ok){ $this->ErrorInfo='mail() failed - Hostinger sendmail deshabilitado o From rechazado. Verifica en hPanel -> Emails que el dominio tenga SPF y que no-reply exista o usa noreply@dashboard.elvigilantedeagua.com'; error_log("EVA mail failed to {$to}: ".$this->ErrorInfo); return false; }
   error_log("EVA mail() accepted for {$to}");
   return true;
 }
}
