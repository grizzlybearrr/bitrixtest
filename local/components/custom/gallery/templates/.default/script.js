;(function ($) {
'use strict';

	/**
	* функция цепочки запросов
	*/
	function requestChain(url, data){
		$.post({
			url: url,
			data: data,
			dataType: 'json'
		})
		.done(function(resp){

			var sendData = {
				action: data.action,
				stage: resp.stage,
				id: data.id,
			};

			//выбираем действие в зависимости от ответа
			if(resp.stage=='complete') {
				alert('Операция успешно завершена');
			}

			//редирект
			if(resp.stage=='redirect') {
				window.location.href = resp.url;
			}

			//запрос пароля
			if(resp.stage=='password') {
				var pass = prompt('Введите пароль редактирования картинки', '');
				if(pass==null) { 
					alert('Ввод пароля отменён');
					return;
				}
				sendData['password'] = pass;

				//этап сохранения картинки
				if(resp.action=='save') {
					var canvas = document.getElementById('workarea');
					var imageData = canvas.toDataURL("image/png");
					sendData['image'] = imageData;
				}

				requestChain(url, sendData);
			}

			//сохранение изображения
			if(resp.stage=='save') {

				var canvas = document.getElementById('workarea');
				var imageData = canvas.toDataURL("image/png");
				sendData['image'] = imageData;

				requestChain(url, sendData);
			}

		})
		.fail(function(){
			alert('Ошибка выполнения операции');
		});
	}
	
	/**
	* функция брабатывает все кнопки галереи
	*/
	function СanvasButtonControl() {
		$('.js-canvas-button').on('click', function(){
			var $this = $(this);
			var id = $this.data('id');
			var action = $this.data('action');

			//первый шаг
			var sendData = {
				action: action,
				id: id,
			}

			if(window.cnv_gallery.root != '') {
				var folder = window.cnv_gallery.root;
				requestChain( folder + 'ajax/', sendData);
			}
		});
	}


 $(function() {
    СanvasButtonControl();
  });

})(window.jQuery);