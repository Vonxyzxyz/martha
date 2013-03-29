/**
 * Martha
 *
 * An artificially intelligent-ish personal assistant built on the
 * Temboo API library.
 *
 * PHP version 5
 *
 * LICENSE: Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author     Nick Blanchard-Wright <nick.wright@temboo.com>
 * @copyright  2013 Temboo, Inc.
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link       http://www.temboo.com
 * @package    Martha
 * @subpackage Assets
 */

$(function(){
	var $form = $('#martha-form');
	var $input = $('input[name=query]', $form);
	var $query = $('#martha-query');
	var $answer = $('#martha-answer');
	var $results = $('#martha-results');
	var $welcome = $('#martha-welcome');
	var $spinner = $('#spinner');
	$input.placeholder();
	$form.submit(function(e){
		e.preventDefault();
		$input.attr('placeholder', 'Need something else?');
		$answer.hide().html('');
		$results.hide().html('');
		$spinner.fadeIn('fast');
		$welcome.hide();
		var query = $input.val();
		$input.attr('disabled', true);
		if( /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ) {
			$input.blur();
		}
		$query.text('');
		if(query.length < 1) {
			$.post('query/suggestions.php', {}, function(results){
				$input.attr('disabled', false);
				if(results.suggestions && results.suggestions.length) {
					var $examples = $welcome.find('.examples').html('');
					for(var i = 0, j = results.suggestions.length; i < j; i++) {
						var suggestion = results.suggestions[i];
						$('<p>').html('<a href="?query=' + suggestion + '" class="suggestion">"' + suggestion + '"</a>').appendTo($examples);
					}
				}
				$spinner.fadeOut('fast', function(){
					$welcome.fadeIn('fast');
				});
			}, 'json');
			return;
		}
		$.post($form.data('action'), { query: query }, function(results){
			$input.val('').attr('disabled', false);
			$('<div class="message answer"/>').html(results.messages[0]).appendTo($answer);
			if(results.messages && results.messages.length) {
				for(var i = 1, j = results.messages.length; i < j; i++) {
					var message = results.messages[i];
					$('<div class="message"/>').html(message).appendTo($results);
				}
			}
			$spinner.fadeOut('fast', function(){
				$query.text(query);
				$answer.fadeIn('fast');
				$results.fadeIn('fast');
			});
		}, 'json');
	});

	$(document).on('click', 'a.suggestion', function(e){
		e.preventDefault();
		$input.val($(this).text().replace(/\"/g, ''));
		$form.submit();
	});
});

/*! http://mths.be/placeholder v2.0.7 by @mathias */
;(function(f,h,$){var a='placeholder' in h.createElement('input'),d='placeholder' in h.createElement('textarea'),i=$.fn,c=$.valHooks,k,j;if(a&&d){j=i.placeholder=function(){return this};j.input=j.textarea=true}else{j=i.placeholder=function(){var l=this;l.filter((a?'textarea':':input')+'[placeholder]').not('.placeholder').bind({'focus.placeholder':b,'blur.placeholder':e}).data('placeholder-enabled',true).trigger('blur.placeholder');return l};j.input=a;j.textarea=d;k={get:function(m){var l=$(m);return l.data('placeholder-enabled')&&l.hasClass('placeholder')?'':m.value},set:function(m,n){var l=$(m);if(!l.data('placeholder-enabled')){return m.value=n}if(n==''){m.value=n;if(m!=h.activeElement){e.call(m)}}else{if(l.hasClass('placeholder')){b.call(m,true,n)||(m.value=n)}else{m.value=n}}return l}};a||(c.input=k);d||(c.textarea=k);$(function(){$(h).delegate('form','submit.placeholder',function(){var l=$('.placeholder',this).each(b);setTimeout(function(){l.each(e)},10)})});$(f).bind('beforeunload.placeholder',function(){$('.placeholder').each(function(){this.value=''})})}function g(m){var l={},n=/^jQuery\d+$/;$.each(m.attributes,function(p,o){if(o.specified&&!n.test(o.name)){l[o.name]=o.value}});return l}function b(m,n){var l=this,o=$(l);if(l.value==o.attr('placeholder')&&o.hasClass('placeholder')){if(o.data('placeholder-password')){o=o.hide().next().show().attr('id',o.removeAttr('id').data('placeholder-id'));if(m===true){return o[0].value=n}o.focus()}else{l.value='';o.removeClass('placeholder');l==h.activeElement&&l.select()}}}function e(){var q,l=this,p=$(l),m=p,o=this.id;if(l.value==''){if(l.type=='password'){if(!p.data('placeholder-textinput')){try{q=p.clone().attr({type:'text'})}catch(n){q=$('<input>').attr($.extend(g(this),{type:'text'}))}q.removeAttr('name').data({'placeholder-password':true,'placeholder-id':o}).bind('focus.placeholder',b);p.data({'placeholder-textinput':q,'placeholder-id':o}).before(q)}p=p.removeAttr('id').hide().prev().attr('id',o).show()}p.addClass('placeholder');p[0].value=p.attr('placeholder')}else{p.removeClass('placeholder')}}}(this,document,jQuery));