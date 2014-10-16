<?php
class Application_Model_DbTable_Mitglied extends Zend_Db_Table_Abstract
{
    protected $_name = 'mitglieder';

    public function speichern(Application_Model_Mitglied $mitglied)
    {
        $data = $mitglied->toArray();
        
        if (null === $mitglied->getId()) {
            unset($data ['id']);
            $this->insert($data);
        } else {
            $this->update($data, array(
                    'id = ?' => $mitglied->getId()
            ));
        }
    }

    public function sucheId($id)
    {
        $result = $this->find($id);
        $mitglied = null;
        if (0 < count($result)) {
            $row = $result->current();
            $mitglied = new Application_Model_Mitglied();
            $mitglied->setFromRow($row);
        }
        return $mitglied;
    }

    public function suchen($feldUndWert)
    {
        $select = $this->select();
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
            $mitglieder [$mitglied->getId()] = $mitglied;
        }
        return $mitglieder;
    }
    
}
