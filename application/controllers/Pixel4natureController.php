<?php
class Pixel4natureController extends Zend_Controller_Action {
	private $session;
	private $log;
	private $dimensions;
	private $galeryPath;
	private $galeryFilesAbs;
	private $galeryFilesRel;

	public function init() {
		$this->session = new Zend_Session_Namespace('pixelfornature');
		$this->log = Zend_Registry::get('Zend_Log');
		// $this->view->assign("params", $this->getRequest()->getParams());
		$this->view->addScriptPath(APPLICATION_PATH . "/views/partials");

		$this->dimensions = $this->getInvokeArg('bootstrap')->getOption('m2spende')['dimensions'];

		$this->galeryPath = realpath(APPLICATION_PATH . "/../public/images/galerie");
		$this->galeryFilesAbs = glob($this->galeryPath . "/*");
		$this->galeryFilesRel = array_map(function ($file) {
			return str_replace(realpath(APPLICATION_PATH . "/../public/"), "", $file);
		}, $this->galeryFilesAbs);

		$neuesMitgliedForm = new Application_Form_Mitglied();
		$this->view->assign("neuesMitgliedForm", $neuesMitgliedForm);

		$ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('hochladen', 'json')->initContext();
	}

	public function auswahlAction() {
		$galeryFilesJs = array_map(function ($file) {
			return "'{$file}'";
		}, $this->galeryFilesRel);
		$galeryFilesJs = "[" . implode(",", $galeryFilesJs) . "]";

		$this->view->assign("step", 1);
		$this->view->assign("galeryFilesJs", $galeryFilesJs);
		$this->view->assign("justUploaded", $this->session->justUploaded);
		if (isset($this->session->image) && isset($this->session->image['index'])) {
			$this->view->assign("imageIndex", $this->session->image['index']);
		} else {
			$this->view->assign("imageIndex", 0);
		}
	}

	public function ausschnittAction() {
		if (!isset($this->session->image)) {
			$this->session->image = array();
		}
		$dirtyIndex = intval($this->getParam("image"));
		if (isset($this->galeryFilesAbs[$dirtyIndex])) {
			$this->session->image['index'] = $dirtyIndex;
			$this->session->image['pathRel'] = $this->galeryFilesRel[$dirtyIndex];
			$this->session->image['pathAbs'] = $this->galeryFilesAbs[$dirtyIndex];
			$this->view->assign("step", 2);
			$this->view->assign("dimensions", $this->dimensions);
			$this->view->assign("imagePath", $this->session->image['pathRel']);
		} else {
			$this->_helper->redirector->gotoUrl('/');
		}
	}

	public function vorschauAction() {
		if (!isset($this->session->image['pathRel'])) {
			// Es wurde noch kein Bild ausgesucht. Zurueck zur Auswahl.
			$this->_helper->redirector->gotoUrl('/');
		}

		Zend_Loader::loadFile("ValidationService.php");
		try {
			// Validiere gepostete Daten
			$validationService = new ValidationService();
			$imageProps = $validationService->getCleanParams($this->getRequest()->getParams());

			Zend_Loader::loadFile("ImageService.php");
			try {
				// Generiere Bild
				$imageService = new ImageService();
				$outputPath = $imageService->generateCoverPhotoCL(
					$this->session->image['pathAbs'],
					$this->dimensions["original"],
					$this->dimensions["facebook"]["cover"],
					$imageProps);

				$this->session->image['generatedAbs'] = $outputPath;
				$this->session->image['generatedRel'] = str_replace(realpath(APPLICATION_PATH . "/../public/"), "", $outputPath);

				$this->view->assign("step", 3);
				$this->view->assign("dimensions", $this->dimensions);
				$this->view->assign("imagePath", $this->session->image['pathRel']);
				$this->view->assign("generatedPath", $this->session->image['generatedRel']);
			} catch (\Exception $ex) {
				// Irgendwas ist waehrend der Bildgenerierung schief gegangen ...
				$this->log->err("Error while generating image: {$ex->getCode()}\n{$ex->getMessage()}");
			}
		} catch (\Exception $ex) {
			// wahrscheinlich hat jemand an den geposteten Daten rumgespielt. Einfach zurueck auf Start schicken.
			$this->log->err("Error validating posted params: {$ex->getCode()}\n{$ex->getMessage()}");
			$this->_helper->redirector->gotoUrl('/');
		}
	}

	public function hochladenAction() {

		if ($this->_request->isXmlHttpRequest() && isset($this->session->image['generatedRel'])) {
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->disableLayout();

			Zend_Loader::loadFile("ValidationService.php");
			Zend_Loader::loadFile("FacebookService.php");

			try {
				$facebookService = new FacebookService();
				$link = $facebookService->uploadPhoto($this->session->image['generatedRel']);
				$result = array(
					"errorCode" => null,
					"errorMsg" => null,
					"linkUrl" => $link,
				);
			} catch (Facebook\FacebookRequestException $ex) {
				// When Facebook returns an error
				$this->log->err("Facebook Error: {$ex->getCode()}\n{$ex->getMessage()}");
				$result = array(
					"errorCode" => $ex->getCode(),
					"errorMsg" => $ex->getMessage(),
					"linkUrl" => null
				);
			} catch (\Exception $ex) {
				// When validation fails or other local issues
				$this->log->err("Error while uploading to Facebook: {$ex->getCode()}\n{$ex->getMessage()}");
				$result = array(
					"errorCode" => $ex->getCode(),
					"errorMsg" => $ex->getMessage(),
					"linkUrl" => null
				);
			}

			$this->session->donatedPixels = number_format(
				$this->dimensions["facebook"]["cover"]["width"] * $this->dimensions["facebook"]["cover"]["height"],
				0, ",", ".");
			$this->_helper->json($result);
		}
	}

	public function dankeAction() {
		if (isset($this->session->donatedPixels)) {
			$this->view->assign("imagePath", $this->session->image['pathRel']);
			$this->view->assign("squarePixels", $this->session->donatedPixels);
		} else {
			// Es wurde nichts gespendet. Zurueck zur Auswahl.
			$this->_helper->redirector->gotoUrl('/');
		}
	}

}
