<?php
class ValidierungController extends Zend_Controller_Action {
	private $log;

	public function init() {
		$this->log = Zend_Registry::get('Zend_Log');
	}

	public function indexAction() {
	}

	public function validiereWoerterAction() {
		if ($this->_request->isXmlHttpRequest()) {
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->disableLayout();

			Zend_Loader::loadFile("ValidationService.php");
			$validationService = new ValidationService();
			$names = explode(" ", $this->getParam("woerter"));
			$offendingWords = $validationService->findOffendingWords($names, $this->getParam("methode"));
			echo Zend_Json::encode($offendingWords);
		}
	}
}
