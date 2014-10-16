(function() {
	$(document).ready(function() {

		var startIndex;
		var handler = Object.create(imageHandler);
		handler.init({
			defaultImagePath : $('.galeryItem').first().attr('src')
		});
		handler.setBackgroundImage();

		// JCarousel Initialisierung
		$('.jcarousel').jcarousel({
			wrap : 'circular',
			transitions : true,
		});

		// Scroll zum vorgewaehlten Bild
		startIndex = handler.findCarouselIndex($('.galeryItem'));
		$('.jcarousel').jcarousel('scroll', startIndex);
		
		// Verkabel die Bedienelemente
		$('.jcarousel-prev').on('jcarouselcontrol:active', function() {
			$(this).removeClass('inactive');
		}).on('jcarouselcontrol:inactive', function() {
			$(this).addClass('inactive');
		}).jcarouselControl({
			target : '-=1'
		});

		$('.jcarousel-next').on('jcarouselcontrol:active', function() {
			$(this).removeClass('inactive');
		}).on('jcarouselcontrol:inactive', function() {
			$(this).addClass('inactive');
		}).jcarouselControl({
			target : '+=1'
		});

		// Klickhandler fuer Karusellbilder
		$('.galeryItem').click(function() {
			handler.setBackgroundImage(this.src);
		});

		// Schreibe gewaehltes Bild in Form
		$('input[type=submit]').button().click(function() {
			var imgPath = $('html').css('backgroundImage').split('/');
			$('#image').val(imgPath[imgPath.length - 1].replace(')', ''));
		});
	});
})();