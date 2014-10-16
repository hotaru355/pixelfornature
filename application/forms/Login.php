<?php
class Application_Form_Login extends Zend_Form {

    public function init() {
        $this->setName("login");
        $this->setMethod('post');
        
        $email = new Zend_Form_Element_Text('email');
        $email->setLabel('Email:')
            ->setRequired(true)
            ->setValidators(
                array(
                        (new Zend_Validate_StringLength())->setMin(0)
                            ->setMax(50)
                ))
            ->setFilters(
                array(
                        new Zend_Filter_StringTrim(),
                        new Zend_Filter_StringToLower()
                ));
        
        $passwort = new Zend_Form_Element_Password('passwort');
        $passwort->setLabel('Passwort:')
            ->setRequired(true)
            ->setValidators(
                array(
                        (new Zend_Validate_StringLength())->setMin(0)
                            ->setMax(50)
                ))
            ->setFilters(array(
                new Zend_Filter_StringTrim()
        ));
        
        $submit = new Zend_Form_Element_Submit('login');
        $submit->setLabel('Einloggen')
            ->setRequired(false)
            ->setIgnore(true);
        
        $this->addElements(array(
                $email,
                $passwort,
                $submit
        ));
    }
}

