<?php
class ImageService {
	private $log;
	const GENERATE_IMAGE_ERROR = 10002;
	const MISSING_ARGUMENT_ERROR = 10003;
	private $logoPath;

	public function __construct() {
		$this->log = Zend_Registry::get('Zend_Log');
		// $this->logoPath = realpath(APPLICATION_PATH . "/../public/images/logo.png");
		$this->logoPath = realpath(APPLICATION_PATH . "/../public/images/Logo_no_shadow.png");
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
			"x" => round(-0.5 * ($imageDimensions["width"] - $imageDimensions["width"] * $scale) - $x),
			"y" => round(-0.5 * ($imageDimensions["height"] - $imageDimensions["height"] * $scale) - $y)
		);
	}

	public function generateCoverPhotoAPI($imagePath, $imageDimensions, $fbCover, $params) {
		$this->checkArguments($imagePath, $imageDimensions, $fbCover, $params);
		$outputFile = $this->getRandomImagePath();
		$labelText = $params["name"] . " spendet Fläche für Natur.";
		$scaled = $this->getScaling($imageDimensions, $params["x"], $params["y"], $params["scale"]);

		$image = new Imagick($imagePath);
		$image->resizeImage($scaled["width"], $scaled["height"], Imagick::FILTER_LANCZOS, 1);
		$image->cropimage($fbCover["width"], $fbCover["height"], $scaled["x"], $scaled["y"]);

		$logo = new Imagick($this->logoPath);
		$shadow_layer = $logo->clone(); 
		$shadow_layer->setImageBackgroundColor(new ImagickPixel('black')); 
		$shadow_layer->shadowImage(40, 2, 0, 0); 
		$shadow_layer->compositeImage($logo, Imagick::COMPOSITE_OVER, 4, 4);
		$image->compositeImage($shadow_layer, Imagick::COMPOSITE_OVER, 691, 7);
		$logo->destroy();

		$draw = new ImagickDraw();
		$draw->setFont('Helvetica-Narrow-Bold');
		$draw->setFontSize(24);
		$draw->setfillcolor(new ImagickPixel("white"));

		$label = new Imagick();
		$font_info = $label->queryFontMetrics($draw, $labelText);
		$label->newImage($font_info['textWidth']+20, $font_info['textHeight']+9, 'none');
		$label->annotateImage($draw, 10, 26, 0, $labelText);
		$draw->destroy();

		$shadow_layer = $label->clone(); 
		$shadow_layer->setImageBackgroundColor( new ImagickPixel( 'black' ) ); 
		$shadow_layer->shadowImage(40, 2, 0, 0); 
		$shadow_layer->compositeImage($label, Imagick::COMPOSITE_OVER, 4, 4);

		$image->compositeImage($shadow_layer, Imagick::COMPOSITE_OVER, 2, 30);
		$shadow_layer->destroy();
		$label->destroy();

		$image->writeimage($outputFile);
		$image->destroy();

		return $outputFile;
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