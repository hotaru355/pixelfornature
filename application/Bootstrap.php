<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
	protected function _initAutoload() {
		require_once 'facebook-php-sdk-v4-4.0-dev/autoload.php';

		// bootstrap DB resource
		$this->bootstrap('db');

		// load project into registry
		$projectMapper = new Application_Model_DbTable_Projekt();
		$project = $projectMapper->getCurrent();
		Zend_Registry::set('project', $project);

		// load galery file paths into registry
		$galeryPath = realpath(APPLICATION_PATH . "/../public/images/galerie");
		$galeryFilesAbs = glob($galeryPath . "/*");
		$galeryFilesRel = array_map(function ($file) {
			return str_replace(realpath(APPLICATION_PATH . "/../public/"), "", $file);
		}, $galeryFilesAbs);
		$galeryFilesJs = array_map(function ($file) {
			return "'{$file}'";
		}, $galeryFilesRel);
		$galeryFilesJs = "[" . implode(",", $galeryFilesJs) . "]";
		Zend_Registry::set('galery', array(
			'path' => $galeryPath,
			'filesAbs' => $galeryFilesAbs,
			'filesRel' => $galeryFilesRel,
			'arrayJs' => $galeryFilesJs));

		// load dimensions from application.ini into registry
		$dimensions = $this->getOption('pixelfornature')['dimensions'];
		$dimensions['squarePixels'] = $dimensions["facebook"]["cover"]["width"] * $dimensions["facebook"]["cover"]["height"];

		Zend_Registry::set('dimensions', $dimensions);
	}

	protected function _initPlaceholders() {
		$this->bootstrap('View');
		$view = $this->getResource('View');

		$doctypeHelper = new Zend_View_Helper_Doctype();
		$doctypeHelper->doctype('HTML5');

		// Titel der Seite
		$view->headTitle('Pixel for nature')
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
