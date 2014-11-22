(function() {
	$(function() {
		$('button#closeMenu').click(function() {
			setTimeout(function() {
				window.location.href = '/';
			}, 1000);
		})

	    // init menu
	    $('div#slidingContainer').append($('div#menuThankyou'));

		$('div#menu').css({top: 0});
	});
})();