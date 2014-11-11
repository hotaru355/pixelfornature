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

	function transformOnParams() {
		var x = $.url().param('x');
		var y = $.url().param('y');
		var scale = $.url().param('scale');
		if (x && y && scale) {
			$('#slider').slider('value', scale);
			$('#clippingImage').panzoom('setMatrix', [scale, 0, 0, scale, x, y]);
		}
	}

	function fadeOutCallback() {
		setTimeout(function() {
			$("#illegalNameMsg:visible").removeAttr("style").fadeOut();
		}, 1000);
	}

	function isValidName(name) {
		var isValid = false;
		$.ajax({
			url: '/validierung/validiere-woerter',
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
	    // init bg divs
	    $('div#bg0').css({
	        'background-image': 'url("' + imagePath + '")',
	        'z-index': -1
	    });
	    $('div#bg1').css({
	        'z-index': -1
	    });

	    $('area#helpCtrl').click(function() {
		    $('div#helpBox').css('display', 'block');
		});

	    $('span#closeHelp').click(function() {
	        $('div#helpBox').css('display', 'none');
	    });

		$('#select_ctrl').click(function() {
			$('#image').val(imageUrls[selected]);
			$('#auswahl_form').submit();
		});


		var initialMatrix = getInitialMatrix();
		$('#slider').circleSlider({
			min: fbCover.minScale,
			max: fbCover.maxScale,
			step: 0.01,
			value: initialMatrix.scale,
			container: 'rotationSliderContainer',
			slider: 'rotationSlider',
			slide: function(event, value) {
				$('#clippingImage').panzoom('zoom', value);
			}
		});


		// Panzoom Initialisierung
		$('#clippingImage').panzoom({
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
		transformOnParams();

		/**
		 * schreibt die Koordinaten und die Skalierung des Ausschnitts
		 * in die HTML-Form
		 */
		$('form').submit(function(event) {
			var matrix = $('#clippingImage').panzoom('getMatrix');
			var scale = matrix[0];
			$('#x').val(parseFloat(matrix[4]).toFixed(2));
			$('#y').val(parseFloat(matrix[5]).toFixed(2));
			$('#scale').val(parseFloat(scale).toFixed(4));
			return isValidName($('#name').val());
		});

	});
})();