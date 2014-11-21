<?php
class FlaecheFuerNaturController extends Zend_Controller_Action {
	private $session;
	private $log;
	private $dimensions;
	private $galeryFiles;
	private $galeryPath;

	public function init() {
		$this->session = new Zend_Session_Namespace('pixelfornature');

		$this->log = Zend_Registry::get('Zend_Log');
		$this->view->assign("params", $this->getRequest()
				->getParams());
		$this->view->addScriptPath(APPLICATION_PATH . "/views/partials");
		$this->dimensions = $this->getInvokeArg('bootstrap')
		->getOption('m2spende')['dimensions'];
		$this->galeryPath = realpath(APPLICATION_PATH . "/../public/images/galerie");
		$this->galeryFiles = glob($this->galeryPath . "/*");
		$squarePixels = number_format($this->dimensions["facebook"]["cover"]["width"] * $this->dimensions["facebook"]["cover"]["height"],
			0, ",", ".");
		$this->view->assign("squarePixels", $squarePixels);

		$neuesMitgliedForm = new Application_Form_Mitglied();
		$this->view->assign("neuesMitgliedForm", $neuesMitgliedForm);
	}

	public function indexAction() {
	}

	public function auswahlAction() {
		// Lade jedes Bild aus dem Galerieverzeichnis in das Karusell
		$galeryFilesJs = array_map(function ($file) {
			return ("'" . str_replace(realpath(APPLICATION_PATH . "/../public/"), "", $file) . "'");
		}, $this->galeryFiles);
		$galeryFilesJs = "[" . implode(",", $galeryFilesJs) . "]";

		$this->view->assign("step", 1);
		$this->view->assign("galeryFilesJs", $galeryFilesJs);
	}

	public function ausschnittAction() {
		$imageIdx = $this->getParam("image");
		if (file_exists($this->galeryFiles[$imageIdx])) {
			$this->view->assign("step", 2);
			$this->view->assign("dimensions", $this->dimensions);
			$this->view->assign("imagePath", str_replace(realpath(APPLICATION_PATH . "/../public/"), "", $this->galeryFiles[$imageIdx]));
		} else {
			$this->_helper->redirector('auswahl', 'flaeche-fuer-natur');
		}
	}

	public function vorschauAction() {
		$imageIdx = $this->getParam("image");
		$selectedGaleryPath = $this->galeryFiles[$imageIdx];

		if (file_exists($selectedGaleryPath)) {
			$this->view->assign("step", 3);
			$this->view->assign("dimensions", $this->dimensions);
			$this->view->assign("imagePath", str_replace(realpath(APPLICATION_PATH . "/../public/"), "", $selectedGaleryPath));

			Zend_Loader::loadFile("ValidationService.php");
			Zend_Loader::loadFile("ImageService.php");

			try {
				$validationService = new ValidationService();
				$imageProps = $validationService->getCleanParams($this->getRequest()->getParams());

				// Generiere Bild
				$imageService = new ImageService();
				$outputPath = $imageService->generateCoverPhotoCL(
					$selectedGaleryPath,
					$this->dimensions["original"],
					$this->dimensions["facebook"]["cover"],
					$imageProps);
				if (isset($this->session->generatedPath)) {
				}
				$this->session->generatedPath = $outputPath;
				$this->view->assign("generatedPath", str_replace(realpath(APPLICATION_PATH . "/../public/"), "", $outputPath));
			} catch (Exception $e) {
				$this->log->err(sprintf("Error code: %d\n%s", $e->getCode(), $e->getMessage()));
				$result = array(
					"errorCode" => $e->getCode(),
					"errorMsg" => $e->getMessage(),
					"link" => null
				);
			}
		} else {
			$this->_helper->redirector('auswahl', 'flaeche-fuer-natur');
		}
	}

	public function hochladenAction() {
		print_r($this->session->generatedPath);

		if ($this->_request->isXmlHttpRequest() && $this->session->generatedPath) {
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->disableLayout();

			Zend_Loader::loadFile("ValidationService.php");
			Zend_Loader::loadFile("FacebookService.php");

			try {
				$facebookService = new FacebookService();
				$link = $facebookService->uploadPhoto($this->session->generatedPath);
				$result = array(
					"errorCode" => null,
					"errorMsg" => null,
					"linkUrl" => $link,
				);
			} catch (Facebook\FacebookRequestException $ex) {
				// When Facebook returns an error
				$this->log->err(sprintf("Error code: %d\n%s", $ex->getCode(), $ex->getMessage()));
				$result = array(
					"errorCode" => $ex->getCode(),
					"errorMsg" => $ex->getMessage(),
					"linkUrl" => null
				);
			} catch (\Exception $ex) {
				// When validation fails or other local issues
				$this->log->err($ex);
				$result = array(
					"errorCode" => $ex->getCode(),
					"errorMsg" => $ex->getMessage(),
					"linkUrl" => null
				);
			}
			echo Zend_Json::encode($result);
		}
	}

}
