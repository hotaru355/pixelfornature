<?php
class ValidationService {
	private $log;
	const FILE_NOT_FOUND_ERROR = 10000;
	const INVALID_PARAM_ERROR = 10001;

	public function __construct() {
		$this->log = Zend_Registry::get('Zend_Log');
	}

	public function findOffendingWords($words, $compareCallback) {
		$blacklistWords = array();
		if (!$words) {
			return $blacklistWords;
		}
		// $supportedCompares = array(
		// 	"equals",
		// 	"startsWith",
		// 	"endsWith",
		// );
		// if (!$compareCallback || !in_array($compareCallback, $supportedCompares)) {
		// 	$compareCallback = array_shift(array_values($supportedCompares));
		// }
		// $cokeList = file_get_contents('http://www.coke.de/www/config.json');
		// $json = json_decode($cokeList);
		// foreach ($words as $word) {
		// 	for ($groupIndex = 0; $groupIndex <= 2; $groupIndex++) {
		// 		$goupName = "group-" . $groupIndex;
		// 		foreach ($json->blacklist->{$goupName} as $blacklistWord) {
		// 			$compare = call_user_func(
		// 				array(
		// 					$this,
		// 					$compareCallback,
		// 				), $word, $blacklistWord);
		// 			if ($compare) {
		// 				array_push($blacklistWords, $blacklistWord);
		// 			}
		// 		}
		// 	}
		// }
		return $blacklistWords;
	}

	private function startsWith($haystack, $needle) {
		return $needle === "" || strpos(strtoupper($haystack), strtoupper($needle)) === 0;
	}

	private function endsWith($haystack, $needle) {
		return $needle === "" || substr(strtoupper($haystack), -strlen($needle)) === strtoupper($needle);
	}

	private function equals($haystack, $needle) {
		return strtoupper($needle) === strtoupper(trim($haystack));
	}

	/**
	 * Validiert die Queryparameter und wirft eine Exception wenn einer der Parameter ungueltig ist.
	 * Diese Funktion ist wichtig, da die Parameter in ein Kommando eingefuegt werden welches auf
	 * der Kommandozeile ausgefuehrt wird.
	 *
	 * @throws Exception
	 * @return multitype:number string
	 */
	public function getCleanParams($imageProps) {
		$params = array();

		if (isset($imageProps["name"])) {
			$dirtyName = substr($imageProps["name"], 0, 25);
			$this->validateName($dirtyName);
			$params["name"] = $dirtyName;
		}

		if (isset($imageProps["x"]) && isset($imageProps["y"]) && isset($imageProps["scale"])) {
			$dirtyX = $imageProps["x"];
			$dirtyY = $imageProps["y"];
			$dirtyScale = $imageProps["scale"];
			$this->validateCoordinate($dirtyX, $dirtyY, $dirtyScale);
			$params["x"] = $dirtyX;
			$params["y"] = $dirtyY;
			$params["scale"] = $dirtyScale;
		}

		return $params;
	}

	private function validateName($dirtyName) {
		if (!$dirtyName) {
			return;
		}

		$nameValidator = new Zend_Validate_Alnum(array(
			"allowWhiteSpace" => true
		));
		if (!$nameValidator->isValid($dirtyName)) {
			throw new Exception("Name contains invalid characters: " . $dirtyName, $this::INVALID_PARAM_ERROR);
		}
		$dirtyNames = explode(" ", $dirtyName);
		$offendingWords = $this->findOffendingWords($dirtyNames, "startsWith");
		if (count($offendingWords) != 0) {
			throw new Exception("Name contains offending words: " . $dirtyName, $this::INVALID_PARAM_ERROR);
		}
	}

	private function validateCoordinate($dirtyX, $dirtyY, $dirtyScale) {
		// Die Koordinaten werden in JS berechnet und haben daher ein englisches Format
		$floatValidator = new Zend_Validate_Float(array(
			"locale" => "en",
		));
		if (!$floatValidator->isValid($dirtyX) && !$floatValidator->isValid($dirtyY) &&
			!$floatValidator->isValid($dirtyScale)) {
			throw new Exception("Invalid numerical value, x={$dirtyX}, y={$dirtyY}, scale={$dirtyScale}", $this::INVALID_PARAM_ERROR);
		}
	}

	private function validatePath($path) {
		if (!file_exists($path)) {
			throw new Exception("Image file could not be found: " . $path, $this::FILE_NOT_FOUND_ERROR);
		}
	}
}