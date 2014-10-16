<?php
require 'password_compat/password.php';

/**
 *
 * @author kenta.fried@gmail.com
 */
class MitgliedController extends Zend_Controller_Action {
    
    /**
     * Cost parameter for BCRYPT that allows to change the CPU cost of the algorithm. The cost can
     * range from 4 to 31. It is recommended to use the highest cost possible, while keeping
     * response time reasonable (target 0.1 and 0.5 seconds for a hashing).
     */
    const ALGORITHMIC_COST = 15;

    public function init() {
        /* Initialize action controller here */
    }

    public function indexAction() {
        $mitglied = new Application_Model_Mitglied();
        $mitglied->setEmail('kenta.fried@gmail.com')
            ->setVorname("firstname")
            ->setName("lastname")
            ->setVerifizierungHash('qwerasdf');
        
        $this->sendVerificationEmail($mitglied);
    }

    public function hinzufuegenAction() {
        $form = new Application_Form_Mitglied();
        $this->view->form = $form;
        $form->submit->setLabel('Anmelden');
        
        if ($this->getRequest()
            ->isPost()) {
            $formData = $this->getRequest()
                ->getPost();
            
            if ($form->isValid($formData) && $this->isHuman()) {
                $mitglied = new Application_Model_Mitglied();
                $mitglied->setFromForm($form);
                $mitglied->setDatumErstellt(date('Y-m-d H:i:s'));
                // Hash zur Validierung der Anmeldung = Hash(UserEmail + Zufallszahl)
                $mitglied->setVerifizierungHash(sha1($mitglied->getEmail() . rand(1, 1000000)));
                $mitglied->setStatus('angemeldet');
                $mitglied->setPasswortHash(
                        password_hash($form->getValue('passwort'), PASSWORD_BCRYPT, 
                                array(
                                        "cost" => MitgliedController::ALGORITHMIC_COST
                                )));
                
                $mitgliedMapper = new Application_Model_DbTable_Mitglied();
                $mitgliedMapper->speichern($mitglied);
                
                $this->sendVerificationEmail($mitglied);
                $this->_helper->redirector('index', 'index');
            } else {
                $form->populate($formData);
            }
        }
    }

    public function aendernAction() {
        $form = new Application_Form_Mitglied();
        
        $form->submit->setLabel('Speichern');
        $this->view->form = $form;
        
        if ($this->getRequest()
            ->isPost()) {
            $formData = $this->getRequest()
                ->getPost();
            if ($form->isValid($formData)) {
                $mitglied = new Application_Model_Mitglied();
                $mitglied->setFromForm($form);
                $mitglied->setDatumGeandert(date('Y-m-d H:i:s'));
                
                $mitgliedMapper = new Application_Model_DbTable_Mitglied();
                $mitgliedMapper->speichern($mitglied);
                $this->_helper->redirector('index', 'index');
            } else {
                $form->populate($formData);
            }
        } else {
            $id = $this->_getParam('id', 0);
            if ($id > 0) {
                $mitgliedMapper = new Application_Model_DbTable_Mitglied();
                $form->populate($mitgliedMapper->finden($id)
                    ->toArray());
            }
        }
    }

    public function entfernenAction() {
        // action body
    }

    private function isHuman() {
        $captcha = $this->getRequest()
            ->getPost('captcha');
        $captchaId = $captcha ['id'];
        $captchaInput = $captcha ['input'];
        $captchaSession = new Zend_Session_Namespace('Zend_Form_Captcha_' . $captchaId);
        $captchaIterator = $captchaSession->getIterator();
        $captchaWord = $captchaIterator ['word'];
        return ($captchaInput == $captchaWord);
    }

    private function sendVerificationEmail($mitglied) {
        if (! $mitglied->getEmail() || ! $mitglied->getVerifizierungHash()) {
            throw new Exception('Cannot send email. No email address or verification hash provided.');
        }
        
        $emailConfig = $this->getInvokeArg('bootstrap')
            ->getOption('m2spende')['verification_email'];
        
        $fromAddress = $emailConfig ['fromAddress'];
        $fromName = $emailConfig ['fromName'];
        $subject = 'Willkommen bei Naturefund!';
        $verificationUrl = sprintf('%s?email=%s&hash=%s', $emailConfig ['url'], $mitglied->getEmail(), 
                $mitglied->getVerifizierungHash());
        
        $emailBodyHtml = new Zend_View();
        $emailBodyHtml->setScriptPath(APPLICATION_PATH . '/views/emails/');
        $emailBodyHtml->assign('vorname', $mitglied->getVorname());
        $emailBodyHtml->assign('url', $verificationUrl);
        
        $mail = new Zend_Mail('UTF-8');
        $mail->setBodyHtml($emailBodyHtml->render('verification_de.phtml'));
        $mail->setFrom($fromEmail, $fromName);
        $mail->addTo($mitglied->getEmail(), $mitglied->getVorname() . ' ' . $mitglied->getName());
        $mail->setSubject($subject);
        $mail->send();
    }
}







