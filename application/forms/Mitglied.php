<?php
class Application_Form_Mitglied extends Zend_Form {

    public function init() {
        $this->setName("mitglied");
        $this->setMethod('post');
        
        $anrede = $this->_getAnredeElement();
        $vorname = $this->_getText50Element('vorname', 'Vorname:');
        $name = $this->_getText50Element('name', 'Name:');
        $strasse = $this->_getText50Element('strasse', 'Strasse:');
        $plz = $this->_getPlzElement();
        $ort = $this->_getText50Element('ort', 'Ort:');
        $land = $this->_getLandElement();
        $email = $this->_getEmailElement();
        $passwort = $this->_getPasswortElement('passwort', 'Neues Passwort:');
        $passwort2 = $this->_getPasswortWiederholtElement('passwort2', 'passwort', 'Passwort wiederholen:');
        $captcha = $this->_getCaptchaElement();
        $submit = $this->_getSubmitElement();
        
        $this->addElements(
                array(
                        $anrede,
                        $vorname,
                        $name,
                        $strasse,
                        $plz,
                        $ort,
                        $land,
                        $email,
                        $passwort,
                        $passwort2,
                        $captcha,
                        $submit
                ));
    }

    private function _getAnredeElement() {
        $anrede = new Zend_Form_Element_Radio('anrede');
        $anrede->setLabel('Anrede:')
            ->setMultiOptions(
                array(
                        'herr' => 'Herr',
                        'frau' => 'Frau',
                        'firma' => 'Firma'
                ))
            ->setValue('herr')
            ->setAttrib('class', '_')
            ->setSeparator('');
        return $anrede;
    }

    private function _getText50Element($id, $label) {
        $element = new Zend_Form_Element_Text($id);
        $element->setLabel($label)
            ->setRequired(true)
            ->setValidators(
                array(
                        (new Zend_Validate_StringLength())->setMin(0)
                            ->setMax(50)
                ))
            ->setFilters(array(
                new Zend_Filter_StringTrim()
        ))
            ->setAttrib('maxlength', '50');
        return $element;
    }

    private function _getPlzElement() {
        $plz = new Zend_Form_Element_Text('plz');
        $plz->setLabel('PLZ:')
            ->setRequired(true)
            ->setValidators(
                array(
                        (new Zend_Validate_StringLength())->setMin(5)
                            ->setMax(5),
                        (new Zend_Validate_Digits())->setMessage('Nur Nummern')
                ))
            ->setAttrib('maxlength', '5');
        return $plz;
    }

    private function _getLandElement() {
        $land = new Zend_Form_Element_Select('land');
        $land->setLabel('Land:')
            ->setRequired(true)
            ->addMultiOptions(
                array(
                        'de' => 'Deutschland',
                        'ca' => 'Kanada'
                ));
        return $land;
    }

    private function _getEmailElement() {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        
        $email = new Zend_Form_Element_Text('email');
        $email->setLabel('Email:')
            ->setRequired(true)
            ->setValidators(
                array(
                        (new Zend_Validate_StringLength())->setMin(0)
                            ->setMax(50),
                        new Zend_Validate_EmailAddress(),
                        new Zend_Validate_Db_NoRecordExists('mitglieder', 'email')
                ))
            ->setFilters(array(
                new Zend_Filter_StringTrim()
        ))
            ->setAttrib('maxlength', '50');
        return $email;
    }

    private function _getPasswortElement($id, $label) {
        $passwort = new Zend_Form_Element_Password($id);
        $passwort->setLabel($label)
            ->setRequired(true)
            ->setValidators(
                array(
                        (new Zend_Validate_StringLength())->setMin(6)
                            ->setMax(50),
                        new Zend_Validate_Alnum()
                ))
            ->setAttrib('maxlength', '50');
        return $passwort;
    }

    private function _getPasswortWiederholtElement($id, $passwortId, $label) {
        $passwort = new Zend_Form_Element_Password($id);
        $passwort->setLabel($label)
            ->setRequired(true)
            ->setValidators(
                array(
                        (new Zend_Validate_StringLength())->setMin(6)
                            ->setMax(50),
                        new Zend_Validate_Alnum(),
                        (new Zend_Validate_Identical())->setToken($passwortId)
                ))
            ->setAttrib('maxlength', '50');
        return $passwort;
    }

    private function _getCaptchaElement() {
        $captchaValidator = new Zend_Captcha_Image();
        $captchaValidator->setFont('../public/ttf/UbuntuMono-B.ttf')
            ->setWordlen(6)
            ->setImgDir('../public/images/captcha/')
            ->setImgUrl('/images/captcha/');
        
        $captcha = new Zend_Form_Element_Captcha('captcha', 
                array(
                        'label' => 'Hier eingeben :',
                        'captcha' => $captchaValidator
                ));
        return $captcha;
    }

    private function _getSubmitElement() {
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setRequired(false)
            ->setIgnore(true);
        return $submit;
    }
}

