var FlowluForm = function(){
	var originalButtonHTML = '';

	var blockUI = function(container){
		Array.prototype.forEach.call(container.getElementsByClassName('flowlu-submit'), function(button){
			originalButtonHTML = button.innerHTML;
			button.disabled = true;
			button.innerHTML = '....';
		});
	};

	var unblockUI = function(container){
		Array.prototype.forEach.call(container.getElementsByClassName('flowlu-submit'), function(button){
			button.disabled = false;
			button.innerHTML = originalButtonHTML;
		});
	};

	var setNotification = function(container, html, isSuccess){
		var notificationEls = container.getElementsByClassName('flowlu-notification');
		if(!notificationEls.length){
			var el = document.createElement('div');
			el.className = 'flowlu-notification';
			el.innerHTML = html;
			container.insertBefore(el, container.firstChild);
		}

		var notificationEl = container.getElementsByClassName('flowlu-notification')[0];
		if(!html){
			notificationEl.innerHTML = '';
		}else{
			notificationEl.innerHTML = html;
		}

	};

	var getAllInputs = function(container){
		return [].concat(
			Array.prototype.slice.call(container.getElementsByTagName('input')),
			Array.prototype.slice.call(container.getElementsByTagName('select')),
			Array.prototype.slice.call(container.getElementsByTagName('textarea'))
		);
	};

	var cleanForm = function(container){
		Array.prototype.forEach.call(getAllInputs(container), function(el){
			if(el.getAttribute('type') == 'hidden'){
				return;
			}

			if(el.tagName == 'SELECT'){
				return;
			}

			el.value = null;
		});
	};

	var formIsValid = function(container){
		var isValid = true;

		Array.prototype.forEach.call(getAllInputs(container), function(el){
			el.className = el.className.replace(' flowlu-input-error', '');
			if(el.getAttribute('required') === 'required' && !el.value.trim()){
				el.className = el.className + ' flowlu-input-error';
				isValid = false;
			}
		});

		return isValid;
	};

	var sendForm = function(container, options){
		blockUI(container);

		var _items = [];
		Array.prototype.forEach.call(getAllInputs(container), function(el){
			console.info(el.getAttribute('name'));
			console.info(el.value);
			if(el.getAttribute('name')){
				_items.push(el.getAttribute('name') + '=' + encodeURIComponent(el.value));
			}
		});

		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange = function(){
			if(xhr.readyState != 4) return false;

			var result = null;
			try{
				result = JSON.parse(xhr.responseText);
			}catch(e){

			}
			
			if(xhr.status === 200/* && !('error' in result)*/){
        setNotification(container, 'Success');
				if(result && '_resultMessage' in result && 'id' in result){
					setNotification(container, result['_resultMessage'].replace(/{{ID}}/g, result['id']));
				}else{
					setNotification(container, 'Success');
				}
				cleanForm(container);
			}else{
				if(result && 'description' in result){
					setNotification(container, result['description']);
				}else if(result && 'error' in result && 'error_msg' in result['error'] ){
					setNotification(container, result['error']['error_msg']);
				}else{
					setNotification(container, xhr.status);
				}
			}

			unblockUI(container);
		};

		setNotification(container);

		xhr.open('POST', '/submit/');
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

		xhr.send(_items.join('&'));
	};

	return {
		options: {},
		eventHandlers: {},

		init: function(options){
			var self = this;
			this.options = options;

			var forms = document.getElementsByClassName('flowlu-form');

			Array.prototype.forEach.call(forms, function(form){
				setNotification(form);
				var buttons = form.getElementsByClassName('flowlu-submit');
				Array.prototype.forEach.call(buttons, function(button){
					button.addEventListener('click', function(ev){						
						ev.preventDefault();
						
						if(formIsValid(form)){
							sendForm(form, self.options);
						}
					});
				});
			});
		},

		addEventHandler: function(eventName, callback){
			if(typeof callback !== 'function')
				return false;

			if(!this.eventHandlers.hasOwnProperty(eventName))
				this.eventHandlers[eventName] = [];

			this.eventHandlers[eventName].push(callback);
		}
	}
}();

if('flowlu_forms' in window){
	var flowluHost = '/';
	if (document.getElementById('flowlu_host')) {
		flowluHost = document.getElementById('flowlu_host').value;
	}

	var cssId = 'flowlucss';  // you could encode the css path itself to generate id..
	if (!document.getElementById(cssId)){
	    var head  = document.getElementsByTagName('head')[0];
	    var link  = document.createElement('link');
	    link.id   = cssId;
	    link.rel  = 'stylesheet';
	    link.type = 'text/css';
	    link.href = flowluHost + 'static/ext/flowlu.css';
	    link.media = 'all';
	    head.appendChild(link);
	}

	window['flowlu_forms'][0](flowluHost);
}