

(function() {
	function preload(images, cb) {
		var imageCount = 0;
		images.forEach(function(image) {
			imageObj = new Image();
			imageObj.onload = function() {
				imageCount++;
				if (imageCount === images.length) {
					cb()
				}
			}
			imageObj.src = image;
		})
	}
	var appCache = window.applicationCache;

	appCache.addEventListener('cached', function() {
		alert('cached')
	}, false);
	appCache.addEventListener('updateready', function() {
		alert('updated')
	}, false);



	$(function() {
		// preload(imageUrls, function(){
		// 	alert('done')
		// })

	    const first = 0;
	    const last = imageUrls.length - 1;
	    var selected = ($.url().param('image')) || first;
	    var visibleBg = 0;

	    // init bg divs
	    $('div#bg' + visibleBg).css({
	        'background-image': 'url("' + imageUrls[selected] + '")',
	        'z-index': -1
	    });
	    $('div#bg1').css({
	        'z-index': -1
	    });
	 
	    // camera controller
	    $('area#backCtrl').mouseenter(function() {
			$('div#controllerDiv').css('background-position', '0 0');
		}).mousedown(function() {
			$('div#controllerDiv').css('background-position', '-203px 0');

	        $('div#bg' + visibleBg).css('z-index', -1);

	        var prevVisibleBg = visibleBg;
	        visibleBg = (visibleBg === 0) ? 1 : 0;
	        selected = (selected > first) ? selected-1 : last;
	        $('div#bg' + visibleBg).css({
	            left: 0,
	            'z-index': -2,
	            'background-image': 'url("' + imageUrls[selected] + '")'
	        });
	        $('div#bg' + prevVisibleBg).animate({left: '100%'});
		}).mouseup(function() {
			$('div#controllerDiv').css('background-position', '0 0');
		}).mouseleave(function() {
			$('div#controllerDiv').css('background-position', '-203px -406px');
		});
	    
	    $('area#forwardCtrl').click(function() {
	        $('div#bg' + visibleBg).css('z-index', -2);

	        visibleBg = (visibleBg === 0) ? 1 : 0;
	        selected = (selected < last) ? selected+1 : first;
	        $('div#bg' + visibleBg).css({
	            left: '100%',
	            'z-index': -1,
	            'background-image': 'url("' + imageUrls[selected] + '")'
	        });
	        $('div#bg' + visibleBg).animate({left: 0});
	    });

	    $('area#selectCtrl').click(function() {
			$('input#image').val(selected);
		    $('form#auswahl').submit();
		}).mouseenter(function() {
			$('div#controllerDiv').css('background-position', '0 -203px');
		}).mousedown(function() {
			$('div#controllerDiv').css('background-position', '-203px -203px');
		}).mouseleave(function() {
			$('div#controllerDiv').css('background-position', '-203px -406px');
		});


	});
})();