<?php
require_once 'password_compat/Auth.php';

class AuthentifizierungController extends Zend_Controller_Action
{

    public function init()
    {
        $form = new Application_Form_Login();
        $this->view->form = $form;
    }

    public function indexAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($this->view->form->isValid($request->getPost())) {
                if ($this->_authenticate()) {
                    $this->_helper->redirector('index', 'index');
                }
            }
        }
    }

    public function ausloggenAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        $this->_helper->redirector('index', 'authentifizierung');
    }

    protected function _authenticate()
    {
        $values = $this->view->form->getValues();
        $adapter = $this->_getAuthAdapter();
        $adapter->setIdentity($values ['email']);
        $adapter->setCredential($values ['passwort']);
        
        $auth = Zend_Auth::getInstance();
        $isValid = false;
        if ($auth->authenticate($adapter)
            ->isValid()) {
            $user = $adapter->getResultRowObject();
            // $auth->getStorage()
            // ->write($user);
            $isValid = true;
        }
        return $isValid;
    }

    protected function _getAuthAdapter()
    {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $authAdapter = new Compat_Auth_Adapter_DbTable($dbAdapter);
        
        $authAdapter->setTableName('mitglieder')
            ->setIdentityColumn('email')
            ->setCredentialColumn('passwort_hash')
            ->setStatusColumn('status')
            ->setStatusPassValue('aktiv');
        
        return $authAdapter;
    }

    public function bestaetigenAction()
    {
        $email = $this->getRequest()
            ->getParam('email');
        $hash = $this->getRequest()
            ->getParam('hash');
        
        if ($email && $hash) {
            $mitgliedMapper = new Application_Model_DbTable_Mitglied();
            $mitglieder = $mitgliedMapper->suchen(array('email' => $email, 'verifizierung_hash' => $hash));
            
            if (count($mitglieder) != 1) {
                $this->view->assign('erfolgreich', false);
            } else {
                $mitglied = reset($mitglieder);
                $mitglied->setStatus('aktiv');
                $mitgliedMapper->speichern($mitglied);
                $this->view->assign('erfolgreich', true);
            }
        } else {
            $this->_helper->redirector('index', 'index');
        }
    }
}





