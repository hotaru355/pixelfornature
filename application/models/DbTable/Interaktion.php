<?php
class Application_Model_DbTable_Interaktion extends Zend_Db_Table_Abstract {
	protected $_name = 'interaktionen';

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

	public function getTimeline($mitgliedId) {
		$select = $this
			->select()
			->from(array('i' => 'interaktionen'), array('datum_erstellt', 'type', 'pixel_gespendet'))
			->join(array('p' => 'projekte'), 'i.projekt_id = p.id', array('timeline_name'))
			->where('i.mitglied_id = ?', $mitgliedId)
			->order('i.datum_erstellt DESC')
			->setIntegrityCheck(false);

		$result = $this->fetchAll($select);
		// TODO: do this project wide the zend way
		date_default_timezone_set('Europe/Berlin');
		setlocale(LC_ALL, "de_DE", "de_DE@euro", "deu", "deu_deu", "german");

		foreach ($result as $row) {
			$row['datum_erstellt'] = strftime('%d. %B %Y', strtotime($row['datum_erstellt']));
		}
		return $result->toArray();
	}

	public function getPixelsTotalByMember($mitgliedId) {
		$select = $this
			->select()
			->from($this, new Zend_Db_Expr("SUM(pixel_gespendet) as total"))
			->where('mitglied_id = ?', $mitgliedId);

		$result = $this->fetchRow($select);
		return $result->total;
	}

	public function getPixelsTotalByProject($projectId) {
		$select = $this
			->select()
			->from($this, new Zend_Db_Expr("SUM(pixel_gespendet) as total"))
			->where('projekt_id = ?', $projectId);

		$result = $this->fetchRow($select);
		return $result->total;
	}

	public function getLastDonors($projectId, $limit = 3) {
		$select = $this
			->select()
			->from(array('i' => 'interaktionen'), array())
			->join(array('m' => 'mitglieder'), 'i.mitglied_id = m.id', array('facebook_id', 'vorname', 'nachname', 'ort'))
			->where('i.projekt_id = ?', $projectId)
			->where('i.type = ?', 'pixelspende')
			->order('i.datum_erstellt DESC')
			->limit($limit)
			->setIntegrityCheck(false);

		$result = $this->fetchAll($select);
		return $result->toArray();
	}

	public function createSignup($memberId, $projectId) {
		$data = array(
			'mitglied_id' => $memberId,
			'projekt_id' => $projectId,
			'type' => 'signup');
		return $this->insert($data);
	}

	public function createDonation($donation) {
		$donation["type"] = "pixelspende";
		return $this->insert($donation);
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
