<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once DOL_DOCUMENT_ROOT.'/includes/swiftmailer/lexer/lib/Doctrine/Common/Lexer/AbstractLexer.php';

// egulias autoloader lib
require_once DOL_DOCUMENT_ROOT.'/includes/swiftmailer/autoload.php';

require_once DOL_DOCUMENT_ROOT.'/includes/swiftmailer/lib/swift_required.php';
class Mail
{
    public $db;
    
    public function __construct($db = NULL)
    {
        if($db != NULL){
            $this->db = $db;
        }
        
    }
    
    /*VASA enviament de emails*/
    public static function sendEmail($plantilla, $destinatari, $titol, $contingut){

        try {
            // Create the SMTP Transport
            $transport = new Swift_SmtpTransport('defi.cloud', 465, 'ssl'); //587 o465 ssl
            $transport->setUsername('helpdesk@defi.ad');
            $transport->setPassword('fVt6uzBDDl9UJlF');
            // Create the Mailer using your created Transport
            $mailer = new Swift_Mailer($transport);
            // Create a message
            $message = new Swift_Message();
            // Set a "subject"
            $message->setSubject($titol);
            // Set the "From address"
            $message->setFrom(['helpdesk@defi.ad' => 'HELPDESK DEFI']);
            // Set the "To address" [Use setTo method for multiple recipients, argument should be array]
            $message->addTo($destinatari);
            // Set a "Body"
            $message->addPart($contingut, 'text/html');

            // Send the message
            $result = $mailer->send($message);
        } catch (Exception $e) {
          echo $e->getMessage(); die();
        }
        
        return TRUE;
    }

}
?>

