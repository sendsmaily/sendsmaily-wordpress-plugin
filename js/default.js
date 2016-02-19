/**
 * This file is part of Sendsmaily Wordpress plugin.
 * 
 * Sendsmaily Wordpress plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * Sendsmaily Wordpress plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with Sendsmaily Wordpress plugin.  If not, see <http://www.gnu.org/licenses/>.
 */

var Default = (function(){
	var _form = null;
	
	/**
	 * display response message
	 * @param String text
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
		
		jQuery('#wpbody-content .wrap h2').after(message);
	}
	
	/**
	 * handle response
	 * @param Object response
	 * @return void
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
		jQuery.post(SS_PLUGIN_URL + 'action.php', data, function(response){
			// handle response
			_handleResponse(response);
			
			// execute callback function
			if(typeof(callback) == 'function'){
				callback(response);
			}
			
			// hide loader
			jQuery('#h2-loader').hide();
		}, 'json');
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