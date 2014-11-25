<?php
class Application_Model_DbTable_Mitglied extends Zend_Db_Table_Abstract {
	protected $_name = 'mitglieder';

	private $nonSensitiveColums = array(
		'id',
		'vorname',
		'nachname',
		'strasse',
		'plz',
		'ort',
		'email',
		'telefon');

	public function save($mitglied) {
		$data = null;
		if ($mitglied instanceof Application_Model_Mitglied) {
			$data = $mitglied->toArray();
		} else if (is_array($mitglied)) {
			$data = $mitglied;
		} else {
			throw new Exception("Illegal argument", 1);
		}

		if (isset($data['id']) && $data['id'] != null) {
			$id = $data['id'];
			unset($data['id']);
			$this->update($data, array(
				'id = ?' => $id,
			));
			return $id;
		} else {
			unset($data['id']);
			return $this->insert($data);
		}
	}

	public function findById($id, $columns = null) {
		$select = $this
			->select()
			->from($this, ($columns == null) ? $this->nonSensitiveColums : $columns)
			->where('id = ?', $id);

		$result = $this->fetchRow($select);
		return $result->toArray();
	}

	// public function sucheId($id, $columns = null) {
	// 	$columns = ($columns == null) ? $this->nonSensitiveColums : $columns;
	// 	$result = $this->find($id);
	// 	$mitglied = null;
	// 	if (0 < count($result)) {
	// 		$row = $result->current();
	// 		$mitglied = new Application_Model_Mitglied();
	// 		$mitglied->setFromRow($row);
	// 	}
	// 	return $mitglied;
	// }

	public function suchen($feldUndWert) {
		$select = $this->select()->columns($columns);
		foreach ($feldUndWert as $feld => $wert) {
			$select->where($feld . ' = ?', $wert);
		}
		$resultSet = $this->getAdapter()
		                  ->query($select)
		                  ->fetchAll();
		$mitglieder = array();
		foreach ($resultSet as $row) {
			$mitglied = new Application_Model_Mitglied();
			$mitglied->setFromRow($row);
			$mitglieder[$mitglied->getId()] = $mitglied;
		}
		return $mitglieder;
	}

}
