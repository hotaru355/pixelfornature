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

$newMemberRoute = new Zend_Controller_Router_Route(
	'mitglieder/neu',
	array(
		'controller' => 'mitglied',
		'action' => 'hinzufuegen',
	)
);
$router->addRoute('mitgliederNeu', $newMemberRoute);

$updateMemberRoute = new Zend_Controller_Router_Route(
	'mitglieder/aendern',
	array(
		'controller' => 'mitglied',
		'action' => 'aendern',
	)
);
$router->addRoute('mitgliederAendern', $updateMemberRoute);

$deleteMemberRoute = new Zend_Controller_Router_Route(
	'mitglieder/loeschen',
	array(
		'controller' => 'mitglied',
		'action' => 'loeschen',
	)
);
$router->addRoute('mitgliederLoeschen', $deleteMemberRoute);

$resetPasswordRoute = new Zend_Controller_Router_Route(
	'mitglieder/passwort-zuruecksetzen',
	array(
		'controller' => 'mitglied',
		'action' => 'passwort-zuruecksetzen',
	)
);
$router->addRoute('mitgliederPasswortZuruecksetzen', $resetPasswordRoute);

$authLoginRoute = new Zend_Controller_Router_Route(
	'auth/login',
	array(
		'controller' => 'authentifizierung',
		'action' => 'login',
	)
);
$router->addRoute('authLogin', $authLoginRoute);

$authLogoutRoute = new Zend_Controller_Router_Route(
	'auth/logout',
	array(
		'controller' => 'authentifizierung',
		'action' => 'logout',
	)
);
$router->addRoute('authLogout', $authLogoutRoute);

$requestResetRoute = new Zend_Controller_Router_Route(
	'auth/request-reset',
	array(
		'controller' => 'authentifizierung',
		'action' => 'request-reset',
	)
);
$router->addRoute('mitgliederPasswortBestaetigen', $requestResetRoute);
