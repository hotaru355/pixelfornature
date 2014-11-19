
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
		$('div#slidingContainer').append(menuDiv);
		//			console.log('children', $('div#slidingContainer').children())
		$('div#slidingContainer').children().each(function(index, child) {
			$(this).css('left', '-50%');
		})
	}

	function resetMenu() {
		if ($('div#slidingContainer').children().length > 1) {
			$('div#slidingContainer').children().each(function(index, child) {
				if (index === 0) {
					$(this).addClass('noTransition'); // Disable transitions
					$(this).css('left', '0');
					$(this)[0].offsetHeight; // Trigger a reflow, flushing the CSS changes
					$(this).removeClass('noTransition'); // Re-enable transitions
				} else {
					$(this).remove();
				}
			})
		}
	}

	$(function() {
		menuNewMember = $('div#menuNewMember');
		menuLanding = $('div#menuLanding');
		menuResetPassword = $('div#menuResetPassword');
		menuAccount = $('div#menuAccount');

		$('div#slidingContainer').append(menuLanding);

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


		$('a#registerNow').click(function() {
	        transitionMenu(menuNewMember);
	    });

		$('a#lostPassword').click(function() {
	        transitionMenu(menuResetPassword);
	    });
	})
})();