;(function(){
'use strict'

	function FilterControl(block) {
		this.block = null;
		this.params = null;
		this.result = null;
		this.xhr = null;

		this.init = function(block){
			this.block = block;
			this.params = block.querySelectorAll('.js-filter__param-value');
			this.result = block.querySelector('.js-filter__result');
			this.xhr = new XMLHttpRequest();

			//если компонент неполный
			if(!this.params || !this.result) return;

			//события изменения
			var clickHandler = this.update.bind(this);
			for(var i=0;i<this.params.length; i++) {
				this.params[i].addEventListener('change', clickHandler);
			}

			//событие успешной загрузки данных
			this.xhr.onload = this.xhrHandler.bind(this);
		}

		//чтение параметров формы и запрос данных
		this.update = function() {
			var data = this.readFormValues();
			this.send(data);
		}

		//считывание всех параметров
		this.readFormValues = function() {
			var key, i, data={};
			for(i=0; i< this.params.length; i++) {
				key = this.params[i].name;
				data[key] = this.readSelectValue( this.params[i] );
			}
			return data;
		}

		//считывание одного параметра
		this.readSelectValue = function(select) {
			var i, val=[];
			for(i=0; i< select.selectedOptions.length; i++) {
				val.push(select.selectedOptions[i].value);
			}
			return val;
		}

		//отправка данных
		this.send = function(data) {
			var formData = new FormData();
		
			formData.append('data', JSON.stringify(data) );

			this.xhr.open("POST", "", true);
			this.xhr.send(formData);
		}

		//обработка результата запроса
		//вставка данных в таблицу
		this.xhrHandler = function(event) {
			var data = this.xhr.responseText;

			if (this.xhr.status == 200) {
			  this.result.innerHTML = data;
			} else {
			  alert('Ошибка запроса');
			}
			
		}

		this.init(block);
	}

	document.addEventListener('DOMContentLoaded',function(){
		//инициализация всех блоков на странице, если их несколько
		var block_list = document.querySelectorAll('.js-filter');
		for(var i=0; i< block_list.length; i++) {
			new FilterControl(block_list[i]);
		}
	});

})()