
// imageHandler = {
// 	paramName : 'image',
// 	image : '',

// 	init : function(config) {
// 		if (config !== undefined && config.paramName !== undefined) {
// 			this.paramName = config.paramName;
// 		}
// 		this.image = ($.url().param(this.paramName));
// 	},

// 	setBackgroundImage : function(newPath) {
// 		if (newPath !== undefined) {
// 			this.imagePath = newPath;
// 		}
// 		$('html').css('backgroundImage', 'url(' + this.image + ')');
// 	},
// };

(function() {
	var menuNewMember, menuLanding, menuResetPassword, menuAccount;

	function transitionMenu(menuDiv) {
		placeMenu(menuDiv, '0');
		$('div#slidingContainer').append(menuDiv);
		$('div#slidingContainer')[0].offsetHeight;
		$('div#slidingContainer').children().each(function(index, child) {
			$(this).css('left', '-50%');
		})
	}

	function placeMenu(menuDiv, left) {
		menuDiv.addClass('noTransition'); // Disable transitions
		menuDiv.css('left', left);
		menuDiv[0].offsetHeight; // Trigger a reflow, flushing the CSS changes
		menuDiv.removeClass('noTransition'); // Re-enable transitions
	}

	function resetMenu() {
		if ($('div#slidingContainer').children().length > 1) {
			$('div#slidingContainer').children().each(function(index, child) {
				if (index === 0) {
					placeMenu($(this), '0');
				} else {
					$(this).remove();
				}
			})
		}
	}

	$(function() {
		menuNewMember = $('div#menuNewMember');
		menuResetPassword = $('div#menuResetPassword');
		menuAccount = $('div#menuAccount');

	    $('area#infoCtrl').click(function() {
	        resetMenu();
	        $('div#menu').animate({top: 0});
		});
	    $('button#closeMenu').click(function() {
	        $('div#menu').animate({top: '-100%'});
	    });

  	    $('area#flashCtrl').click(function() {
		    $('div#helpBox').toggle();
		});

	    $('button#closeHelp').click(function() {
	        $('div#helpBox').toggle();
	    });


		$('a.registerNow').click(function() {
	        transitionMenu(menuNewMember);
	    });

		$('a.lostPassword').click(function() {
	        transitionMenu(menuResetPassword);
	    });
	})
})();