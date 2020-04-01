var Default = (function(){
	var _form = null;

	/**
	 * Display response message.
	 * @return void
	 */
	function _throwMessage(text, error){
		// create message and insert to dom
		var message = jQuery('<div></div>').attr({
			'id': 'message',
			'class': error ? 'error' : 'updated'
		}).append(
			jQuery('<p></p>').text(text)
		);

		jQuery('.wrap h2', '#wpbody-content').after(message);
	}

	/**
	 * Handle response.
	 * @return void|bool
	 */
	function _handleResponse(response){
		if(!response){ return false; }

		if(response.message){
			// remove previous messages
			jQuery('#message').remove();

			// throw message
			_throwMessage(response.message, response.error);
		}
	}

	/**
	 * build request query string
	 * based on form values
	 * @return String
	 */
	function _buildQuery(){
		var result = {};

		// get serialized data and restructure
		var data = jQuery('#form-container').serializeArray();
		for(var i in data){
			var item = data[i];
			result[item['name']] = item['value'];
		}

		return result;
	}

	/**
	 * request helper
	 * @return void
	 */
	function _request(data, callback){
		// show loader
		jQuery('#h2-loader').show();

		// make the request
		jQuery.post(

			smaily.ajax_url,
			{
				'action' : 'smaily_admin_save',
				'form_data' : jQuery.param(data)
			},
			function(response) {
				// handle response
				_handleResponse(response);

				// execute callback function
				if(typeof(callback) == 'function'){
					callback(response);
				}

				// hide loader
				jQuery('#h2-loader').hide();
			}, "json");
	}

	return {
		/**
		 * request api key validation
		 * @return void
		 */
		validateApiKey: function(){
			// build query
			var query = _buildQuery();
			query['op'] = 'validateApiKey';
			query['refresh'] = 1;

			// make the request
			_request(query, function(response){
				if(response.content){
					jQuery('#form-container').html(response.content);
				}
			});
		},

		/**
		 * remove api key request
		 * @return void
		 */
		removeApiKey: function(){
			// build query
			var query = {
				'op': 'removeApiKey',
				'refresh': 1
			};

			// make the request
			_request(query, function(response){
				if(response.content){
					jQuery('#form-container').html(response.content);
				}
			});
		},

		/**
		 * refresh newsletter subscription form
		 * reset back to default form
		 * @return void
		 */
		resetForm: function(){
			// build query
			var query = {
				'op': 'resetForm'
			};

			// make the request
			_request(query, function(response){
				// set textarea content
				var content = response.content;
				jQuery('#advanced-form').val(content);
			});
		},

		/**
		 * refresh autoresponders
		 * @return void
		 */
		refreshAutoresp: function(){
			// build query
			var query = {
				'op': 'refreshAutoresp',
				'refresh': 1
			};

			// make the request
			_request(query, function(response){
				if(response.content){
					jQuery('#form-container').html(response.content);
				}
			});
		},

		/**
		 * save form contents
		 * @return void
		 */
		save: function(){
			// build query
			var query = _buildQuery();
			query['op'] = 'save';

			// make the request
			_request(query);
		}
	}
})();

/**
 * Tabs
 * @param {Object} args
 */
var Tabs = (function(args){
	// default options
	var _options = {
		'target': '',
		'ajax': false
	}

	// extend options
	_options = jQuery.extend(_options, args);

	// check required target
	if(!_options.target || _options.target.length < 1){ return false; }

	// bind click event to target tabs
	jQuery(_options.target+' a').click(function(){
		_select(this);
	});

	// use location hash to select tab
	var hash = location.hash.length > 0 ? location.hash : '';
	if(hash.length > 0){
		var target = jQuery(_options.target+' a[href='+hash+']');
		_select(target);
	}

	/**
	 * select element
	 * @param {Object} element
	 */
	function _select(element){
		if(!element || element.length < 1){ return false; }
		var href = jQuery(element).attr('href');
		var hash = (href.length > 0 && /#/.test(href)) ? href.split('#')[1] : '';

		// exit if does not have hash
		if(hash.length < 1){ return false; }

		// reset target tabs selected state
		jQuery(_options.target+' a').removeClass('selected');

		// set this tab's state to selected
		jQuery(element).addClass('selected');

		// hide tabs and make clicked tab contents visible
		jQuery('*[id^=content\-]').addClass('hidden');
		jQuery('#content-'+hash).removeClass('hidden');
	}
});
