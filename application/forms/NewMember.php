<?php
class Application_Form_NewMember extends Zend_Form {
	public $postfix;
	private $identity;
	private $skipIdentity;

	function __construct($idPostfix = '', $identity = null, $skipIdentity = false) {
		$this->postfix = $idPostfix;
		$this->identity = $identity;
		$this->skipIdentity = $skipIdentity;
		parent::__construct();
	}

	public function init() {
		$this->setName("mitglied");
		$this->setMethod('post');

		$vorname = $this->_getText50Element('vorname', 'Vorname', true);
		$nachname = $this->_getText50Element('nachname', 'Nachname', true);
		$email = $this->_getEmailElement('email', 'E-Mail');
		$strasse = $this->_getText50Element('strasse', 'Strasse');
		$plz = $this->_getPlzElement('plz', 'PLZ');
		$ort = $this->_getText50Element('ort', 'Stadt');
		$telefon = $this->_getText50Element('telefon', 'Telefon');
		$passwort = $this->_getPasswortElement('passwort', 'passwortWiederholt', 'Passwort');
		$passwortWiederholt = $this->_getPasswortWiederholtElement('passwortWiederholt', 'passwort', 'Passwort Wiederholung');

		$this->addElements(
			array(
				$vorname,
				$nachname,
				$email,
				$strasse,
				$plz,
				$ort,
				$telefon,
				$passwort,
				$passwortWiederholt,
			));
	}

	private function _getText50Element($name, $label, $required = false) {
		$id = $name . $this->postfix;
		$element = new Zend_Form_Element_Text($id);
		$element
			->setName($name)
			->setAttribs(
			array(
				'id' => $id,
				'class' => 'form-control',
				'placeholder' => $label,
				'maxlength' => '50',
			))
			->setRequired($required)
			->setValidators(
			array(
				(new Zend_Validate_StringLength())->setMin(0)->setMax(50)->setMessage('Bitte kürzer als 50 Buchstaben halten')
			))
			->setFilters(
			array(
				new Zend_Filter_StringTrim()
			));
		return $element;
	}

	private function _getEmailElement($name, $label) {
		$id = $name . $this->postfix;
		$validators = array(
			(new Zend_Validate_StringLength())->setMin(0)->setMax(50)->setMessage('Bitte kürzer als 50 Buchstaben halten'),
			(new Zend_Validate_EmailAddress())->setMessage('Das ist keine gültige E-Mail')
		);
		if (!$this->skipIdentity) {
			array_push($validators, (new Zend_Validate_Db_NoRecordExists('mitglieder', 'email', $this->identity))->setMessage('Ein Konto mit dieser E-Mail existiert bereits'));
		}

		$email = new Zend_Form_Element_Text($id);
		$email
			->setName($name)
			->setRequired(true)
			->setValidators($validators)
			->setFilters(
			array(
				new Zend_Filter_StringTrim(),
				new Zend_Filter_StringToLower()
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

	private function _getPlzElement($name, $label) {
		$id = $name . $this->postfix;

		$plz = new Zend_Form_Element_Text($id);
		$plz
			->setName($name)
			->setRequired(false)
			->setValidators(
			array(
				(new Zend_Validate_StringLength())->setMin(5)->setMax(5)->setMessage('Bitte 5 Ziffern angeben'),
				(new Zend_Validate_Digits())->setMessage('Bitte nur Nummern verwenden')))
		                              ->setAttribs(
			array(
				'id' => $id,
				'class' => 'form-control',
				'placeholder' => $label,
				'maxlength' => '5'));
		return $plz;
	}

	private function _getPasswortElement($name, $passwortWiederholtId, $label) {
		$id = $name . $this->postfix;

		$passwort = new Zend_Form_Element_Password($id);
		$passwort
			->setName($name)
			->setRequired(true)
			->setValidators(
			array(
				(new Zend_Validate_StringLength())->setMin(6)->setMax(50)->setMessage('Bitte zwischen 6-50 Zeichen verwenden'),
				(new Zend_Validate_Alnum())->setMessage('Bitte nur Buchstaben und Ziffern verwenden'),
				//(new Zend_Validate_Identical())->setToken($passwortWiederholtId)->setMessage('Die Passwörter stimmen nicht überein')
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

	private function _getPasswortWiederholtElement($name, $passwortId, $label) {
		$id = $name . $this->postfix;

		$passwort = new Zend_Form_Element_Password($id);
		$passwort
			->setName($name)
			->setRequired(true)
			->setValidators(
			array(
				(new Zend_Validate_StringLength())->setMin(6)->setMax(50)->setMessage('Bitte zwischen 6-50 Zeichen verwenden'),
				(new Zend_Validate_Alnum())->setMessage('Bitte nur Buchstaben und Ziffern verwenden'),
				(new Zend_Validate_Identical())->setToken($passwortId)->setMessage('Die Passwörter stimmen nicht überein')
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
