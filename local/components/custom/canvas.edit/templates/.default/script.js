;(function ($) {
'use strict';

	function loadCanvas(imgSrc) {
	    var ctx = document.getElementById('workarea');
	    if (ctx.getContext) {
	        ctx = ctx.getContext('2d');
	        var canvasImg = new Image();
	        canvasImg.onload = function () {
	            ctx.drawImage(canvasImg, 0, 0);
	        };
	        canvasImg.src = imgSrc;
	    }
	}

	function initEditor(){
		var sketchpad = new Sketchpad({
		  element: '#workarea',
		  width: 320,
		  height: 240
		});
	}

	$(function() {
		initEditor();
		if(typeof(window.cnv_gallery.img_src) != '') {
			loadCanvas( window.cnv_gallery.img_src );
		}
	});

})(window.jQuery);
