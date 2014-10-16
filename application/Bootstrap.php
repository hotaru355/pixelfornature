<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    protected function _initPlaceholders()
    {
        $this->bootstrap('View');
        $view = $this->getResource('View');
        
        // Titel der Seite
        $view->headTitle('Naturefund.de')
            ->setSeparator(' :: ');
        
        // Globale CSS Datei(en)
        $view->headLink()
            ->prependStylesheet('/css/site.css');
        
        // Globale JS Datei(en)
//        $view->headScript()
//            ->prependFile('/js/jquery/jquery.min.js');
    }

    protected function _initLogger()
    {
        $this->bootstrap("log");
        $log = $this->getResource("log");
        $log->registerErrorHandler();
        Zend_Registry::set('Zend_Log', $log);
    }
    
}

