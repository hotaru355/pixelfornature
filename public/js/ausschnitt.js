(function() {
	function getInitialMatrix() {
		var ausschnittTop = $('#clipping').offset().top;
		var scale = (window.innerWidth > window.innerHeight * original.ratio) ? window.innerWidth / original.width : window.innerHeight / original.height;
		scale = (scale > fbCover.maxScale) ? fbCover.maxScale : scale;
		scale = (scale < fbCover.minScale) ? fbCover.minScale : scale;

		var x = (original.width - fbCover.width) / -2;
		var y = (original.height * scale - original.height) / 2 - ausschnittTop;
		return {
			scale: scale,
			x: x,
			y: y,
			// CSS3 2D Transform matrix(xScale, xSkew, ySkew, yScale, xTrans, yTrans)
			toString: 'matrix(' + scale + ', 0.0, 0.0, ' + scale + ', ' + x + ', ' + y + ')',
		};
	}

	function fadeOutCallback() {
		setTimeout(function() {
			$("#illegalNameMsg:visible").removeAttr("style").fadeOut();
		}, 1000);
	}

	function isValidName(name) {
		var isValid = false;
		$.ajax({
			url: '/validierung/woerter',
			type: 'GET',
			dataType: 'json',
			async: false,
			data: {
				'woerter': name,
				'methode': 'equals',
			},
			global: true,
		}).done(function(responseJson) {
			if (responseJson.length > 0) {
				$('#illegalWords').html(responseJson.join());
				$('#illegalNameMsg').show('highlight', {
					color: "#ff0000"
				}, 3000, fadeOutCallback);
			} else {
				isValid = true;
			}
		}).fail(function(responseJson) {});
		return isValid;
	}

	$(function() {

	    // init menu
	    $('div#slidingContainer').append($('div#menuLanding'));

		var initialMatrix = getInitialMatrix();
		$('#slider').circleSlider({
			min: fbCover.minScale,
			max: fbCover.maxScale,
			step: 0.01,
			value: initialMatrix.scale,
			container: 'rotationSliderContainer',
			slider: 'rotationSlider',
			slide: function(event, value) {
				$('img#clippingImage').panzoom('zoom', value);
			}
		});


		// Panzoom Initialisierung
		$('img#clippingImage').panzoom({
			$reset: $('#reset'),
			startTransform: initialMatrix.toString,
			minScale: fbCover.minScale,
			maxScale: fbCover.maxScale,
			contain: 'invert',
			onReset: function(event, pzObj) {
				$('#slider').slider('value', pzObj.getMatrix()[0]);
			},
			onChange: function(event, pzObj) {
				// Debug
				//console.log(pzObj.getMatrix());
			},
		});
		
		/**
		 * schreibt die Koordinaten und die Skalierung des Ausschnitts
		 * in die HTML-Form
		 */
		$('form').submit(function(event) {
			var matrix = $('img#clippingImage').panzoom('getMatrix');
			var scale = matrix[0];
			var nameEntered = $('input#name').val();
			$('input#x').val(parseFloat(matrix[4]).toFixed(2));
			$('input#y').val(parseFloat(matrix[5]).toFixed(2));
			$('input#scale').val(parseFloat(scale).toFixed(4));
			return (isValidName(nameEntered));
		});

	    $('area#selectCtrl').click(function() {
		    $('form#ausschnitt').submit();
		}).mouseenter(function() {
			$('div#controllerDiv').css('background-position', '0 -203px');
		}).mousedown(function() {
			$('div#controllerDiv').css('background-position', '-203px -203px');
		}).mouseleave(function() {
			$('div#controllerDiv').css('background-position', '0 -406px');
		});

	});
})();