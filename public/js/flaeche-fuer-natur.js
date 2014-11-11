if (typeof Object.create !== 'function') {
	Object.create = function(obj) {
		var Func = function() {
		};
		Func.prototype = obj;
		return new F();
	};
}

imageHandler = {
	paramName : 'image',
	image : '',

	init : function(config) {
		if (config !== undefined && config.paramName !== undefined) {
			this.paramName = config.paramName;
		}
		this.image = ($.url().param(this.paramName));
	},

	setBackgroundImage : function(newPath) {
		if (newPath !== undefined) {
			this.imagePath = newPath;
		}
		$('html').css('backgroundImage', 'url(' + this.image + ')');
	},
};