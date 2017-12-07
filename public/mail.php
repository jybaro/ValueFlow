<?php

//require_once('../library/PHPMailer_v5.1/class.phpmailer.php');
//require_once('vendor/phpmailer/phpmailer/src/PHPMailer.php');

/*


 ***********
 * PHPMailer
 https://gistpages.com/posts/phpmailer_smtp_error_failed_to_connect_to_server_permission_denied_13_fix
 $ setsebool -P httpd_can_sendmail 1
 $ setsebool -P httpd_can_network_connect 1


 ************
 * WKHTML2PDF
 https://stackoverflow.com/questions/12784814/wkhtmltopdf-integrated-with-php-doesnt-work-on-centos-access-deny
 setenforce 0

 https://github.com/zakird/wkhtmltopdf_binary_gem/issues/19
 yum install libjpeg libpng12 libXrender libXext fontconfig
 yum install libpng
 https://stackoverflow.com/questions/30738963/wkhtmltopdf-unreadable-fonts
 yum install libXext  libXrender  fontconfig  libfontconfig.so.1
 yum install urw-fonts

 chown -R apache:apache public/
 chmod -R 775 public/
 yum install php-xml
 
 
 */

require_once('../vendor/autoload.php');


define('SMTP_SERVER', 'mail.nedetel.net');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'sait@nedetel.net');
define('SMTP_PASSWORD', 'n3D1$207*');

define('MAIL_ORDERS_ADDRESS', 'sait@nedetel.net');
define('MAIL_ORDERS_NAME', 'SAIT');


        try{
            //PDF
            unlink('prueba2.pdf');
            $snappy = new Knp\Snappy\Pdf('../vendor/bin/wkhtmltopdf-amd64');
            $msg = ('<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><style>body{font:serif;}</style></head><body><h1>PDF autogenerado</h1><p>Cuerpo de PDF en HTML.</p></body></html>');
            file_put_contents( 'prueba.html', $msg);
            $msg = file_get_contents('prueba.html');
            //$msg = utf8_decode($msg);
            $snappy->generateFromHtml($msg, 'prueba2.pdf', array('encoding' => 'utf-8'));

            //Hoja de cálculo en Excel
            $header = array(
                'created'=>'date',
                'product_id'=>'integer',
                'quantity'=>'#,##0',
                'amount'=>'price',
                'description'=>'string',
                'tax'=>'[$$-1009]#,##0.00;[RED]-[$$-1009]#,##0.00',
            );
            $data = array(
                array('2015-01-01',873,1,'44.00','misc','=D2*0.05'),
                array('2015-01-12',324,2,'88.00','none','=D3*0.05'),
            );

            $writer = new XLSXWriter();
            $writer->writeSheetHeader('Sheet1', $header );
            foreach($data as $row)
                $writer->writeSheetRow('Sheet1', $row );
            $writer->writeToFile('example.xlsx');

            //MAIL
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->IsSMTP();
            $mail->SMTPSecure = 'tls';
            $mail->SMTPAuth = true;
            $mail->Host = SMTP_SERVER;
            $mail->Port = SMTP_PORT;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPDebug = 2;
            $mail->SetFrom(MAIL_ORDERS_ADDRESS, MAIL_ORDERS_NAME);
            $mail->Subject = 'prueba';
            $mail->MsgHTML('<b>Esto es una prueba desde SAIT</b>');
            $mail->AddAddress('edgar.valarezo@gmail.com');
            //$mail->AddAddress('sminga@nedetel.net');
            //$mail->AddAddress('dcedeno@nedetel.net');
            $mail->AddAttachment('prueba.txt');
            $mail->AddAttachment('prueba2.pdf');
            $mail->AddAttachment('example.xlsx');
            $mail->AddBCC(MAIL_ORDERS_ADDRESS, MAIL_ORDERS_NAME);

            if(!$mail->Send()) throw new Exception($mail->ErrorInfo);
        }
        catch(Exception $e){
            echo $e->getMessage();
        }
