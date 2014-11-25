<?php
class Application_Model_DbTable_Projekt extends Zend_Db_Table_Abstract {
	protected $_name = 'projekte';

	public function save($data) {
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
		$columns = ($columns == null) ? $this->nonSensitiveColums : $columns;
		$result = $this->fetchRow($this->select($this->_name)->where('id = ?', $id)->columns($columns));
		return $result->toArray();
	}

	public function getCurrent() {
		$select = $this
			->select()
			->where('status = ?', 'aktiv')
			->order('datum_erstellt DESC');

		$result = $this->fetchRow($select);
		return $result->toArray();
	}

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
