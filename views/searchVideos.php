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
		<title><?php echo htmlentities($subject, ENT_NOQUOTES, 'UTF-8'); ?> videos found by Martha</title>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	</head>
	<body>
<?php endif; ?>

<style>

.ytlinkcontainer {
    width: 120px;
    height: 110px;
    float: left;
    margin: 7px 33px 18px 0px;
}

/*Firefox specific spacing*/
@-moz-document url-prefix() {
	.ytlinkcontainer {
  	  width: 115px;
	}
}

.yttitle {
    font-size: 12px;
    font-weight:bold;
    margin:4px 0 2px;
    width:142px;
}

.ytthumb {
    width: 142px;
    height: 66px;
	background-position: 11px -12px;
	background-repeat: no-repeat;
	background-color: #ddd;
}

.ytthumb:hover {background-color:#ccc}

.ytstamp {
    padding-left: 10px;
    width: 40px;
    background: #000000;
    color: #ffffff;
}

.ytlinkcontainer .info {
	color: #333;
	font-size: 10px;
	width: 100px;
}

.ytviews {
}


.ytage {
	margin-top:-1px !important;
}

.yttime {
background-color: #000;
float: right;
margin: 49px 13px 0;
color: #fff;
font-weight: bold;
font-size: 10px;
text-align: right;
padding: 0px 3px 1px;
}

</style>

<?php foreach($items as $video): ?>

    <div class="ytlinkcontainer" >
        <a class="ytlink" href="http://<?php if($this->_context != 'web' || preg_match('/ipad|iphone|ipod|android/i', $_SERVER['HTTP_USER_AGENT'])) { echo "m"; } else { echo "www"; }?>.youtube.com/watch?v=<?php echo htmlentities($video->id, ENT_COMPAT, 'UTF-8' ); ?>" data-embedsrc="https://www.youtube.com/embed/<?php echo htmlentities($video->id, ENT_COMPAT, 'UTF-8' ); ?>" target="_blank">
            <div class="ytthumb" style="background-image:url(<?php echo str_replace('http://', 'https://', htmlentities($video->snippet->thumbnails->default->url, ENT_COMPAT, 'UTF-8' )); ?>);">
                <div class="yttime"><?php echo durationString($video->contentDetails->duration); ?></div>
            </div>
        </a>
        <div class="yttitle"><?php echo htmlentities($video->snippet->title, ENT_NOQUOTES, 'UTF-8')?></div>
        <div class="info">
            <div class="ytviews"><?php echo isset($video->statistics->viewCount) ? $video->statistics->viewCount . ' views': 'N/A'; ?></div>
            <div class="ytage"><?php echo ageString($video->snippet->publishedAt); ?></div>
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
	$('.yttitle').each(function(){
		$(this).text(elide($(this).text(), 22, 'end'));
	});
});

</script>

<?php if($this->_context != 'web'): ?>
	</body>
</html>
<?php endif; ?>
<?php

function ageString($dateString) {
    $formattedDate = str_replace("T", " ", $dateString);
    $formattedDate = str_replace("Z", "", $formattedDate);
    $uploadDate = new DateTime($formattedDate,  new DateTimeZone('UTC'));
    $now = new DateTime(null, new DateTimeZone('UTC'));
    $diff = $now->diff($uploadDate);
    $years = $diff->format('%y');
    $months = $diff->format('%m');
    $days = $diff->format('%d');

    if ($years > 0) {
        if ($years == 1) {
            return "One year ago";
        } else {
            return $years . " years ago";
        }
    } elseif ($months > 0) {
        if ($months == 1) {
            return "One month ago";
        } else {
            return $months . " months ago";
        }
    } elseif ($days > 0) {
        if ($days == 1) {
            return "One day ago";
        } else {
            return $days . " days ago";
        }
    } else {
        return "Less than a day ago";
    }
}

function durationString($duration) {
    if(preg_match('/^PT((?P<hours>\d+)H)?((?P<minutes>\d+)M)?((?P<seconds>\d+)S)?$/', $duration, $matches)) {
        $hours = isset($matches['hours']) && intval($matches['hours']) > 0 ?  sprintf('%02d', $matches['hours']) . ':' : '';
        $minutes = isset($matches['minutes']) ?  sprintf('%02d', $matches['minutes']) : '00';
        $seconds = isset($matches['seconds']) ?  sprintf('%02d', $matches['seconds']) : '00';
        return $hours . $minutes . ':' . $seconds;
    } else {
        return '00:00';
    }
}

?>