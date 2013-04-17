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
<!DOCTYPE html>
<html lang="en-us">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" media="(device-height: 568px)" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
		<title>Martha</title>
		<link type="text/css" rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400,300,700"/>
		<link rel="stylesheet" href="assets/martha.css">
		<link rel="shortcut icon" href="assets/temboo-drop-orange.png" type="image/png"/>
		<link rel="apple-touch-icon" href="assets/temboo-martha-touch-icon.png"/>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		<script src="assets/martha.js"></script>
	</head>
	<body>
		<form id="martha-form" action="" data-action="query/ajax.php" method="POST">
			<input type="text" name="query" autocomplete="off" placeholder="What do you want?" autofocus />
			<button type="submit" id="martha-submit"></button>
			<?php $tweet = $martha->getTweet(); ?>
			<?php if($tweet): ?>
				<div id="martha-twitter-share" style="display: block">
					<a href="https://twitter.com/share" class="twitter-share-button" data-url="https://temboo.com/examples" data-text="<?php echo htmlentities($tweet, ENT_COMPAT, 'UTF-8') ?>" data-align="right">Tweet</a>
					<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
				</div>
			<?php else: ?>
				<div id="martha-twitter-share">
					<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
				</div>
			<?php endif; ?>
		</form>
		<?php if(isset($query) && strlen($query) > 0): ?>
			<div id="martha-query"<?php if($tweet): ?> class="twitter-margin"<?php endif; ?>><?php echo htmlentities($query, ENT_NOQUOTES, 'UTF-8'); ?></div>
			<div id="martha-answer">
				<?php $messages = $martha->messages(); $message = array_shift($messages); ?>
				<div class="message answer"><?php echo $message; ?></div>
			</div>
			<div id="martha-results">
				<?php foreach($messages as $message): ?>
					<div class="message"><?php echo $message; ?></div>
				<?php endforeach; ?>
			</div>
		<?php else: ?>
			<div id="martha-query"<?php if($tweet): ?> class="twitter-margin"<?php endif; ?>></div>
			<div id="martha-answer" style="display: none"></div>
			<div id="martha-results" style="display: none"></div>
		<?php endif; ?>
		<div id="martha-welcome"<?php if(isset($query) && strlen($query) > 0): ?> style="display: none"<?php endif; ?>>
			<h1>Hi, I'm Martha!</h1>
			<p>I can help you answer questions and find things.</p>
			<p>Try asking me some stuff like this:</p>
			<div class="examples">
				<?php foreach($martha->suggest(3) as $suggestion): ?>
					<p><a href="?query=<?php echo htmlspecialchars($suggestion, ENT_COMPAT, 'UTF-8'); ?>" class="suggestion">"<?php echo htmlspecialchars($suggestion, ENT_NOQUOTES, 'UTF-8'); ?>"</a></p>
				<?php endforeach; ?>
			</div>
			<p>Or type <a href="?query=help" class="suggestion">"help"</a> for more assistance.</p>
		</div>
		<div id="spinner"></div>
	</body>
</html>