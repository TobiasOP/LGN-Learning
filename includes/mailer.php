<?php
// includes/mailer.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

/**
 * Send email using PHPMailer
 * 
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $htmlBody HTML email body
 * @param string $textBody Plain text body (optional)
 * @return bool Success status
 */
function sendEmail($to, $subject, $htmlBody, $textBody = '') {
    $mail = new PHPMailer(true);
    
    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host       = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('SMTP_USERNAME');
        $mail->Password   = getenv('SMTP_PASSWORD');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = getenv('SMTP_PORT') ?: 587;
        $mail->CharSet    = 'UTF-8';
        
        // Sender
        $mail->setFrom(getenv('MAIL_FROM') ?: 'noreply@lgn.com', 'LGN E-Learning');
        $mail->addReplyTo('support@lgn.com', 'LGN Support');
        
        // Recipient
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $textBody ?: strip_tags($htmlBody);
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Email error: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Send verification email for registration
 */
function sendVerificationEmail($to, $name, $code) {
    $subject = 'Verifikasi Email - LGN E-Learning';
    
    $htmlBody = "
    <html>
    <head>
        <style>
            body { font-family: 'Poppins', Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f8fafc; padding: 30px; border-radius: 0 0 10px 10px; }
            .code { font-size: 36px; font-weight: bold; letter-spacing: 10px; color: #4f46e5; text-align: center; padding: 25px; background: white; border-radius: 10px; margin: 20px 0; border: 2px dashed #e2e8f0; }
            .footer { text-align: center; margin-top: 20px; color: #64748b; font-size: 12px; }
            .highlight { background: #fef3c7; padding: 15px; border-radius: 8px; border-left: 4px solid #f59e0b; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1 style='margin: 0; font-size: 28px;'>LGN E-Learning</h1>
                <p style='margin: 10px 0 0 0; opacity: 0.9;'>Verifikasi Email Anda</p>
            </div>
            <div class='content'>
                <p>Halo <strong>{$name}</strong>! 👋</p>
                <p>Terima kasih telah mendaftar di LGN E-Learning. Untuk menyelesaikan pendaftaran, masukkan kode verifikasi berikut:</p>
                
                <div class='code'>{$code}</div>
                
                <div class='highlight'>
                    <strong>⏰ Kode ini akan kadaluarsa dalam 15 menit.</strong>
                </div>
                
                <p style='margin-top: 20px;'>Jika Anda tidak mendaftar di LGN, abaikan email ini.</p>
                
                <hr style='border: none; border-top: 1px solid #e2e8f0; margin: 25px 0;'>
                
                <p style='color: #64748b; font-size: 13px;'>
                    <strong>Tips Keamanan:</strong><br>
                    • Jangan bagikan kode ini kepada siapapun<br>
                    • Tim LGN tidak akan pernah meminta kode verifikasi Anda
                </p>
            </div>
            <div class='footer'>
                <p>© " . date('Y') . " LGN E-Learning. All rights reserved.</p>
                <p>Platform E-Learning Terbaik di Indonesia</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $textBody = "Halo {$name}!\n\n"
              . "Terima kasih telah mendaftar di LGN E-Learning.\n"
              . "Kode verifikasi Anda: {$code}\n\n"
              . "Kode ini akan kadaluarsa dalam 15 menit.\n\n"
              . "Jika Anda tidak mendaftar di LGN, abaikan email ini.\n\n"
              . "LGN E-Learning";
    
    return sendEmail($to, $subject, $htmlBody, $textBody);
}

/**
 * Send password reset email
 */
function sendResetEmail($to, $name, $code) {
    $subject = 'Reset Password - LGN E-Learning';
    
    $htmlBody = "
    <html>
    <head>
        <style>
            body { font-family: 'Poppins', Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f8fafc; padding: 30px; border-radius: 0 0 10px 10px; }
            .code { font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #4f46e5; text-align: center; padding: 20px; background: white; border-radius: 10px; margin: 20px 0; }
            .footer { text-align: center; margin-top: 20px; color: #64748b; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1 style='margin: 0;'>LGN E-Learning</h1>
                <p style='margin:10px 0 0 0; opacity:0.9;'>Reset Password</p>
            </div>
            <div class='content'>
                <p>Halo <strong>{$name}</strong>,</p>
                <p>Kami menerima permintaan untuk mereset password akun Anda. Gunakan kode verifikasi berikut:</p>
                <div class='code'>{$code}</div>
                <p>Kode ini akan kadaluarsa dalam <strong>15 menit</strong>.</p>
                <p>Jika Anda tidak meminta reset password, abaikan email ini. Password Anda akan tetap aman.</p>
                <hr style='border:none;border-top:1px solid #e2e8f0;margin:20px 0;'>
                <p style='color:#64748b;font-size:14px;'>Jangan bagikan kode ini kepada siapapun termasuk pihak yang mengaku dari LGN.</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " LGN E-Learning. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $textBody = "Halo {$name},\n\n"
              . "Kami menerima permintaan untuk mereset password akun Anda.\n"
              . "Kode verifikasi: {$code}\n\n"
              . "Kode ini akan kadaluarsa dalam 15 menit.\n\n"
              . "Jika Anda tidak meminta reset password, abaikan email ini.\n\n"
              . "LGN E-Learning";
    
    return sendEmail($to, $subject, $htmlBody, $textBody);
}
