<?php
class Zend_View_Helper_EingeloggtAls extends Zend_View_Helper_Abstract {

    public function eingeloggtAls() {
        $auth = Zend_Auth::getInstance();
        
        if ($auth->hasIdentity()) {
            $username = $auth->getIdentity();
            $logoutUrl = $this->view->url(array(
                    'controller' => 'auth',
                    'action' => 'logout'
            ), null, true);
            return 'Welcome ' . $username . '. Logout';
        }
        
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        if ($controller == 'auth' && $action == 'index') {
            return '';
        }
        $loginUrl = $this->view->url(array(
                'controller' => 'authentifizierung',
                'action' => 'index'
        ));
        return 'Login';
    }
}