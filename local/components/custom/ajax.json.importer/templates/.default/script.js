;(function() {
'use strict'
	//считаем что компонент один на странице

	function JSON_Uploader(block) {
		this.form = null;
		this.info = null;
		this.input = null;
		this.progressbar = null;
		this.message = null;
		this.step = 1;
		this.max_step = 6;
		this.xhr=null;
		this.block = null;
		this.wait = null;

		//инициализировать компонент
		this.init = function(block) {
			this.block = block;
			this.form = block.querySelector('.js-uploader__form');
			this.info = block.querySelector('.js-uploader__info');
			this.input = block.querySelector('.js-uploader__file');

			if(typeof(this.form) === 'undefined') return;
			if(typeof(this.info) === 'undefined') return;
			if(typeof(this.input) === 'undefined') return;

			var submit_handler = this.onsubmit.bind(this);

			document.forms.file_form.onsubmit = submit_handler;
		}

		//полностью очищает информер
		this.clearInformer = function() {
			this.info.innerText="";
		}

		//вставляет новую строку в информер
		this.addInfoLine = function(data) {
			if(typeof(data) === 'undefined' ) return;

			var res = {};
			if( typeof(data.use_progress) !== 'undefined' && data.use_progress == true) {
				var progress = document.createElement('div');
				progress.classList.add('progress');

				var progressbar = document.createElement('div');
				progressbar.classList.add('progress-bar');
				progressbar.classList.add('progress-bar-striped');
				
				progressbar.style.width='0%';

				progress.append(progressbar);
				this.info.append(progress);

				this.progressbar = progressbar;
			}

			if( typeof(data.type) !== 'undefined' && typeof(data.text) !== 'undefined') {
				var message = document.createElement('div');
				message.classList.add('alert');
				message.classList.add('alert-'+data.type);
				message.innerText = data.text;


				this.info.append(message);
				this.message = message;
			}
		}

		//функция устанавливающая в информере статус текущего шага и его текст.
		//текст возвращается с бэкенда
		this.setStepStatus = function(data) {
			if(typeof(data) === 'undefined' ) return;

			if( typeof(data.type) !== 'undefined') {
				this.message.classList.remove('alert-info');
				//this.message.classList.remove('alert-success');
				//this.message.classList.remove('alert-danger');
				this.message.classList.add('alert-'+data.type);
			}

			if( typeof(data.text) !== 'undefined') {
				this.message.innerText = data.text;
			}
		}


		//функция, ловящая сабмит
		//сабмит запускает цепочку выполнения
		this.onsubmit = function(event) {
		    event.preventDefault();
			this.clearInformer();

			//сброс настроек
			this.progressbar = null;
			this.message = null;
			this.step = 1;

			this.showWait();

		    var file = this.input.files[0];
		    if (typeof(file) === 'undefined') {
		    	this.addInfoLine({
		    		//use_progress: true,
		    		type: 'danger',
		    		text: 'Не выбран файл',
		    	});
		    	this.hideWait();
		    	return;
		    }
		 
		    this.uploadFile(file);
		}

		//функция, устанавливающая положение прогресс-бара
		this.setProgress = function(percent) {
			if(typeof(this.progressbar) !== 'undefined') {
	    		this.progressbar.style.width = percent+'%';
	    	}
		}

		//функция возникающая при событии прогресса. вычисляет процент прогресса и вызывает функцию
		this.onprogress = function(event) {
			if(event.total == 0) return;
			var percent = ( event.loaded / event.total ) * 100;
			this.setProgress( percent );
		}

		this.xhrHandler = function(event) {
			var data = {};

			try {
				data = JSON.parse(this.xhr.responseText);
			}
			catch(e){
				data.message = e.message;
				this.stepError(data);
			}
	
			if (this.xhr.status == 200) {
			  this.checkResult(data);
			} else {
			  this.stepError(data);
			}
			
		}

		//функция, вызывающая нужную функцию нужного шага
		//функция запускает следующий шаг импорта
		//либо остаётся на текущем
		this.nextStep = function(data) {
			if(typeof(data.next_step) === 'undefined') return;
			
			var status = data.status;

			//набор классов для статуса
			var statClass = {
				success: 'success',
				continue: 'info',
				error: 'danger',
			}
			var messClass = statClass[ status ];


			this.setStepStatus({
				type: messClass, //возможны другие статусы
				text: data.message,
			});
			
			//это был последний шаг
			if(this.step >= this.max_step) {
				this.hideWait();
				return;
			}

			this.step = data.next_step;
			this.addInfoLine({
	    		type: 'info',
	    		text: data.next_message,
	    	});


	    	var formData = new FormData();
			formData.append("step", this.step);

			this.xhr.open("POST", "", true);
			this.xhr.send(formData);

			//следующий запрос
			//следующий шаг
			return;

		}

		//текущая отправка завершена
		//проверяет завершён ли шаг и надо ли делать следующий
		this.checkResult = function(data) {
			this.message.classList.remove("alert-info");
			this.message.classList.add("alert-success");

			this.progressbar.style.display = 'none';

			if(typeof(data.message) !== 'undefined') {
				this.message.innerText = data.message;
			}

			this.nextStep(data);
		}

		this.stepError = function(data) {
			this.hideWait();

			this.message.classList.remove("alert-info");
			this.message.classList.add("alert-danger");

			if(typeof(data.message) !== 'undefined') {
				this.message.innerText = data.message;
			}
		}

		this.showWait = function() {
			if(typeof(BX) !== 'undefined') {
				this.wait = BX.showWait(this.block);
			}
		}

		this.hideWait = function() {
			if(typeof(BX) !== 'undefined') {
				BX.closeWait(this.block, this.wait);
			}
		}




		this.uploadFile = function(file) {

		    //вставляем инфостроку
			this.addInfoLine({
	    		use_progress: true,
	    		type: 'info',
	    		text: 'Загрузка файла',
	    	});

	    	this.xhr = new XMLHttpRequest();

			// обработчик для закачки
			this.xhr.upload.onprogress = this.onprogress.bind(this);

			//обработчики завершения передачи
			this.xhr.onload = this.xhrHandler.bind(this);
			this.xhr.onerror = this.stepError.bind(this);


			var formData = new FormData();
			formData.append("datafile", file);
			formData.append("step", 1);

			this.xhr.open("POST", "", true);
			this.xhr.send(formData);

		}

		this.init(block);
	}

	function componentsInit() {
		var blockList = document.querySelectorAll('.js-uploader');
		for(var i=0;i<blockList.length;i++) {
			new JSON_Uploader(blockList[i]);
		}
	}

	document.addEventListener('DOMContentLoaded', componentsInit);
}());