

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

	$(function() {
		// preload(imageUrls, function(){
		// 	alert('done')
		// })

	    const first = 0;
	    const last = imageUrls.length - 1;

	    var visibleBg = 0;

	    // init bg divs
	    $('div#bg' + visibleBg).css({
	        'background-image': 'url("' + imageUrls[selected] + '")',
	        'z-index': -1
	    });
	    $('div#bg1').css({
	        'z-index': -1
	    });

	    // init menu
	    $('div#slidingContainer').append($('div#menuLanding'));

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