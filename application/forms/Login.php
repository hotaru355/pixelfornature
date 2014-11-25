<?php
class Application_Form_Login extends Zend_Form {

	public function init() {
		$this->setName("login");
		$this->setMethod('post');

		$email = $this->_getEmailElement('email', 'E-Mail');
		$passwort = $this->_getPasswordElement('passwort', 'Passwort');

		$this->addElements(array(
			$email,
			$passwort,
		));
	}

	private function _getEmailElement($name, $label) {
		$id = $name . 'Login';

		$email = new Zend_Form_Element_Text($id);
		$email
			->setName($name)
			->setRequired(true)
			->setValidators(
			array(
				(new Zend_Validate_StringLength())->setMin(0)->setMax(50),
				new Zend_Validate_EmailAddress()
			))
		->setFilters(
			array(
				new Zend_Filter_StringTrim()
			))
		->setAttribs(
			array(
				'id' => $id,
				'class' => 'form-control',
				'placeholder' => $label,
				'maxlength' => '50',
			));
		return $email;
	}

	private function _getPasswordElement($name, $label) {
		$id = $name . 'Login';

		$passwort = new Zend_Form_Element_Password($id);
		$passwort
			->setName($name)
			->setRequired(true)
			->setValidators(
			array(
				(new Zend_Validate_StringLength())->setMin(6)->setMax(50),
				new Zend_Validate_Alnum()
			))
		->setAttribs(
			array(
				'id' => $id,
				'class' => 'form-control',
				'placeholder' => $label,
				'maxlength' => '50',
			));
		return $passwort;
	}

}
