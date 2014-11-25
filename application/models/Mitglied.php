<?php

/**
 * Modell fuer ein Naturefund-Mitglied.
 *
 * @author kenta.fried@gmail.com
 */
class Application_Model_Mitglied {
	private $_id;
	private $_anrede;
	private $_vorname;
	private $_nachname;
	private $_strasse;
	private $_plz;
	private $_ort;
	private $_land;
	private $_email;
	private $_telefon;
	private $_passwortHash;
	private $_verifizierungHash;
	private $_status;
	private $_datumErstellt;
	private $_datumGeandert;

	public function getId() {
		return $this->_id;
	}

	public function setId($value) {
		$this->_id = $value;
		return $this;
	}

	public function getAnrede() {
		return $this->_anrede;
	}

	public function setAnrede($value) {
		$this->_anrede = $value;
		return $this;
	}

	public function getVorname() {
		return $this->_vorname;
	}

	public function setVorname($value) {
		$this->_vorname = $value;
		return $this;
	}

	public function getNachname() {
		return $this->_nachname;
	}

	public function setNachname($value) {
		$this->_nachname = $value;
		return $this;
	}

	public function getStrasse() {
		return $this->_strasse;
	}

	public function setStrasse($value) {
		$this->_strasse = $value;
		return $this;
	}

	public function getPlz() {
		return $this->_plz;
	}

	public function setPlz($value) {
		$this->_plz = $value;
		return $this;
	}

	public function getOrt() {
		return $this->_ort;
	}

	public function setOrt($value) {
		$this->_ort = $value;
		return $this;
	}

	public function getLand() {
		return $this->_land;
	}

	public function setLand($value) {
		$this->_land = $value;
		return $this;
	}

	public function getEmail() {
		return $this->_email;
	}

	public function setEmail($value) {
		$this->_email = $value;
		return $this;
	}

	public function getTelefon() {
		return $this->_telefon;
	}

	public function setTelefon($value) {
		$this->_telefon = $value;
		return $this;
	}

	public function getPasswortHash() {
		return $this->_passwortHash;
	}

	public function setPasswortHash($value) {
		$this->_passwortHash = $value;
		return $this;
	}

	public function getVerifizierungHash() {
		return $this->_verifizierungHash;
	}

	public function setVerifizierungHash($value) {
		$this->_verifizierungHash = $value;
		return $this;
	}

	public function getStatus() {
		return $this->_status;
	}

	public function setStatus($value) {
		$this->_status = $value;
		return $this;
	}

	public function getDatumErstellt() {
		return $this->_datumErstellt;
	}

	public function setDatumErstellt($value) {
		$this->_datumErstellt = $value;
		return $this;
	}

	public function getDatumGeandert() {
		return $this->_datumGeandert;
	}

	public function setDatumGeandert($value) {
		$this->_datumGeandert = $value;
		return $this;
	}

	/**
	 * Konvertiert ein Mitglied-Objekt in ein Array. Diese Funktion beim Lesen und Schreiben auf
	 * die Datenbank verwendet. Die Array-Schluessel muessen mit den Feldnamen in der Datenbank
	 * uebereinstimmen.
	 *
	 * @return ein Array mit den Datenbank-Feldnamen als Schluessel und den Objektfeldern als
	 *         Wert
	 */
	public function toArray() {
		$arrayObj = array();
		if (null !== $this->getId()) {
			$arrayObj['id'] = $this->getId();
		}
		if (null !== $this->getVorname()) {
			$arrayObj['vorname'] = $this->getVorname();
		}
		if (null !== $this->getNachname()) {
			$arrayObj['nachname'] = $this->getNachname();
		}
		if (null !== $this->getEmail()) {
			$arrayObj['email'] = $this->getEmail();
		}
		if (null !== $this->getPasswortHash()) {
			$arrayObj['passwort_hash'] = $this->getPasswortHash();
		}
		if (null !== $this->getAnrede()) {
			$arrayObj['anrede'] = $this->getAnrede();
		}
		if (null !== $this->getStrasse()) {
			$arrayObj['strasse'] = $this->getStrasse();
		}
		if (null !== $this->getPlz()) {
			$arrayObj['plz'] = $this->getPlz();
		}
		if (null !== $this->getOrt()) {
			$arrayObj['ort'] = $this->getOrt();
		}
		if (null !== $this->getLand()) {
			$arrayObj['land'] = $this->getLand();
		}
		if (null !== $this->getTelefon()) {
			$arrayObj['telefon'] = $this->getTelefon();
		}
		if (null !== $this->getVerifizierungHash()) {
			$arrayObj['verifizierung_hash'] = $this->getVerifizierungHash();
		}
		if (null !== $this->getStatus()) {
			$arrayObj['status'] = $this->getStatus();
		}
		if (null !== $this->getDatumGeandert()) {
			$arrayObj['datum_geaendert'] = $this->getDatumGeandert();
		}
		if (null !== $this->getDatumErstellt()) {
			$arrayObj['datum_erstellt'] = $this->getDatumErstellt();
		}

		return $arrayObj;
	}

	/**
	 * Initialisiert ein Mitglied-Objekt durch eine assoziiertes Array wie es beim abfragen der
	 * Datenbank erzeugt wird.
	 *
	 * @param array $row
	 *        das Array welches eine Zeile der Mitglieder-Tabelle darstellt
	 */
	public function setFromRow($row) {
		$this->setId($row['id']);
		// $this->setAnrede($row['anrede']);
		$this->setVorname($row['vorname']);
		$this->setNachname($row['nachname']);
		$this->setStrasse($row['strasse']);
		$this->setOrt($row['ort']);
		$this->setPlz($row['plz']);
		$this->setLand($row['land']);
		$this->setEmail($row['email']);
		$this->setTelefon($row['telefon']);
		$this->setPasswortHash($row['passwort_hash']);
		$this->setVerifizierungHash($row['verifizierung_hash']);
		$this->setStatus($row['status']);
		$this->setDatumErstellt($row['datum_erstellt']);
		$this->setDatumGeandert($row['datum_geaendert']);
	}

	/**
	 * Initialisiert ein Mitglied-Objekt durch das gepostete Application_Form_Mitglied-Objekt.
	 * Achtung: Felder, die nicht in der Form vorhanden sind oder gehasht werden muessen, werden
	 * nicht initialisiert und muessen nachtraeglich gesetzt werden.
	 *
	 * @param Application_Form_Mitglied $form
	 */
	public function setFromForm($form) {
		// $this->setAnrede($form->getValue('anrede'));
		$this->setVorname($form->getValue('vorname'));
		$this->setNachname($form->getValue('nachname'));
		$this->setStrasse($form->getValue('strasse'));
		$this->setOrt($form->getValue('ort'));
		$this->setPlz($form->getValue('plz'));
		$this->setLand($form->getValue('land'));
		$this->setEmail($form->getValue('email'));
	}

	public function readNewMemberForm($form) {
		$this->setVorname($form->getValue('vorname'));
		$this->setNachname($form->getValue('nachname'));
		$this->setEmail($form->getValue('email'));
	}

	public function readUpdateMemberForm($form) {
		$this->setVorname($form->getValue('vorname'));
		$this->setNachname($form->getValue('nachname'));
		$this->setStrasse($form->getValue('strasse'));
		$this->setPlz($form->getValue('plz'));
		$this->setOrt($form->getValue('ort'));
		$this->setTelefon($form->getValue('telefon'));
	}
}
