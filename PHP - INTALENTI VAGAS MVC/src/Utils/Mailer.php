<?php

namespace src\Utils;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    public static function enviar($destinatarioEmail, $destinatarioNome, $assunto, $mensagemHTML)
    {
        $mail = new PHPMailer(true);

        try {
            // Configurações do Servidor
            //$mail->SMTPDebug = 2; // Debug off em produção
            $mail->isSMTP();
            $mail->Host       = 'br1168.hostgator.com.br';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'notificacao@intalenti.com.br';
            $mail->Password   = 'Intalenti18!';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            // Bypass SSL certificado (HostGator fix)
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Remetente
            $mail->setFrom('notificacao@intalenti.com.br', 'IntalenTI Vagas');
            $mail->addAddress($destinatarioEmail, $destinatarioNome);

            // Conteúdo
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $assunto;

            $corPrincipal = '#007bff';

            $template = "
            <div style='background-color: #f4f4f4; padding: 20px; font-family: Arial, sans-serif;'>
                <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1);'>
                    <div style='background-color: $corPrincipal; padding: 20px; text-align: center;'>
                        <h1 style='color: #ffffff; margin: 0; font-size: 24px;'>IntalenTI</h1>
                    </div>
                    <div style='padding: 30px; color: #333333; line-height: 1.6;'>
                        <h3 style='color: $corPrincipal; margin-top: 0;'>Olá, $destinatarioNome!</h3>
                        <div style='font-size: 16px;'>
                            $mensagemHTML
                        </div>
                        <br>
                        <hr style='border: 0; border-top: 1px solid #eee;'>
                        <p style='font-size: 12px; color: #999; text-align: center; margin-top: 20px;'>
                            Este é um e-mail automático.<br>
                            <a href='https://intalenti.com.br/vagas' style='color: $corPrincipal; text-decoration: none;'>Acessar Plataforma</a>
                        </p>
                    </div>
                </div>
            </div>";

            $mail->Body    = $template;
            $mail->AltBody = strip_tags(str_replace('<br>', "\n", $mensagemHTML));

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Erro PHPMailer: {$mail->ErrorInfo}");
            return false;
        }
    }
}
