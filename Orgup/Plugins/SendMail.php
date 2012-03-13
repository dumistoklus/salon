<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 12.10.11
 * Time: 16:50
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Plugins;
use \Orgup\Application\Registry;

require_once(ROOTDIR.'system/Mailer/class.phpmailer.php' );

class SendMail {

    public static function send($email, $subject, $body)
    {
        $feedback_conf = Registry::instance('mail')->get('mail');

        $Mailer = new \PHPMailer();

        $Mailer->Host   = $feedback_conf['feedback_host'];
        $Mailer->SMTPAuth = TRUE;
        $Mailer->CharSet  = 'utf-8';
        $Mailer->Mailer   = 'smtp';
        $Mailer->Encoding = 'quoted-printable';

        $Mailer->From   = $feedback_conf['feedback_mail'];

        $Mailer->Username = $feedback_conf['feedback_user'];
        $Mailer->Password = $feedback_conf['feedback_password'];

        $Mailer->WordWrap = 120;


        $Mailer->AddAddress( $email);
        $Mailer->Subject  = $subject;
        $Mailer->Body   = $body;

        return $Mailer->Send();
    }
}
