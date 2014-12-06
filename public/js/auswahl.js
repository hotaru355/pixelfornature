

(function() {
	function preloadPictures(pictureUrls, onAllLoaded, onError, onAbort) {
	    var loaded = 0;

	    return pictureUrls.map(function (url, idx) {
	    	var img = new Image();
            img.onload = function () {                               
                if (++loaded == pictureUrls.length && onAllLoaded) {
                    onAllLoaded();
                }
            };
            if (onError) {
            	img.onerror = onError();
            }
            if (onAbort) {
            	img.onabort = onAbort();
            }
            img.src = url;
            return img;
	    })
	};
	// var imageObjs = preloadPictures(imageUrls, function(){
	// 		alert('done')
	// 	});

	// (function preload(arrayOfImages) {
	//     $(arrayOfImages).each(function () {
	//         $('<img />').attr('src',this).appendTo('body').css('display','none');
	//     })
	// })(imageUrls);

	const first = 0;
	const last = imageUrls.length - 1;

	function transitionBg(isRight, onTransitionEnd) {
		var start, end;
		var index = parseInt($('#bg-frame .sliding-card.slided-center').attr('id').substr(3));

		if (isRight) {
	        index = (index < last) ? index + 1 : first;
			start = 'slided-left';
			end = 'slided-right';
		} else {
	        index = (index > first) ? index - 1 : last;
			start = 'slided-right';
			end = 'slided-left';
		}

		var bgDiv = $('#bg-' + index);
		// place new background div to the right/left of the screen 
		bgDiv.removeClass(end +' slided').addClass(start);
		// Trigger a reflow, flushing the CSS changes
		bgDiv[0].offsetHeight;

		// slide the cards over
		$('#bg-frame .sliding-card.slided-center').removeClass('slided-center').addClass(end + ' slided');
		bgDiv.removeClass(start).addClass('slided-center slided');

		if (onTransitionEnd) {
			bgDiv.one($.support.transition.end, onTransitionEnd);
		}
	}


	$(function() {
		// preload(imageUrls, function(){
		// 	alert('done')
		// })



	    // camera controller
	    $('area#backCtrl').mouseenter(function() {
			$('div#controllerDiv').css('background-position', '0 0');
		}).mousedown(function() {
			$('div#controllerDiv').css('background-position', '-203px 0');
			transitionBg(true);
		}).mouseup(function() {
			$('div#controllerDiv').css('background-position', '0 0');
		}).mouseleave(function() {
			$('div#controllerDiv').css('background-position', '-203px -406px');
		});
	    
	    $('area#forwardCtrl').mouseenter(function() {
			$('div#controllerDiv').css('background-position', '-203px -406px');
		}).mousedown(function() {
			$('div#controllerDiv').css('background-position', '-203px -406px');

			transitionBg(false);
		}).mouseup(function() {
			$('div#controllerDiv').css('background-position', '-203px -406px');
		}).mouseleave(function() {
			$('div#controllerDiv').css('background-position', '-203px -406px');
		});

	    $('area#selectCtrl').click(function() {
			$('input#image').val($('#bg-frame .sliding-card.slided-center').attr('id').substr(3));
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