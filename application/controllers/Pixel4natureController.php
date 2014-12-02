<?php
class Pixel4natureController extends Zend_Controller_Action {
	private $session;
	private $log;
	private $dimensions;
	private $galeryPath;
	private $galeryFilesAbs;
	private $galeryFilesRel;
	private $hasIdentity;

	public function init() {
		$this->log = Zend_Registry::get('Zend_Log');
		$this->view->addScriptPath(APPLICATION_PATH . "/views/partials");
		$ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('hochladen', 'json')->initContext();

		// get session and init project
		$this->session = new Zend_Session_Namespace('pixelfornature');
		if (!isset($this->session->project)) {
			$projectMapper = new Application_Model_DbTable_Projekt();
			$this->session->project = $projectMapper->getCurrent();
		}
		$this->view->assign("session", $this->session);

		// load project pixel count and donors on every request!
		$interaktionMapper = new Application_Model_DbTable_Interaktion();
		$this->session->project['pixelsTotal'] = $interaktionMapper->getPixelsTotalByProject($this->session->project['id']);
		$this->session->project['lastDonors'] = $interaktionMapper->getLastDonors($this->session->project['id']);

		// assign auth
		$auth = Zend_Auth::getInstance();
		$this->hasIdentity = $auth->hasIdentity();
		$this->view->assign("isLoggedin", $this->hasIdentity);

		// assign view forms
		if ($this->hasIdentity) {
			if (!isset($this->session->user)) {
				throw new Exception("No session user", 1);
			}
			$updateMemberForm = new Application_Form_NewMember("Update");
			$updateMemberForm->populate((array) $this->session->user);
			$this->view->assign("updateMemberForm", $updateMemberForm);
		} else {
			$loginForm = new Application_Form_NewMember("Login");
			$this->view->assign("loginForm", $loginForm);
			$newMemberForm = new Application_Form_NewMember("Signup");
			$this->view->assign("newMemberForm", $newMemberForm);
			$requestResetForm = new Application_Form_NewMember("RequestReset");
			$this->view->assign("requestResetForm", $requestResetForm);
		}

		// get variables
		$this->dimensions = $this->getInvokeArg('bootstrap')->getOption('m2spende')['dimensions'];
		$this->dimensions['squarePixels'] = $this->dimensions["facebook"]["cover"]["width"] * $this->dimensions["facebook"]["cover"]["height"];
		// this would be faster when hard-coded ...
		$this->galeryPath = realpath(APPLICATION_PATH . "/../public/images/galerie");
		$this->galeryFilesAbs = glob($this->galeryPath . "/*");
		$this->galeryFilesRel = array_map(function ($file) {
			return str_replace(realpath(APPLICATION_PATH . "/../public/"), "", $file);
		}, $this->galeryFilesAbs);

		//DEBUG
		// Zend_Session::namespaceUnset('pixelfornature');
		// var_dump($_SESSION['pixelfornature']);
		// var_dump($_SESSION['pixelfornature']['pendingData']);
	}

	public function auswahlAction() {
		$galeryFilesJs = array_map(function ($file) {
			return "'{$file}'";
		}, $this->galeryFilesRel);
		$galeryFilesJs = "[" . implode(",", $galeryFilesJs) . "]";

		$this->view->assign("step", 1);
		$this->view->assign("galeryFilesJs", $galeryFilesJs);
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
				$link = $facebookService->uploadPhoto($this->session->image['generatedAbs']);
				$fbId = $facebookService->getUser()->getProperty("id");

				//DEBUG
				// $link = 'done';
				// $fbId = 4;

				if ($this->hasIdentity) {
					// save facebook id to user
					$mitgliedMapper = new Application_Model_DbTable_Mitglied();
					$mitgliedMapper->save(array(
						"id" => $this->session->user["id"],
						"facebook_id" => $fbId,
					));

					// save donation interaction
					$interaktionMapper = new Application_Model_DbTable_Interaktion();
					$interaktionMapper->createDonation(array(
						"mitglied_id" => $this->session->user['id'],
						"projekt_id" => $this->session->project['id'],
						"pixel_gespendet" => $this->dimensions['squarePixels']));

					// reload pixel count and timeline into session
					$this->session->user['pixelsTotal'] = $interaktionMapper->getPixelsTotalByMember($this->session->user['id']);
					$this->session->user['timeline'] = $interaktionMapper->getTimeline($this->session->user['id']);
				} else {
					if (!$this->session->pendingData) {
						$this->session->pendingData = (object) array(
							'fbId' => null,
							'donations' => array());
					}
					// save facebook id in pending data
					$this->session->pendingData->fbId = $fbId;

					// save donation interaction in pending data
					array_push($this->session->pendingData->donations, array(
						"projekt_id" => $this->session->project["id"],
						"pixel_gespendet" => $this->dimensions["squarePixels"],
						"datum_erstellt" => date('Y-m-d H:i:s'),
					));
				}

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
					"linkUrl" => null,
				);
			} catch (\Exception $ex) {
				// When validation fails or other local issues
				$this->log->err("Error while uploading to Facebook: {$ex->getCode()}\n{$ex->getMessage()}");
				$result = array(
					"errorCode" => $ex->getCode(),
					"errorMsg" => $ex->getMessage(),
					"linkUrl" => null,
				);
			}

			$this->_helper->json($result);
		}
	}

	public function dankeAction() {
		$this->view->assign("squarePixels", number_format($this->dimensions['squarePixels'], 0, ",", "."));
		$this->view->assign("imagePath", $this->session->image['pathRel']);
		// Es wurde nichts gespendet. Zurueck zur Auswahl.
		// $this->_helper->redirector->gotoUrl('/');
	}
}
