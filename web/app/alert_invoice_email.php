<?php
error_reporting(E_STRICT);
date_default_timezone_set("Asia/Shanghai");//设定时区东八区
require_once('vendors/nmail/phpmailer.php');
include("vendors/nmail/class.smtp.php"); 
require_once("config/database.php");

function send_mail($invoice_period_start, $invoice_period_end, $invoice_number,$client_email, $client_company, $row2) {
    $subject = "Invoice from {$client_company} for {$invoice_period_start} - {$invoice_period_end}";
    $content = <<<EOT
Dear {$client_company},

    This is invoice {$invoice_number} for {$invoice_period_start} - {$invoice_period_end}.
    Please remit your due balance within the terms of your contractual
    agreement to avoid disconnection of service.

    --
    Autogenerated by billing system 
EOT;
    $mail             = new PHPMailer(); //new一个PHPMailer对象出来
    $mail->CharSet ="UTF-8";                   //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
    if ($row2['loginemail'] === 'false')
    {
         $mail->IsMail();
    }
    else
    {
         $mail->IsSMTP();
    }
    $mail->SMTPDebug  = 1;                     // 启用SMTP调试功能
                                               // 1 = errors and messages
                                               // 2 = messages only
    $mail->SMTPAuth   = $row2['loginemail'] === 'false' ? false : true;                  // 启用 SMTP 验证功能
    //$mail->SMTPSecure = "ssl";                 // 安全协议
    $mail->Host       = $row2['smtphost'];       // SMTP 服务器
    $mail->Port       = intval($row2['smtpport']);                   // SMTP服务器的端口号
    $mail->Username   = $row2['username'];   // SMTP服务器用户名
    $mail->Password   = $row2['password'];              // SMTP服务器密码
    $mail->SetFrom($row2['from']);
    //$mail->AddReplyTo("sisl@mail.yht.com","webtest");
    $mail->Subject    = $subject;
    $mail->Body  =$content;
    $real_path = APP . '/webroot/upload/invoice/' . $result[0][0]['pdf_file'];
    $mail->AddAttachment($real_path);
    $mail->AddAddress($client_email);
    if(!$mail->Send()) {
        echo "Mailer Error: " . $mail->ErrorInfo;
        return FALSE;
    } else {
        echo "Message sent!恭喜，邮件发送成功！";
        return TRUE;
    }

}


$class_dbconfig = new DATABASE_CONFIG();
$conn_config = $class_dbconfig->default;

$dbconn = pg_connect("host={$conn_config['host']} port={$conn_config['port']} dbname={$conn_config['database']} user={$conn_config['login']} password={$conn_config['password']}")
    or die('Could not connect: ' . pg_last_error());


$query = 'SELECT fromemail as "from", smtphost, smtpport,emailusername as username, emailpassword as  "password", emailname as "name", loginemail FROM system_parameter';
$result = pg_query($dbconn,$query);
$row2 = pg_fetch_assoc($result);
pg_free_result($result);

$sql =<<<EOT
SELECT 
client.client_id,
client.email AS client_email,
client.company,
invoice.invoice_number,
invoice.invoice_start,
invoice.invoice_end
FROM 
invoice 
LEFT JOIN client ON client.client_id::text = invoice.client_id::text order by invoice_id desc limit 1
EOT;
$result = pg_query($dbconn,$sql);
echo $sql;
while ($row = pg_fetch_assoc($result)) {
    print_r($row);
    $client_id = $row['client_id'];
    //$client_name = $row['name'];
    $client_email = $row['client_email'];
    //$system_email = $row['system_email'];
    $client_company = $row['company'];
    $invoice_period_start = $row['invoice_start'];
    $invoice_period_end = $row['invoice_end'];
    $invoice_number = $row['invoice_number'];
    //$balance = $row['balance'];
    //$notify_client_balance = $row['notify_client_balance'];
    //$notify_admin_balance = $row['notify_admin_balance'];
    //$allow_credit = round(abs($row['allowed_credit']), 3);
    //$type = $row['type'];
    
    send_mail($invoice_period_start, $invoice_period_end, $invoice_number,$client_email, $client_company, $row2);
}

pg_free_result($result);

pg_close($dbconn);

?>