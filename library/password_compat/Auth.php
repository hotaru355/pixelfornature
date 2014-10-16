<?php

/**
 * This class extends the Zend_Auth_Adapter_DbTable to use the Elkblowfish encryption algorithm, the
 * strongest algorithm available to PHP at the current time. The implementation of the algorithm is
 * provided by PHP version > 5.5 and by a backport for PHP 5.3.7 >= version < 5.5.
 *
 * @author kenta.fried@gmail.com
 * @link http://us2.php.net/password_verify password_verify()
 * @link https://github.com/ircmaxell/password_compat backport
 */
class Compat_Auth_Adapter_DbTable extends Zend_Auth_Adapter_DbTable {
    private $_statusColumn;
    private $_statusPassValue;

    public function setStatusColumn($additionalColumn) {
        $this->_statusColumn = $additionalColumn;
        return $this;
    }

    public function setStatusPassValue($additionalPassValue) {
        $this->_statusPassValue = $additionalPassValue;
        return $this;
    }

    /**
     * Creates a Zend_Db_Select object that is configured to retrieve a record matching the identity
     * column only. The credential column and credential treatment are *not* used in the where
     * clause.
     *
     * @see Zend_Auth_Adapter_DbTable::_authenticateCreateSelect()
     */
    protected function _authenticateCreateSelect() {
        $dbSelect = clone $this->getDbSelect();
        $dbSelect->from($this->_tableName)
            ->where($this->_zendDb->quoteIdentifier($this->_identityColumn, true) . ' = ?', $this->_identity);
        
        return $dbSelect;
    }

    /**
     * Verifies resultIdentity by calling password_verify() from the password.php backport or PHP
     * >5.5 std lib.
     *
     * @see Zend_Auth_Adapter_DbTable::_authenticateValidateResult()
     */
    protected function _authenticateValidateResult($resultIdentity) {
        if (version_compare(PHP_VERSION, '5.3.7', '<')) {
            throw new Exception(
                    'The PHP version does not provide an implementation of the Elkblowfish encryption algorithm.');
        }
        
        require_once 'password_compat/password.php';
        
        // if the credentials verify
        if (password_verify($this->_credential, $resultIdentity [$this->_credentialColumn])) {
            // if status option is set, test it, otherwise pass
            if ($this->_statusColumn && $this->_statusPassValue) {
                if ($resultIdentity [$this->_statusColumn] != $this->_statusPassValue) {
                    $this->_authenticateResultInfo ['code'] = Zend_Auth_Result::FAILURE_UNCATEGORIZED;
                    $this->_authenticateResultInfo ['messages'] [] = 'The account has not been activated yet.';
                    return $this->_authenticateCreateAuthResult();
                }
            }
            $this->_authenticateResultInfo ['code'] = Zend_Auth_Result::SUCCESS;
            $this->_authenticateResultInfo ['messages'] [] = 'Authentication successful.';
            $this->_resultRow = $resultIdentity;
            return $this->_authenticateCreateAuthResult();
        } else {
            $this->_authenticateResultInfo ['code'] = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
            $this->_authenticateResultInfo ['messages'] [] = 'Supplied credential is invalid.';
            return $this->_authenticateCreateAuthResult();
        }
    }

    /**
     * Hiding this method as the credential treatment is fixed and cannot be changed.
     *
     * @param unknown $treatment        
     */
    public function setCredentialTreatment($treatment) {
        throw new Exception(
                'The credential treatment is fixed to the Elkblowfish encryption algorithm and cannot be changed.');
    }
}