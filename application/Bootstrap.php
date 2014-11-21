<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
	protected function _initAutoload() {
		$this->getApplication()
		     ->getAutoloader();
		require_once 'facebook-php-sdk-v4-4.0-dev/autoload.php';
	}

	protected function _initPlaceholders() {
		$this->bootstrap('View');
		$view = $this->getResource('View');

		$doctypeHelper = new Zend_View_Helper_Doctype();
		$doctypeHelper->doctype('HTML5');

		// Titel der Seite
		$view->headTitle('Pixel 4 Nature')
		     ->setSeparator(' - ');

		// Globale CSS Datei(en)
		$view->headLink()
		     ->prependStylesheet('/css/site.css');

		// Globale JS Datei(en)
		//        $view->headScript()
		//            ->prependFile('/js/jquery/jquery.min.js');
	}

	protected function _initLogger() {
		$this->bootstrap("log");
		$log = $this->getResource("log");
		$log->registerErrorHandler();
		Zend_Registry::set('Zend_Log', $log);
	}

	protected function _initRoutes() {
		$router = Zend_Controller_Front::getInstance()->getRouter();
		include APPLICATION_PATH . "/configs/routes.php";
	}

}
