(function() {
	$(function() {
		$('button#closeMenu').click(function() {
			setTimeout(function() {
				window.location.href = '/';
			}, 1000);
		})

		// remove other login page to avoid conflict
		$('ul#menuPages').remove();

	    // init menu
	    $('div#slidingContainer').append($('div#menuThankyou'));

		$('div#menu').css({top: 0});
	});
})();