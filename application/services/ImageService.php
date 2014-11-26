<?php
class ImageService {
	private $log;
	const GENERATE_IMAGE_ERROR = 10002;
	const MISSING_ARGUMENT_ERROR = 10003;
	private $logoPath;

	public function __construct() {
		$this->log = Zend_Registry::get('Zend_Log');
		$this->logoPath = realpath(APPLICATION_PATH . "/../public/images/logo.png");
	}

	/**
	 * Panzoom (front-end) belässt die Canvas in der Orginalgröße und plaziert das skalierte Bild in
	 * den Bildmittelpunkt der unveränderten Canvas. Für ImageMagick benötigen wir neue Koordinaten
	 * für eine Canvas, die sich dem skalierten Bild anpaßt.
	 */
	private function getScaling($imageDimensions, $x, $y, $scale) {
		return array(
			"width" => round($imageDimensions["width"] * $scale),
			"height" => round($imageDimensions["height"] * $scale),
			"x" => round(-0.5 * ($imageDimensions["width"]-$imageDimensions["width"] * $scale) - $x),
			"y" => round(-0.5 * ($imageDimensions["height"]-$imageDimensions["height"] * $scale) - $y)
		);
	}

	public function generateCoverPhotoAPI($params) {
		$scaled = $this->getScaling($params["x"], $params["y"], $params["scale"]);

		$image = new Imagick($params["imagePath"]);
		$image->resizeImage($scaled["width"], $scaled["height"], Imagick::FILTER_LANCZOS, 1);
		$image->cropimage($fbCover["width"], $fbCover["height"], $scaled["x"], $scaled["y"]);

		$logo = new Imagick($params["logoPath"]);
		$image->compositeImage($logo, Imagick::COMPOSITE_DEFAULT, 580, 0);
		$logo->destroy();

		$drawColor = new ImagickPixel("ivory2");
		$draw = new ImagickDraw();
		$draw->setFont('DejaVu-Serif-Book');
		$draw->setFontSize(24);
		$draw->setfillcolor($drawColor);
		$label = new Imagick();
		$font_info = $label->queryFontMetrics($draw, $params["labelText"]);
		$blackTransparentBG = new ImagickPixel("rgba(0,0,0,0.5)");
		$label->newImage($font_info['textWidth']+20, $font_info['textHeight']+9, $blackTransparentBG);
		$label->annotateImage($draw, 10, 26, 0, $params["labelText"]);
		$image->compositeImage($label, Imagick::COMPOSITE_DEFAULT, 0, 100);
		$draw->destroy();
		$blackTransparentBG->destroy();
		$label->destroy();

		$whtiteBar = new Imagick();
		$whiteTransparentBG = new ImagickPixel("rgba(255,255,255,0.5)");
		$whtiteBar->newImage($font_info['textWidth']+20, 6, $whiteTransparentBG);
		$image->compositeImage($whtiteBar, Imagick::COMPOSITE_DEFAULT, 0, 136);
		$whiteTransparentBG->destroy();
		$whtiteBar->destroy();

		$image->writeimage($params["outputFile"]);
		$image->destroy();
	}

	public function generateCoverPhotoCL($imagePath, $imageDimensions, $fbCover, $params) {
		$this->checkArguments($imagePath, $imageDimensions, $fbCover, $params);
		$outputFile = $this->getRandomImagePath();
		$labelText = $params["name"] . " spendet Fläche für Natur";
		$scaled = $this->getScaling($imageDimensions, $params["x"], $params["y"], $params["scale"]);

		$command = sprintf(
			"convert \( -resize '%dx%d' -unsharp '0x0.75+0.75+0.008' -crop '%dx%d+%d+%d' %s \) %s " .
			"-geometry '+580+0' -composite " .
			"\( -bordercolor rgba\(0,0,0,0.5\) -border '10x10' -background transparent -pointsize 24 " .
			"-fill ivory2 -font DejaVu-Serif-Book label:'%s' -geometry '+0+100' \) -composite %s 2>&1",

			$scaled["width"], $scaled["height"], $fbCover["width"], $fbCover["height"], $scaled["x"],

			$scaled["y"], $imagePath, $this->logoPath, $labelText, $outputFile);

		$this->log->debug($command);

		$shellOutput = shell_exec($command);

		if ($shellOutput) {
			throw new Exception($shellOutput, $this::GENERATE_IMAGE_ERROR);
		}

		return $outputFile;
	}

	private function getRandomImagePath() {
		$fileName = "gen" . uniqid() . ".png";
		return realpath(APPLICATION_PATH . "/../public/images/tmp/") . "/" . $fileName;
	}

	private function checkArguments($imagePath, $imageDimensions, $fbCover, $params) {
		if (!isset($imagePath)) {
			throw new Exception("Image path not provided.", $this::MISSING_ARGUMENT_ERROR);
		}
		if (!isset($imageDimensions["width"])) {
			throw new Exception("Image width not provided.", $this::MISSING_ARGUMENT_ERROR);
		}
		if (!isset($imageDimensions["height"])) {
			throw new Exception("Image height not provided.", $this::MISSING_ARGUMENT_ERROR);
		}
		if (!isset($fbCover["width"])) {
			throw new Exception("Facebook cover photo width not provided.", $this::MISSING_ARGUMENT_ERROR);
		}
		if (!isset($fbCover["height"])) {
			throw new Exception("Facebook cover photo height not provided.", $this::MISSING_ARGUMENT_ERROR);
		}
		if (!isset($params["name"])) {
			throw new Exception("User name not provided.", $this::MISSING_ARGUMENT_ERROR);
		}
		if (!isset($params["x"])) {
			throw new Exception("X coordinate not provided.", $this::MISSING_ARGUMENT_ERROR);
		}
		if (!isset($params["y"])) {
			throw new Exception("Y coordinate not provided.", $this::MISSING_ARGUMENT_ERROR);
		}
		if (!isset($params["scale"])) {
			throw new Exception("Scale coordinate not provided.", $this::MISSING_ARGUMENT_ERROR);
		}
	}
}