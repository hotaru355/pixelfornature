<?php
class Pixel4natureController extends Zend_Controller_Action {
	private $session;
	private $log;
	private $dimensions;
	private $galeryPath;
	private $galeryFilesAbs;
	private $galeryFilesRel;

	public function init() {
		$this->log = Zend_Registry::get('Zend_Log');
		$this->view->addScriptPath(APPLICATION_PATH . "/views/partials");

		$this->session = new Zend_Session_Namespace('pixelfornature');
		$interactionMapper = new Application_Model_DbTable_Interaktion();
		if (!isset($this->session->project)) {
			$projectMapper = new Application_Model_DbTable_Projekt();
			$project = $projectMapper->getCurrent();
			$this->session->project = $project;
		}
		$this->session->project['pixelsTotal'] = $interactionMapper->getPixelsTotalByProject($this->session->project['id']);
		$this->session->project['lastDonors'] = $interactionMapper->getLastDonors($this->session->project['id']);
		$this->view->assign("session", $this->session);

		$ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('hochladen', 'json')->initContext();

		$auth = Zend_Auth::getInstance();
		$this->view->assign("isLoggedin", $auth->hasIdentity());

		if ($auth->hasIdentity()) {
			if (!isset($this->session->user)) {
				// load user!
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
			$resetPasswordForm = new Application_Form_NewMember("ResetPassword");
			$this->view->assign("resetPasswordForm", $resetPasswordForm);
		}

		$this->dimensions = $this->getInvokeArg('bootstrap')->getOption('m2spende')['dimensions'];
		$this->galeryPath = realpath(APPLICATION_PATH . "/../public/images/galerie");
		$this->galeryFilesAbs = glob($this->galeryPath . "/*");
		$this->galeryFilesRel = array_map(function ($file) {
			return str_replace(realpath(APPLICATION_PATH . "/../public/"), "", $file);
		}, $this->galeryFilesAbs);
		$this->dimensions['squarePixels'] = number_format(
			$this->dimensions["facebook"]["cover"]["width"] * $this->dimensions["facebook"]["cover"]["height"],
			0, ",", ".");
		$this->view->assign("squarePixels", $this->dimensions['squarePixels']);

		// var_dump($_SESSION['pixelfornature']['user']);
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
				$result = array(
					"errorCode" => null,
					"errorMsg" => null,
					"linkUrl" => $link,
				);
				$auth = Zend_Auth::getInstance();
				if ($auth->hasIdentity()) {
					$interactionMapper = new Application_Model_DbTable_Interaktion();
					$interactionMapper->createDonation($this->session->user['id'], $this->session->project['id'], $this->dimensions['squarePixels']);
				}
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

			$this->session->donatedPixels = $this->dimensions['squarePixels'];
			$this->_helper->json($result);
		}
	}

	public function dankeAction() {
		if (isset($this->session->donatedPixels)) {
			$this->view->assign("imagePath", $this->session->image['pathRel']);
		} else {
			// Es wurde nichts gespendet. Zurueck zur Auswahl.
			$this->_helper->redirector->gotoUrl('/');
		}
	}

// private function _userToArray($userObj) {
	//         $arrayObj = array(
	//             'vorname' => $this->getVorname(),
	//             'name' => $this->getName(),
	//             'email' => $this->getEmail(),
	//             'passwort_hash' => $this->getPasswortHash(),
	//         );

//         if (null !== $this->getAnrede()) {
	//             $arrayObj['anrede'] = $this->getAnrede();
	//         }
	//         if (null !== $this->getStrasse()) {
	//             $arrayObj['strasse'] = $this->getStrasse();
	//         }
	//         if (null !== $this->getPlz()) {
	//             $arrayObj['plz'] = $this->getPlz();
	//         }
	//         if (null !== $this->getOrt()) {
	//             $arrayObj['ort'] = $this->getOrt();
	//         }
	//         if (null !== $this->getLand()) {
	//             $arrayObj['land'] = $this->getLand();
	//         }
	//         if (null !== $this->getVerifizierungHash()) {
	//             $arrayObj['verifizierung_hash'] = $this->getVerifizierungHash();
	//         }
	//         if (null !== $this->getStatus()) {
	//             $arrayObj['status'] = $this->getStatus();
	//         }
	//         if (null !== $this->getDatumGeandert()) {
	//             $arrayObj['datum_geaendert'] = $this->getDatumGeandert();
	//         }
	//         if (null !== $this->getDatumErstellt()) {
	//             $arrayObj['datum_erstellt'] = $this->getDatumErstellt();
	//         }

//         return $arrayObj;
	//     }
}
