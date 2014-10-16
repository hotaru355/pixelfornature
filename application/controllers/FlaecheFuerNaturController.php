<?php
class FlaecheFuerNaturController extends Zend_Controller_Action
{
    private $log;
    private $dimensions;
    const FILE_NOT_FOUND_ERROR = 10000;
    const INVALID_PARAM_ERROR = 10001;

    public function init()
    {
        $this->log = Zend_Registry::get('Zend_Log');
        $this->view->assign("params", $this->getRequest()
            ->getParams());
        $this->view->addScriptPath(APPLICATION_PATH . "/views/partials");
        $this->dimensions = $this->getInvokeArg('bootstrap')
            ->getOption('m2spende')['dimensions'];
    }

    public function indexAction()
    {
    }

    public function auswahlAction()
    {
        $this->view->params ["currentStep"] = 1;
        
        // Lade jedes Bild aus dem Galerieverzeichnis in das Karusell
        $galeryPath = realpath(APPLICATION_PATH . "/../public/images/galerie");
        $galeryFiles = scandir($galeryPath);
        $this->view->assign("galeryFiles", $galeryFiles);
    }

    public function ausschnittAction()
    {
        $bild = $this->getParam("bild");
        $bildPath = realpath(APPLICATION_PATH . "/../public/images/galerie/" . $bild);
        if (file_exists($bildPath)) {
            $this->view->params ["currentStep"] = 2;
            $this->view->assign("dimensions", $this->dimensions);
        } else {
            $this->_helper->redirector('auswahl', 'flaeche-fuer-natur');
        }
    }

    public function vorschauAction()
    {
        $bild = $this->getParam("bild");
        $bildPath = realpath(APPLICATION_PATH . "/../public/images/galerie/" . $bild);
        if (file_exists($bildPath)) {
            $this->view->params ["currentStep"] = 3;
            $this->view->assign("dimensions", $this->dimensions);
        } else {
            $this->_helper->redirector('auswahl', 'flaeche-fuer-natur');
        }
    }

    public function hochladenAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $this->_helper->viewRenderer->setNoRender();
            $this->_helper->layout->disableLayout();
            
            Zend_Loader::loadFile("ValidationService.php");
            Zend_Loader::loadFile("ImageService.php");
            Zend_Loader::loadFile("FacebookService.php");
            
            try {
                $validationService = new ValidationService();
                $imageProps = $validationService->getCleanParams($this->getParam("imageProps"));
                // $imageProps = $this->getCleanParams($this->getRequest()
                // ->getParams());
                
                // Generiere Bild
                $imageService = new ImageService();
                $outputPath = $imageService->generateCoverPhotoCL($this->dimensions ["original"], 
                        $this->dimensions ["facebook"] ["cover"], $imageProps);
                
                // Lade hoch zu Facebook
                $accessData = array(
                        "accessToken" => $this->getParam("accessToken"),
                        "signedRequest" => $this->getParam("signedRequest"),
                        "userID" => $this->getParam("userID")
                );
                $facebookService = new FacebookService();
                $link = $facebookService->uploadPhoto($accessData, $outputPath);
                $result = array(
                        "errorCode" => null,
                        "errorMsg" => null,
                        "link" => $link
                );
            } catch (Exception $e) {
                $this->log->err(sprintf("Error code: %d\n%s", $e->getCode(), $e->getMessage()));
                $result = array(
                        "errorCode" => $e->getCode(),
                        "errorMsg" => $e->getMessage(),
                        "link" => null
                );
            }
            echo Zend_Json::encode($result);
        }
    }


}









