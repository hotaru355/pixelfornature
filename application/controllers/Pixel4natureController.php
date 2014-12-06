<?php
class Pixel4natureController extends Zend_Controller_Action {
	private $session;
	private $log;
	private $dimensions;
	private $hasIdentity;

	public function init() {
		$this->log = Zend_Registry::get('Zend_Log');
		$this->view->addScriptPath(APPLICATION_PATH . "/views/partials");
		$ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('hochladen', 'json')->initContext();

		// get session
		$this->session = new Zend_Session_Namespace('pixelfornature');
		$this->view->assign("session", $this->session);

		// load project pixel count and donors on every request!
		$project = Zend_Registry::get('project');
		$interaktionMapper = new Application_Model_DbTable_Interaktion();
		$project['pixelsTotal'] = $interaktionMapper->getPixelsTotalByProject($project['id']);
		$project['pixelsTotalFormatted'] = number_format($project['pixelsTotal'], 0, ',', '.');
		$project['lastDonors'] = $interaktionMapper->getLastDonors($project['id']);
		Zend_Registry::set('project', $project);

		// assign auth
		$auth = Zend_Auth::getInstance();
		$this->hasIdentity = $auth->hasIdentity();
		$this->view->assign("isLoggedin", $this->hasIdentity);

		// assign view forms
		$updateMemberForm = new Application_Form_NewMember("Update");
		$this->view->assign("updateMemberForm", $updateMemberForm);
		$loginForm = new Application_Form_NewMember("Login");
		$this->view->assign("loginForm", $loginForm);
		$newMemberForm = new Application_Form_NewMember("Signup");
		$this->view->assign("newMemberForm", $newMemberForm);
		$requestResetForm = new Application_Form_NewMember("RequestReset");
		$this->view->assign("requestResetForm", $requestResetForm);

		if ($this->hasIdentity) {
			if (!isset($this->session->user)) {
				throw new Exception("No session user", 1);
			}
			$updateMemberForm->populate((array) $this->session->user);
		}

		//DEBUG
		// Zend_Session::namespaceUnset('pixelfornature');
		// var_dump($_SESSION['pixelfornature']);
		// var_dump($_SESSION['pixelfornature']['user']);
		// var_dump(Zend_Registry::get('dimensions'));
	}

	public function auswahlAction() {
		$this->view->assign("galeryFilesJs", Zend_Registry::get('galery')['arrayJs']);
		$this->view->assign("galeryFilesRel", Zend_Registry::get('galery')['filesRel']);
		if (isset($this->session->image) && isset($this->session->image['index'])) {
			$this->view->assign("imageIndex", $this->session->image['index']);
		} else {
			$this->view->assign("imageIndex", 0);
		}
	}

	public function ausschnittAction() {
		// create image object in session
		if (!isset($this->session->image)) {
			$this->session->image = array();
		}

		$dirtyIndex = intval($this->getParam("image"));
		$galery = Zend_Registry::get('galery');
		if (isset($galery['filesAbs'][$dirtyIndex])) {
			// save image properties in session
			$this->session->image['index'] = $dirtyIndex;
			$this->session->image['pathRel'] = $galery['filesRel'][$dirtyIndex];
			$this->session->image['pathAbs'] = $galery['filesAbs'][$dirtyIndex];
			$this->view->assign("dimensions", Zend_Registry::get('dimensions'));
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
				$dimensions = Zend_Registry::get('dimensions');
				$imageService = new ImageService();
				$outputPath = $imageService->generateCoverPhotoCL(
					$this->session->image['pathAbs'],
					$dimensions["original"],
					$dimensions["facebook"]["cover"],
					$imageProps);

				$this->session->image['generatedAbs'] = $outputPath;
				$this->session->image['generatedRel'] = str_replace(realpath(APPLICATION_PATH . "/../public/"), "", $outputPath);

				$this->view->assign("dimensions", $dimensions);
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
						"projekt_id" => Zend_Registry::get('project')['id'],
						"pixel_gespendet" => Zend_Registry::get('dimensions')['squarePixels']));

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
						"projekt_id" => Zend_Registry::get('project')["id"],
						"pixel_gespendet" => Zend_Registry::get('dimensions')["squarePixels"],
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
		$this->view->assign("squarePixels", number_format(Zend_Registry::get('dimensions')['squarePixels'], 0, ",", "."));
		$this->view->assign("imagePath", $this->session->image['pathRel']);
		// Es wurde nichts gespendet. Zurueck zur Auswahl.
		// $this->_helper->redirector->gotoUrl('/');
	}
}
