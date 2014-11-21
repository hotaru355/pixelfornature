<?php
$router->removeDefaultRoutes();

$indexRoute = new Zend_Controller_Router_Route_Static(
	'/',
	array(
		'controller' => 'pixel4nature',
		'action' => 'auswahl',
	)
);
$router->addRoute('auswahl', $indexRoute);

$ausschnittRoute = new Zend_Controller_Router_Route_Static(
	'ausschnitt',
	array(
		'controller' => 'pixel4nature',
		'action' => 'ausschnitt',
	)
);
$router->addRoute('ausschnitt', $ausschnittRoute);

$vorschauRoute = new Zend_Controller_Router_Route_Static(
	'vorschau',
	array(
		'controller' => 'pixel4nature',
		'action' => 'vorschau',
	)
);
$router->addRoute('vorschau', $vorschauRoute);

$hochladenRoute = new Zend_Controller_Router_Route_Static(
	'hochladen',
	array(
		'controller' => 'pixel4nature',
		'action' => 'hochladen',
	)
);
$router->addRoute('hochladen', $hochladenRoute);

$dankeRoute = new Zend_Controller_Router_Route_Static(
	'danke',
	array(
		'controller' => 'pixel4nature',
		'action' => 'danke',
	)
);
$router->addRoute('danke', $dankeRoute);

$validiereWoerterRoute = new Zend_Controller_Router_Route(
	'validierung/woerter',
	array(
		'controller' => 'validierung',
		'action' => 'validiere-woerter',
	)
);
$router->addRoute('validiereWoerter', $validiereWoerterRoute);
