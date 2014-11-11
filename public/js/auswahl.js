(function() {
	$(function() {

		// open/close menu
	    $('span#openMenu').click(function() {
	        $('div#menu').animate({top: '0px'});
	        $('div#menuHandle').animate({top: '-100%'});
	    });
	    $('span#closeMenu').click(function() {
	        $('div#menu').animate({top: '-100%'});
	        $('div#menuHandle').animate({top: '0px'});
	    });

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
	    $('area#backCtrl').click(function() {
			$('img#cameraController').attr('src', '/images/ausloeserBackHvr.png');

	        $('div#bg' + visibleBg).css('z-index', -1);

	        var prevVisibleBg = visibleBg;
	        visibleBg = (visibleBg === 0) ? 1 : 0;
	        selected = (selected > first) ? selected-1 : last;
	        $('div#bg' + visibleBg).css({
	            left: '0px',
	            'z-index': -2,
	            'background-image': 'url("' + imageUrls[selected] + '")'
	        });
	        $('div#bg' + prevVisibleBg).animate({left: '100%'});
		}).mouseenter(function() {
			$('img#cameraController').attr('src', '/images/ausloeserBackHvr.png');
		}).mousedown(function() {
			$('img#cameraController').attr('src', '/images/ausloeserBackDwn.png');
		}).mouseleave(function() {
			$('img#cameraController').attr('src', '/images/ausloeser.png');
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
			$('img#cameraController').attr('src', '/images/ausloeserSlctHvr.png');
		}).mousedown(function() {
			$('img#cameraController').attr('src', '/images/ausloeserSlctDwn.png');
		}).mouseleave(function() {
			$('img#cameraController').attr('src', '/images/ausloeser.png');
		});

	    $('area#helpCtrl').click(function() {
		    $('div#helpBox').css('display', 'block');
		});

	    $('span#closeHelp').click(function() {
	        $('div#helpBox').css('display', 'none');
	    });

	});
})();