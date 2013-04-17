<?php

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
 * @link       http://temboo.com
 * @package    Martha
 * @subpackage Views
 */

?>
<?php if($this->_context != 'web'): ?>
<!DOCTYPE html>
<html lang="en-us">
    <head>
        <meta charset="utf-8">
		<title>Worthy causes found by Martha</title>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	</head>
	<body>
<?php endif; ?>

<style>

.dclinkcontainer {
    width: 125px;
    height: 170px;
    float: left;
    margin: 7px 35px 18px 0px;
}

@-moz-document url-prefix() {
	.dclinkcontainer {
  	  width: 120px;
	}
}

.dctitle {
    font-size: 12px;
    font-weight:bold;
    margin:4px 0 2px;
    width:150px;
}

.dcthumb {
    display: block;
    max-width: 125px !important;
    max-height: 125px !important;
    width: 125px;
    height: 125px;
    border: none;
    margin: 0;
    background-color:#ddd;
    padding: 0 10px;
}

.dcthumb:hover {
	background-color:#ccc;
}

.dclinkcontainer .info {
	color: #333;
	font-size: 10px;
	width: 100px;
}

.dcschool {
	width:150px;
}


.dcgrade {
	margin-top:-1px !important;
}

</style>

<?php foreach($proposals as $proposal): ?>

	<div class="dclinkcontainer" >
		<a class="dclink" href="<?php echo htmlentities($proposal->proposalURL, ENT_COMPAT, 'UTF-8' ); ?>" target="_blank">
			<img class="dcthumb" src="<?php echo htmlentities($proposal->imageURL, ENT_COMPAT, 'UTF-8' ); ?>" />
		</a>
		<div class="dctitle"><?php echo htmlentities(html_entity_decode($proposal->title, ENT_QUOTES, 'UTF-8'), ENT_NOQUOTES, 'UTF-8')?></div>
		<div class="info">
			<div class="dcschool"><?php echo htmlentities(html_entity_decode($proposal->schoolName, ENT_QUOTES, 'UTF-8'), ENT_NOQUOTES, 'UTF-8') ?></div>
			<div class="dcgrade"><?php echo htmlentities($proposal->gradeLevel->name, ENT_NOQUOTES, 'UTF-8') ?></div>
		</div>
	</div>

<?php endforeach; ?>

<script>

var elide = function(str, maxLength, location, indicator){
	var indicator	= indicator || '...',
		len			= str.length,
		location	= location || 'end';

	if(len > maxLength){
		var diff = len - maxLength + indicator.length;
		// Remove/replace chars in requested portion
		switch(location){
			case 'start':
				str = indicator + str.substr(diff);
				break;
			case 'middle':
				var offset = Math.ceil((len - diff) / 2);
				str	= str.substr(0, offset) + indicator + str.substr(offset + diff);
				break;
			case 'end':
				str = str.substr(0, maxLength - indicator.length) + indicator;
				break;
		}
	}

	return str;
}

$(function(){
	$('.dctitle').each(function(){
		$(this).text(elide($(this).text(), 23, 'end'));
	});
	$('.dcschool').each(function(){
		$(this).text(elide($(this).text(), 28, 'end'));
	});
});

</script>

<?php if($this->_context != 'web'): ?>
	</body>
</html>
<?php endif; ?>