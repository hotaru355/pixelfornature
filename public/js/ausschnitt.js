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
			slider: '#zoom-button',
			container: '#master-control',
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
				// console.log('x', Math.round(0.5 * original.width * pzObj.getMatrix()[0] - 0.5 * original.width - pzObj.getMatrix()[4]));
				// console.log('y', Math.round(0.5 * original.height * pzObj.getMatrix()[0] - 0.5 * original.height - pzObj.getMatrix()[5]));
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

		// camera controller, png style
		/*
	    ['up', 'down', 'middle'].forEach(function(direction) {
	    	$('#' + direction + 'Ctrl').mouseenter(function() {
		    	$('#controllerDiv').attr('class', 'center-block controller-' + direction + '-hover-noslide');
			}).mousedown(function() {
		    	$('#controllerDiv').attr('class', 'center-block controller-' + direction + '-active-noslide');
			}).mouseup(function() {
		    	$('#controllerDiv').attr('class', 'center-block controller-normal-noslide');
			}).mouseleave(function() {
		    	$('#controllerDiv').attr('class', 'center-block controller-normal-noslide');
			});
	    })
		*/

	    $('#middle-button').click(function() {
		    $('#ausschnitt').submit();
		});

	});
})();