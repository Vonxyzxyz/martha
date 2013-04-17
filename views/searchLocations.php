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
		<title><?php echo htmlentities($subject, ENT_NOQUOTES, 'UTF-8'); ?> location found by Martha</title>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	</head>
	<body>
<?php endif; ?>

<iframe class="googlemaps" width="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?f=q&source=s_q&hl=en&geocode=&q=<?php echo urlencode($location->result->formatted_address) ?>&ie=UTF8&z=12&t=m&iwloc=near&output=embed"></iframe><br /><small><a href="http://maps.google.com/maps?f=q&source=s_q&hl=en&geocode=&q=<?php echo urlencode($location->result->formatted_address) ?>&ie=UTF8&z=12&t=m&iwloc=near" target="_blank">View Larger Map</a></small>

<?php if($this->_context != 'web'): ?>
	</body>
</html>
<?php endif; ?>