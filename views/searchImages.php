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
		<title><?php echo htmlentities($subject, ENT_NOQUOTES, 'UTF-8'); ?> images found by Martha</title>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	</head>
	<body>
<?php endif; ?>

<?php foreach($items as $image): ?>
	<?php if(isset($image->url_s) && $image->url_s): ?>
		<a href="http://www.flickr.com/photos/<?php echo htmlentities($image->owner, ENT_COMPAT, 'UTF-8'); ?>/<?php echo htmlentities($image->id, ENT_COMPAT, 'UTF-8'); ?>" target="_blank">
			<img src="<?php echo htmlentities(str_replace('http://', 'https://', $image->url_s), ENT_COMPAT, 'UTF-8') ?>" title="<?php echo htmlentities($image->title, ENT_COMPAT, 'UTF-8'); ?>" />
		</a>
	<?php endif; ?>
<?php endforeach; ?>

<?php if($this->_context != 'web'): ?>
	</body>
</html>
<?php endif; ?>