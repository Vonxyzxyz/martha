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
 * @link       http://www.temboo.com
 * @package    Martha
 * @subpackage Core
 */


/**
 * Configuration -- edit this with your Temboo and API credentials.
 *
 * See file for URLs to get new credentials.
 */
require_once('config.php');


/**
 * Temboo PHP SDK. Download from: https://temboo.com/download
 */
require_once('php-sdk/src/temboo.php');


/**
 * Martha is a friendly and helpful bot who will answer your questions via web,
 * SMS, or voice call. She does this by querying many different APIs in one
 * normalized fashion with the Temboo library.
 */
class Martha {

    /**
     * Authenticated session for executing Temboo choreographies.
     *
     * @var Temboo_Session
     */
    protected $_tembooSession;

    /**
     * The user's medium for interaction with Martha. E.g. "web" (default), "sms", "voice".
     *
     * @var string
     */
    protected $_context = 'web';

    /**
     * The list of messages to send to the user.
     *
     * @var array
     */
    protected $_messages = array();

    /**
     * The list of resource types (with synonyms) Martha knows how to search for, keyed by search method.
     *
     * @var array
     */
    protected $_resourceTypes = array(
       'searchImages' => array('image', 'photo', 'picture'),
       'searchVideos' => array('video', 'movie', 'film'),
       'searchTweets' => array('tweet', 'twitter', 'toot')
    );


    /**
     * Instantiate an instance of Martha with a Temboo connection.
     *
     * @param string $context the user's medium for interaction with Martha. E.g. "web" (default), "sms", "voice"
     * @return Martha new Martha instance
     */
    public function __construct($context = 'web') {
        // Instantiate Temboo session...
        $this->_tembooSession = new Temboo_Session(TEMBOO_ACCOUNT, TEMBOO_APP_NAME, TEMBOO_APP_KEY);

        $this->_context = $context;

        // Twitter's TOS doesn't allow storing Tweet data, so we can only
        // supply it for inline results, not SMS/voice where results are
        // uploaded to S3/Dropbox
        if($context != 'web') {
            unset($this->_resourceTypes['searchTweets']);
        }
    }


    /**
     * Search images.
     *
     * Choreos used in this method:
     * https://temboo.com/library/Library/Flickr/Photos/Search/
     *
     * @param string $subject the thing to search for, e.g. "cats"
     * @param int $limit (optional) max number of results
     * @return string a url to a rendered list of results, or an error message on failure
     * @throws Temboo_Exception should unforeseen misfortunes befall us
     */
    protected function searchImages($subject, $limit = false) {
        if(!$limit) { $limit = 50; }

        // Instantiate the Choreo, using a previously instantiated Temboo_Session object
        $search = new Flickr_Photos_Search($this->_tembooSession);

        // Get an input object for the Choreo
        $searchInputs = $search->newInputs();

        // Set credential to use for execution
        $searchInputs->setCredential(TEMBOO_FLICKR_CREDENTIAL);

        // Set inputs
        $searchInputs->setText($subject)->setMedia("photos")->setResponseFormat("json")->setExtras('url_s');
        if($limit) {
            $searchInputs->setPerPage($limit);
        }

        // Execute Choreo and get results
        $searchResults = $search->execute($searchInputs)->getResults();

        $response = json_decode($searchResults->getResponse());

        // Render the list
        return $this->renderListResults(__FUNCTION__, $subject, $response->photos->photo);
    }


    /**
     * Search videos.
     *
     * Choreos used in this method:
     * https://temboo.com/library/Library/YouTube/SearchVideos/
     *
     * @param string $subject the thing to search for, e.g. "cats"
     * @param int $limit (optional) max number of results
     * @return string a url to a rendered list of results, or an error message on failure
     * @throws Temboo_Exception should unforeseen misfortunes befall us
     */
    protected function searchVideos($subject, $limit = false) {
        if(!$limit) { $limit = 20; }

        // Instantiate the Choreo, using a previously instantiated Temboo_Session object
        $searchVideos = new YouTube_SearchVideos($this->_tembooSession);

        // Get an input object for the Choreo
        $searchVideosInputs = $searchVideos->newInputs();

        // Set inputs
        $searchVideosInputs->setQuery($subject)->setResponseFormat("json");
        if($limit) {
            $searchVideosInputs->setMaxResults($limit);
        }

        // Execute Choreo and get results
        $searchVideosResults = $searchVideos->execute($searchVideosInputs)->getResults();

        $response = json_decode($searchVideosResults->getResponse());

        // Render the list
        return $this->renderListResults(__FUNCTION__, $subject, $response->feed->entry);
    }


    /**
     * Search tweets.
     *
     * Choreos used in this method:
     * https://temboo.com/library/Library/Twitter/Search/Tweets/
     *
     * @param string $subject the thing to search for, e.g. "cats"
     * @param int $limit (optional) max number of results
     * @return string a url to a rendered list of results, or an error message on failure
     * @throws Temboo_Exception should unforeseen misfortunes befall us
     */
    protected function searchTweets($subject, $limit = false) {
        if(!$limit) { $limit = 50; }

        // Instantiate the Choreo, using a previously instantiated Temboo_Session object
        $tweets = new Twitter_Search_Tweets($this->_tembooSession);

        // Get an input object for the Choreo
        $tweetsInputs = $tweets->newInputs();

        // Set credential to use for execution
        $tweetsInputs->setCredential(TEMBOO_TWITTER_CREDENTIAL);

        // Set inputs
        $tweetsInputs->setQuery($subject);
        if($limit) {
            $tweetsInputs->setCount($limit);
        }

        // Execute Choreo and get results
        $tweetsResults = $tweets->execute($tweetsInputs)->getResults();

        $response = json_decode($tweetsResults->getResponse());

        // Render the list
        return $this->renderListResults(__FUNCTION__, $subject, $response->statuses);
    }


    /**
     * Search for a location.
     *
     * Choreos used in this method:
     * https://temboo.com/library/Library/Google/Geocoding/GeocodeByAddress/
     *
     * @param string $subject the location to search for, e.g. "world's largest ball of twine"
     * @return string a url to a rendered list of results, or an error message on failure
     * @throws Temboo_Exception should unforeseen misfortunes befall us
     */
    public function searchLocation($subject) {
        // Instantiate the Choreo, using a previously instantiated Temboo_Session object
        $geocodeByAddress = new Google_Geocoding_GeocodeByAddress($this->_tembooSession);

        // Get an input object for the Choreo
        $geocodeByAddressInputs = $geocodeByAddress->newInputs();

        // Set inputs
        $geocodeByAddressInputs->setAddress($subject);

        // Execute Choreo and get results
        $geocodeByAddressResults = $geocodeByAddress->execute($geocodeByAddressInputs)->getResults();

        $response = simplexml_load_string($geocodeByAddressResults->getResponse());

        return $this->renderLocationResult($subject, $response);
    }


    /**
     * Search for a word definition.
     *
     * Choreos used in this method:
     * https://temboo.com/library/Library/Wordnik/Word/GetDefinitions/
     *
     * @param string $subject the word to search for, e.g. "life"
     * @return string a url to a rendered list of results, or an error message on failure
     * @throws Temboo_Exception should unforeseen misfortunes befall us
     */
    public function searchDefinition($subject) {
        // Instantiate the Choreo, using a previously instantiated Temboo_Session object
        $getDefinitions = new Wordnik_Word_GetDefinitions($this->_tembooSession);

        // Get an input object for the Choreo
        $getDefinitionsInputs = $getDefinitions->newInputs();

        // Set credential to use for execution
        $getDefinitionsInputs->setCredential(TEMBOO_WORDNIK_CREDENTIAL);

        // Set inputs
        $getDefinitionsInputs->setWord($subject)->setCannonical(true)->setLimit("1");

        // Execute Choreo and get results
        $getDefinitionsResults = $getDefinitions->execute($getDefinitionsInputs)->getResults();

        $response = json_decode($getDefinitionsResults->getResponse());

        // If we got nothing, fall back to DuckDuckGo/Wikipedia search
        if(count($response) < 1) {
            return $this->searchAnswers($subject);
        }

        return $this->renderDefinitionResult($subject, $response);
    }


    /**
     * Search for a phrase definition or answer to abstract question.
     *
     * Choreos used in this method:
     * https://temboo.com/library/Library/DuckDuckGo/Query/
     *
     * @param string $subject the phrase to search for, e.g. "Tim Berners-Lee"
     * @return string a url to a rendered list of results, or an error message on failure
     * @throws Temboo_Exception should unforeseen misfortunes befall us
     */
    public function searchAnswers($subject) {
        // Instantiate the Choreo, using a previously instantiated Temboo_Session object
        $query = new DuckDuckGo_Query($this->_tembooSession);

        // Get an input object for the Choreo
        $queryInputs = $query->newInputs();

        // Set inputs
        $queryInputs->setNoHTML("1")->setFormat("json")->setNoRedirect("1")->setSkipDisambiguation("1");

        // First we'll try getting a straight definition from DuckDuckGo
        $queryInputs->setQuery("define " . $subject);

        // Execute Choreo and get results
        $queryResults = $query->execute($queryInputs)->getResults();

        $definitionResponse = json_decode($queryResults->getResponse());

        // Now for good measure we'll ask for a Wikipedia url
        // We can re-use the input and choreo objects
        if($this->_context != 'web' || !$definitionResponse->AbstractText) {
            // If this request came over sms/voice, or we'll be returning iframe wikipedia results
            // (because no definition was found above), get the mobile version of wikipedia
            $queryInputs->setQuery("!wm " . $subject);
        } else {
            // Full desktop wikipedia if we're just providing a vanilla link to a web browser
            $queryInputs->setQuery("!w " . $subject);
        }

        // Execute Choreo and get results
        $queryResults = $query->execute($queryInputs)->getResults();

        $wikipediaResponse = json_decode($queryResults->getResponse());

        return $this->renderAnswerResult($subject, $definitionResponse, $wikipediaResponse);

    }


    /**
     * Create a file on S3 or Dropbox (in that order) and return a public URL
     *
     * Choreos used in this method:
     * https://temboo.com/library/Library/Amazon/S3/PutObject/
     * https://temboo.com/library/Library/Dropbox/UploadFile/
     * https://temboo.com/library/Library/Dropbox/GetShareableLink/
     *
     * @param string $filename filename to create
     * @param string $contents file contents to upload
     * @return string public url for file, or false if upload failed
     */
    protected function shareFile($filename, $contents) {
        if(defined('TEMBOO_S3_CREDENTIAL') && TEMBOO_S3_CREDENTIAL && defined('MARTHA_S3_BUCKET') && MARTHA_S3_BUCKET) {
            try {
                // Instantiate the Choreo, using a previously instantiated Temboo_Session object
                $putObject = new Amazon_S3_PutObject($this->_tembooSession);

                // Get an input object for the Choreo
                $putObjectInputs = $putObject->newInputs();

                // Set credential to use for execution
                $putObjectInputs->setCredential(TEMBOO_S3_CREDENTIAL);

                // Set inputs
                $putObjectInputs->setBucketName(MARTHA_S3_BUCKET)->setFileName($filename)->setFileContents(base64_encode($contents));

                // Execute Choreo and get results
                $putObjectResults = $putObject->execute($putObjectInputs)->getResults();

                if(defined('MARTHA_URL') && MARTHA_URL) {
                    $url = MARTHA_URL;
                    if(!preg_match('/(\/|\.php)$/', $url)) {
                        $url = $url . '/';
                    }
                } else {
                    $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
                    $url = $protocol . $_SERVER['SERVER_NAME'] . preg_replace('/\/(query\/)?[a-z]+.php$/i', '/index.php', $_SERVER['REQUEST_URI']);
                }
                return $url . '?answer=' . urlencode($filename);

            } catch(Temboo_Exception $e) {
                error_log(__METHOD__ . ' failed with ' . get_class($e) . ': ' . $e->getMessage());
                // Do nothing, try again with Dropbox below.
            }
        }

        // S3 was unavailable or failed. Try Dropbox...

        if(defined('TEMBOO_DROPBOX_CREDENTIAL') && TEMBOO_DROPBOX_CREDENTIAL) {
            try {
                // Instantiate the Choreo, using a previously instantiated Temboo_Session object
                $uploadFile = new Dropbox_UploadFile($this->_tembooSession);

                // Get an input object for the Choreo
                $uploadFileInputs = $uploadFile->newInputs();

                // Set credential to use for execution
                $uploadFileInputs->setCredential(TEMBOO_DROPBOX_CREDENTIAL);

                // Set inputs
                $uploadFileInputs->setFileName($filename)->setFileContents(base64_encode($contents))->setResponseFormat('json');

                // Execute Choreo and get results
                $uploadFileResults = $uploadFile->execute($uploadFileInputs);


                // Instantiate the Choreo, using a previously instantiated Temboo_Session object
                $getShareableLink = new Dropbox_GetShareableLink($this->_tembooSession);

                // Get an input object for the Choreo
                $getShareableLinkInputs = $getShareableLink->newInputs();

                // Set credential to use for execution
                $getShareableLinkInputs->setCredential(TEMBOO_DROPBOX_CREDENTIAL);

                // Set inputs
                $getShareableLinkInputs->setPath($filename)->setResponseFormat('json');

                // Execute Choreo and get results
                $getShareableLinkResults = $getShareableLink->execute($getShareableLinkInputs)->getResults();

                $response = json_decode($getShareableLinkResults->getResponse());

                if(isset($response->url)) {
                    return str_replace('www.dropbox.com', 'dl.dropbox.com', $response->url);
                }

            } catch(Temboo_Exception $e) {
                error_log(__METHOD__ . ' failed with ' . get_class($e) . ': ' . $e->getMessage());
                // Do nothing, return false below.
            }
        }

        return false;
    }


    /**
     * Serve a file from S3. We route through Martha, so the S3 bucket need not be public.
     *
     * Choreos used in this method:
     * https://temboo.com/library/Library/Amazon/S3/GetBase64EncodedObject/
     *
     * @param string $filename filename to serve
     * @return string contents of file, or false if not found
     */
    public function serveFile($filename) {
        if(defined('TEMBOO_S3_CREDENTIAL') && TEMBOO_S3_CREDENTIAL && defined('MARTHA_S3_BUCKET') && MARTHA_S3_BUCKET) {
            try {
                // Instantiate the Choreo, using a previously instantiated Temboo_Session object;
                $getBase64EncodedObject = new Amazon_S3_GetBase64EncodedObject($this->_tembooSession);

                // Get an input object for the Choreo
                $getBase64EncodedObjectInputs = $getBase64EncodedObject->newInputs();

                // Set credential to use for execution
                $getBase64EncodedObjectInputs->setCredential(TEMBOO_S3_CREDENTIAL);

                // Set inputs
                $getBase64EncodedObjectInputs->setBucketName(MARTHA_S3_BUCKET)->setFileName($filename);

                // Execute Choreo and get results
                $getBase64EncodedObjectResults = $getBase64EncodedObject->execute($getBase64EncodedObjectInputs)->getResults();

                return base64_decode($getBase64EncodedObjectResults->getResponse());

            } catch(Temboo_Exception $e) {
                error_log(__METHOD__ . ' failed with ' . get_class($e) . ': ' . $e->getMessage());
                // Do nothing, return false below.
            }
        }
        return false;
    }


    /**
     * Shorten a URL, if possible.
     *
     * Choreos used in this method:
     * https://temboo.com/library/Library/Bitly/Links/ShortenURL/
     *
     * @param string $url a long url
     * @return string a short url, or the original if shortening failed
     */
    public function shortenUrl($url) {
        if(defined('TEMBOO_BITLY_CREDENTIAL') && TEMBOO_BITLY_CREDENTIAL) {
            try{
                // Instantiate the Choreo, using a previously instantiated Temboo_Session object
                $shortenURL = new Bitly_Links_ShortenURL($this->_tembooSession);

                // Get an input object for the Choreo
                $shortenURLInputs = $shortenURL->newInputs();

                // Set credential to use for execution
                $shortenURLInputs->setCredential(TEMBOO_BITLY_CREDENTIAL)->setResponseFormat('txt');

                // Set inputs
                $shortenURLInputs->setLongURL($url);

                // Execute Choreo and get results
                $shortenURLResults = $shortenURL->execute($shortenURLInputs)->getResults();

                return $shortenURLResults->getResponse();

            } catch(Temboo_Exception $e) {
                error_log(__METHOD__ . ' failed with ' . get_class($e) . ': ' . $e->getMessage());
                // Do nothing. Return original url below.
            }
        }

        return $url;
    }


    /**
     * Send an SMS text message.
     *
     * Choreos used in this method:
     * https://temboo.com/library/Library/Twilio/SendSMS/
     *
     * @param string $to a phone number to text
     * @param string $message the message to send
     * @throws Temboo_Exception should unforeseen misfortunes befall us
     */
    public function sendSMS($to, $message) {
        // Instantiate the Choreo, using a previously instantiated Temboo_Session object
        $sendSMS = new Twilio_SendSMS($this->_tembooSession);

        // Get an input object for the Choreo
        $sendSMSInputs = $sendSMS->newInputs();

        // Set credential to use for execution
        $sendSMSInputs->setCredential(TEMBOO_TWILIO_CREDENTIAL);

        // Set inputs
        $sendSMSInputs->setBody($message)->setTo($to)->setFrom(TWILIO_SMS_NUMBER);

        // Execute Choreo, discard results
        $sendSMS->execute($sendSMSInputs);
    }


    /**
     * Render a list of a given resource (images, tweets, etc.)
     *
     * @param string $searchMethod the search method that called renderListResults, from which we can infer the resource type
     * @param string $subject the search terms use to find the items in the list ("cats")
     * @param array $items array of resources
     */
    protected function renderListResults($searchMethod, $subject, $items = array()) {

        // Pick a random alias of this resource type
        $type = $this->randomItem($this->_resourceTypes[$searchMethod]);
        if(count($items) != 1) {
            $type = $type . 's'; // And now we can never search for irregular nouns!
        }

        // If it's an empty list, just say sorry.
        if(count($items) < 1) {
            return $this->say($this->foundNone() . ' ' . $subject . ' ' . $type . '.');
        }

        // Cheat to render the template to a string.
        ob_start();
        require('views/'.$searchMethod.'.php');
        $html = ob_get_clean();


        // For SMS or voice requests, we return just a URL to the rendered results on Dropbox/S3.
        // For web requests, HTML results are inline.
        if($this->_context != 'web') {
            // Upload to Dropbox or S3
            $filename = uniqid('martha-'.$type.'-', true) . '.html';
            $url = $this->shareFile($filename, $html);

            // If a page was successfully uploaded, link to it for full results (and shorter text messages!)
            if($url) {
                $url = $this->shortenUrl($url);
                return $this->say($this->foundSome() . ' ' . count($items). ' ' . $subject . ' ' . $type . ': ' . $url);
            }

            return $this->error();
        } else {
            $this->say($this->foundSome() . ' ' . count($items). ' ' . $subject . ' ' . $type . ':');
            return $this->say($html, true);
        }
    }


    /**
     * Render a location.
     *
     * @param string $subject the search terms use to find the location ("world's largest ball of twine")
     * @param array $location location result
     */
    protected function renderLocationResult($subject, $location) {
        if($location->status != 'OK') {
            return $this->say($this->foundNone() . ' ' . $subject . ' locations.');
        }

        $name = $location->result->formatted_address;
        if(strlen($name) > 80 && isset($location->result->address_component[0])) {
            $name = $location->result->address_component[0]->short_name;
        }

        // For SMS or voice requests, we return just a URL to google maps for the normalized address.
        // For web requests, map results are inline.
        if($this->_context != 'web') {
            $url = 'http://maps.google.com/maps?f=q&source=s_q&hl=en&geocode=&q=' . urlencode($location->result->formatted_address) . '&ie=UTF8&z=12&t=m&iwloc=near';
            $url = $this->shortenUrl($url);
            return $this->say($this->foundSome() . ' ' . $name . ': ' . $url);
        } else {
            // Cheat to render the template to a string.
            ob_start();
            require('views/searchLocations.php');
            $html = ob_get_clean();

            $this->say($this->foundSome() . ' ' . $name . ':');
            return $this->say($html, true);
        }
}


    /**
     * Render a definition.
     *
     * @param string $subject the search term use to find the definition ("life")
     * @param array $definition definition result
     */
    protected function renderDefinitionResult($subject, $definition) {

        $this->say($this->foundSome() . ' a definition of ' . $definition[0]->word . ':');

        if($this->_context != 'web') {
            $this->say($definition[0]->text . ' (wordnik.com)');
        } else {
             $this->say($definition[0]->text);
             $this->say('<p class="powered-by"><a href="http://wordnik.com/words/' . htmlentities($definition[0]->word, ENT_COMPAT, 'UTF-8') . '" target="_blank"><img src="assets/examples-poweredby-wordnik.png" alt="Powered by Wordnik" /></a></p>', true);
        }
    }


    /**
     * Render an answer to an abstraction question/phrase definition.
     *
     * @param string $subject the search terms used to find the answer ("Tim Berners-Lee")
     * @param array $definition definition result
     * @param array $wikipedia wikipedia result
     */
    protected function renderAnswerResult($subject, $definition, $wikipedia) {
        if($definition->AbstractText) {
            if($definition->Heading) {
                $subject = $definition->Heading;
            }
            $this->say('Let me tell you about ' . $subject . ':');

            $this->say($definition->AbstractText);
            if($this->_context != 'web') {
                $this->say('Read more at Wikipedia: ' . $this->shortenUrl($wikipedia->Redirect));
            } else {
                $this->say('<p><a href="' . $wikipedia->Redirect . '" target="_blank" class="powered-by">Read more on Wikipedia</a></p>', true);
            }
        } else {
            if($this->_context != 'web') {
                $this->say($this->notSure() . " Let's ask Wikipedia: " . $this->shortenUrl($wikipedia->Redirect));
            } else {
                $this->say($this->notSure() . " Let's ask Wikipedia:");
                $this->say('<iframe src="' . str_replace('http://', 'https://', $wikipedia->Redirect) . '" class="wikipedia"></iframe>', true);
            }
        }
    }

    /**
     * Queue a new message to send to the user.
     *
     * @param string $message the next message
     * @param bool $allowHtml whether to allow unescaped HTML in the message (default false).
     */
    public function say($message, $allowHtml = false) {
        if(!$allowHtml) {
            $message = htmlspecialchars($message, ENT_NOQUOTES, 'UTF-8');
        }
        $this->_messages[] = $message;
    }


    /**
     * Get the list of messages to send to the user.
     *
     * @return array messages to the user
     */
    public function messages() {
        return $this->_messages;
    }


    /**
     * Parse a user's query, routing it to the right choreos, and queueing up responses.
     *
     * Problem: I am not an expert in natural language processing.
     * I know! I'll use regular expressions!
     * Now I have two problems.
     *
     * @param string $query a user's query
     */
    public function query($query) {

        if(strlen($query) < 1) {
            return;
        }

        if($query == '?') {
            return $this->help();
        }

        // Some basic scrubbing to make the bigger regexes below simpler...

        // Remove punctuation.
        $query = str_replace(array('.', ',', ':', '!', '?', '"', "'"), '', $query);

        // Compact all types and lengths of whitespace to single spaces. Kills newlines.
        $query = trim(preg_replace('/\s+/', ' ', $query));

        // Remove any superfluous addressing of the bot. Tolerates one word before or aft ("dear martha", "martha dear").
        $query = preg_replace('/^(\w+ )?martha\S? /i', '', $query);
        $query = preg_replace('/ martha\S?( \w+)?$/i', '', $query);

        // Is someone just being friendly?
        if(preg_match('/^(hi|hello|hey|howdy|good (morning|afternoon|evening|day))$/i', $query)) {
            return $this->say($this->greet());
        }

        // Remove any other polite pre/postamble.
        $query = preg_replace('/^((please|hey|hi|hello|kindly|pray|help|go|run|do|perform|will\ you|would\ you|can\ i\ have|may\ i\ have|
                                could\ you|can\ you|quickly|immediately|try\ to|try)(\ for)?(\ me|\ us)?(\ an?)?\ )*/ix', '', $query);
        $query = preg_replace('/(\ (please|right\ now|now|quickly|immediately|stat|thanks|thank\ you|for\ me|for\ us))*$/ix', '', $query);

        // Remove superfluous language specifying a search/request.
        $query = trim(preg_replace('/^((i\ )?(we\ )?(find|get|search|bring|show|give|list|display|fetch|query|look|
                                want|need|gett)(you\ to|ing)?(\ for)?(\ me|\ us)?\ )*/ix', '', $query));


        // If nothing survived all that, just say hello.
        if(strlen($query) < 1) {
            return $this->say($this->greet());
        }

        // Some simple canned responses
        switch(strtolower($query)) {
            case 'who are you':
                return $this->say("I am Martha, ask me anything!");
                break;
            case 'where are you':
                return $this->say("I exist in distributed form, scattered throughout a series of tubes, answering questions and finding things for you.");
                break;
            case 'who made you':
                return $this->say("I was lovingly hand-coded by the software artisans at Temboo.");
                break;
            case 'what is the best api':
                return $this->say("I use Temboo so they all look the same to me :-)");
                break;
            case 'are you human':
                return $this->say("I find it's best to leave them guessing.");
                break;
            case 'what are you wearing':
                return $this->say("An elegant pantsuit woven from a single string of JSON");
                break;
            case 'rock':
            case 'paper':
            case 'scissors':
                return $this->rps($query);
                break;
        }

        if(preg_match('/global.?thermonuclear.?war/i', $query)) {
            return $this->say("How about a nice game of chess?");
        }

        // A plea for help?
        if(preg_match('/^(help|--help|about|what|what are you|what is this|what do you know( how to do)?|what can you do|who is)$/i', $query)) {
            return $this->help();
        }

        // Check for a request to limit the number of results.
        $limit = false;

        // A nice simple numeric quantity would be good. Fortunately Twilio transcription does this!
        if(preg_match('/^(?P<limit>[0-9]+) (?P<query>.+)$/i', $query, $matches)) {
            $query = $matches['query'];
            $limit = (int) $matches['limit'];

        } else { // No? Fine. We'll do it the hard way.

            // What? "Some" is definitely, objectively equal to 5. Look it up.
            $limitAliases =  array('some' => 5, 'a few' => 3, 'a couple' => 2, 'many' => 20, 'several' => 20, 'all' => false,
                'a bunch' => 15, 'any' => 10, 'an' => 1, 'a' => 1,
                'ten' => 10, 'eleven' => 11, 'twelve' => 12, 'thirteen' => 13, 'fourteen' => 14, 'fifteen' => 15,
                'sixteen' => 16, 'seventeen' => 17, 'eighteen' => 18, 'nineteen' => 19, 'twenty' => 20,
                'one' => 1, 'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5, 'six' => 6, 'seven' => 7, 'eight' => 8, 'nine' => 9);

            $limitAliasesRegex = implode('|', array_keys($limitAliases));
            if(preg_match('/^(?P<limit>' . $limitAliasesRegex . ')( of)?( the)? (?P<query>.+)$/i', $query, $matches)) {
                $query = $matches['query'];
                $alias = strtolower($matches['limit']);
                $limit = $limitAliases[$alias];
            }
        }

        // Laziness, Impatience and Hubris.
        $resourceTypeRegex = implode('|', call_user_func_array('array_merge', $this->_resourceTypes));

        // Check for queries in form "<resource type> of/about/with <subject>", e.g. "pictures of cats".
        // or "<subject> <resource type>", e.g. "cat pictures".
        if(preg_match('/^(?P<resource>' . $resourceTypeRegex . ')s? ((about|for|of|showing|with|like|having) )+(?P<subject>.+)$/i', $query, $matches)
            ||preg_match('/^(?P<subject>.+) (?P<resource>' . $resourceTypeRegex . ')s?$/i', $query, $matches)) {
            $type = strtolower($matches['resource']);
            $subject = $matches['subject'];
            foreach($this->_resourceTypes as $searchMethod => $types) {
                if(in_array($type, $types)) {
                    try {
                        return call_user_func(array($this, $searchMethod), $subject, $limit);
                    } catch(Temboo_Exception $e) {
                        error_log(__CLASS__ . '::' . $searchMethod . ' failed with ' . get_class($e) . ': ' . $e->getMessage());
                        return $this->error($e->getMessage());
                    }
                }
            }
        }

        // Check for location queries
        if(preg_match('/^(where( (is|are|.*find))?|.*directions?( to)?|locate|.*locations?( of| for)?) (?P<subject>.+)$/i', $query, $matches)) {
            $subject = $matches['subject'];
            switch(strtolower($subject)) {
                case 'temboo':
                    $subject = '104 Franklin Street, NYC';
                    break;
                case 'am i':
                    $subject = 'On Top of The World';
                    break;
            }
            try {
                return $this->searchLocation($subject);
            } catch(Temboo_Exception $e) {
                error_log(__CLASS__ . '::searchLocation failed with ' . get_class($e) . ': ' . $e->getMessage());
                return $this->error($e->getMessage());
            }
        }

        if(!$limit) {
            // Check for definition query for a single word. Note this also catches queries that are just a single word.
            if(preg_match('/^(((what do )?you know( of| about)?|what( is| are| was| were)?|tell( me| us)? (of|about)|.*definition( of)?|.*meaning( of)?|define|about)( the| an?)? )?(?P<subject>\w+)$/i', $query, $matches)) {
                $subject = $matches['subject'];
                switch(strtolower($subject)) {
                    case 'temboo':
                        if($this->_context != 'web') {
                            return $this->say("Temboo made me. You should follow them on Twitter: " . $this->shortenUrl('https://twitter.com/temboo'));
                        } else {
                            return $this->say('Temboo made me. You should follow them on <a href="https://twitter.com/temboo" target="_blank">Twitter</a>.', true);
                        }
                        break;
                }
                try {
                    return $this->searchDefinition($subject);
                } catch(Temboo_Exception $e) {
                    error_log(__CLASS__ . '::searchDefinition failed with ' . get_class($e) . ': ' . $e->getMessage());
                    return $this->error($e->getMessage());
                }
            }
        }


        // Check for other what/why/who questions
        if(preg_match('/^((what do )?you know( of| about)?|(what|who|why)( is| are| was| were)?|tell( me| us)? (of|about)|.*definition( of)?|.*meaning( of)?|define|about)( the| an?)? (?P<subject>.+)$/i', $query, $matches)) {
            $subject = $matches['subject'];
            switch(strtolower($subject)) {
                case 'temboo':
                    if($this->_context != 'web') {
                        return $this->say("Temboo made me. You should follow them on Twitter: " . $this->shortenUrl('https://twitter.com/temboo'));
                    } else {
                        return $this->say('Temboo made me. You should follow them on <a href="https://twitter.com/temboo" target="_blank">Twitter</a>.', true);
                    }
                    break;
                case 'general bucket':
                    if($this->_context == 'web') {
                        $this->say('<em>*salute*</em>', true);
                        return $this->say('<p class="general-bucket"><a href="assets/general-bucket.png" target="_blank"><img src="assets/general-bucket.png" class="general-bucket" /></a></p>', true);
                    }
                    break;
            }
            try {
                return $this->searchAnswers($subject);
            } catch(Temboo_Exception $e) {
                error_log(__CLASS__ . '::searchAnswers failed with ' . get_class($e) . ': ' . $e->getMessage());
                return $this->error($e->getMessage());
            }
        }

        // Okay, not a search. Maybe some polite banter?
        if(preg_match('/^how (are|is|have)/i', $query)) {
            return $this->say($this->howAre());
        }


        // More canned responses
        if(preg_match('/play a game/i', $query)) {
            return $this->say("How about " . $this->randomItem("Rock, Paper, Scissors?", "Global Thermonuclear War?", "a nice game of chess?"));
        }

        if(preg_match('/general bucket/i', $query)) {
            if($this->_context == 'web') {
                $this->say('<em>*salute*</em>', true);
                    return $this->say('<p class="general-bucket"><a href="assets/general-bucket.png" target="_blank"><img src="assets/general-bucket.png" class="general-bucket" /></a></p>', true);
            }
        }


        // Give up, do a random search type!
        $searchTypes = array_keys($this->_resourceTypes);
        if(!$limit) {
            $searchTypes[] = 'searchAnswers';
        }
        $searchType = $this->randomItem($searchTypes);
        if(!$limit) {
            switch($searchType) {
                case 'searchImages':
                    $limit = rand(3, 7);
                    break;
                case 'searchVideos':
                    $limit = 6;
                    break;
                case 'searchTweets':
                    $limit = rand(5, 10);
                    break;
                default:
                    $limit = false;
            }

        }
        error_log($searchType);
        return call_user_func(array($this, $searchType), $query, $limit);
    }


    /**
     * A random greeting.
     *
     * @return string randomly selected string
     */
    public function greet() {
        return $this->randomItem(
            'Hello! How can I help you?',
            'Martha at your service!',
            'Yes, this is dog. I mean, Martha.',
            'What are you looking for today?',
            'What can I do for you?'
        );
    }


    /**
     * A random acknowledgement of a request.
     *
     * @return string randomly selected string
     */
    public function okay() {
        return $this->randomItem(
            "Okay, I'm on it!",
            "I'll get right on that!",
            'You got it, boss.',
            'No problem!',
            'Sure thing.'
        );
    }


    /**
     * A random success report.
     *
     * @return string randomly selected string
     */
    public function goodNews() {
        return $this->randomItem(
            "You'll be pleased to know",
            'Good news!',
            'Ta-da.',
            'Woohoo,',
            'Here you go!',
            'Am I good or what?',
            'Yes!'
        );
    }

    /**
     * A random way to say we found something.
     *
     * @return string randomly selected string
     */
    public function foundSome() {
        return $this->goodNews()
        . ' '
        . $this->randomItem(
            'I found',
            'I located',
            'I have, just for you,',
            'I got you'
        );
    }


    /**
     * A random empty result report.
     *
     * @return string randomly selected string
     */
    public function foundNone() {
        return $this->randomItem(
            "Sorry, I couldn't find any",
            "That's odd, there don't seem to be any",
            'Sorry, the internet is fresh out of',
            'Alas, I failed to bring you your'
        );
    }


    /**
     * A random admission of uncertainty.
     *
     * @return string randomly selected string
     */
    public function notSure() {
        return $this->randomItem(
            "I'm not sure.",
            "I have absolutely no idea.",
            "Beats me!",
            "Um... good question!"
        );
    }


    /**
     * A random refusal for a bad or unknown request.
     *
     * @return string randomly selected string
     */
    public function sorryDave() {
        return $this->randomItem(
            "I'm sorry, Dave, I can't do that.",
            "I'm sorry, Dave. I can do that, but I just don't want to.",
            'Yeah, sorry. No.',
            'Nope. Better luck next time!',
            'Huh?'
        );
    }


    /**
     * A random response to garbled voice communication.
     *
     * @return string randomly selected string
     */
    public function cantHearYou() {
        return $this->randomItem(
            "Sorry, I didn't quite catch that.",
            'Can you repeat that?',
            'One more time, please?',
            'You want what now?'
        );
    }


    /**
     * A random upbeat report of how things (most likely Martha) are.
     *
     * @return string randomly selected string
     */
    public function howAre() {
        return $this->randomItem(
            "Super, thanks for asking.",
            'Pretty good.',
            'Just fine.',
            'Never better.',
            'Great!'
        );
    }


    /**
     * Play Rock, Paper, Scissors!
     *
     * @param string $userMove player's move
     */
    public function rps($userMove) {
        $userMove = strtolower($userMove);
        $marthaMove = $this->randomItem('rock', 'paper', 'scissors');
        $this->say(ucfirst($marthaMove) . "!");
        if($userMove == $marthaMove) {
            return $this->say($this->randomItem("It's a tie! Rematch?", "Best two out of three?"));
        }
        if(in_array("$userMove$marthaMove", array('paperrock', 'scissorspaper', 'rockscissors'))) {
            return $this->say($this->randomItem("Well played!", "Good game!", "You win!", "A winner is you!"));
        }
            return $this->say($this->randomItem("A winner is Martha!", "You were a good sport though.", "Again?"));
    }


    /**
     * Make up a random search Martha knows how to tackle
     *
     * @return string randomly built string
     */
    public function suggest() {
        $person = $this->randomItem('Tim Berners-Lee', 'Super Mario', 'Jean-Michel Basquiat', 'Levon Helm', 'Barack Obama', 'Shigeru Miyamoto', 'Eero Aarnio', 'Martin Scorsese', 'Twyla Tharp', 'Edward Tufte');

        $place = $this->randomItem('104 Franklin Street, NYC', 'Grand Central Station', 'Corsica', 'Geneva', 'Portland', 'Dublin', 'Tokyo', 'Heaven on Earth', 'Atlantis', 'Waldo', 'Hot Coffee');

        $things = array('3d printer', 'planet', 'goat', 'raccoon', 'computer program', 'skyscraper', 'data visualization', 'double-neck guitar', 'vinyl record', 'pdp-11', 'Baobab Tree', 'large hadron collider');

        $thingsIncludingIrregulars = array_merge($things, array('arcade game', 'salmon', 'javascript', 'zorbing', 'democracy', 'ARP 2600', 'IBM 701'));

        $resourceSearch = $this->randomItem('Find me', 'Search for', 'Get me', 'Run a search for', 'Look for', 'Find');

        $randomTypes = array_keys($this->_resourceTypes);
        shuffle($randomTypes);
        $type = $randomTypes[0];
        if($type == 'searchImages') {
            $preposition = $this->randomItem('of', 'with');
        } else {
            $preposition = $this->randomItem('of', 'about', 'with');
        }
        if($type == 'searchTweets') {
            $type = "tweets"; // No other dignified synonyms
        } else {
            $type = $this->randomItem($this->_resourceTypes[$type]) . 's';
        }

        switch(rand(0,4)) {
            case 0:
                $thing = $this->randomItem($things) . 's';
                $number = $this->randomItem('two', 'three', 'four', 'five', 'ten', 'twenty', 'some', 'all the');
                return implode(' ', array($resourceSearch, $number, $type, $preposition, $thing)) . '.';
                break;
            case 1:
                $thing = $this->randomItem($thingsIncludingIrregulars);
                $number = rand(2, 20);
                return implode(' ', array($resourceSearch, $number, $thing, $type)) . '.';
                break;
            case 2:
                return $this->randomItem("Define " . $this->randomItem($thingsIncludingIrregulars) . ".", "What is a " . $this->randomItem($things) . "?");
                break;
            case 3:
                return $this->randomItem('Where is ', 'Where can I find ', 'Locate ') . $place . '?';
                break;
            case 4:
                return $this->randomItem('Who is ', 'Do you know ') . $person . '?';
                break;

        }
    }

    /**
     * Usage suggestions and info about Martha.
     *
     * @return string randomly built string
     */
    public function help() {
        $this->say("Please let me help!");

        $this->say("I'm Martha and I can help you answer questions and find things online. Try asking:");

        $suggestions = array($this->suggest(), $this->suggest(), $this->suggest());

        if($this->_context != 'web') {
            foreach($suggestions as $suggestion) {
                $this->say($suggestion);
            }
            $this->say("I can talk to all of these APIs thanks to Temboo! https://temboo.com");
            $this->say("You can read my source at https://github.com/temboo/martha");
        } else {
            foreach($suggestions as $suggestion) {
                $this->say('<p><a href="?query=' . htmlentities($suggestion, ENT_COMPAT, 'UTF-8') . '" class="suggestion">"' .  htmlentities($suggestion, ENT_NOQUOTES, 'UTF-8') . '"</a></p>', true);
            }
            $this->say('<p>I can talk to all of these APIs thanks to <a href="https://temboo.com/" target="_blank">Temboo</a>!</p>', true);
            $this->say('<p>You can read my source at <a href="https://github.com/temboo/martha" target="_blank">Github</a>.</p>', true);
        }
    }


    /**
     * A random error report.
     *
     * @param string $message (optional) detailed error to append to random apology.
     */
    public function error($message = null) {
        $this->say($this->randomItem(
            'Sorry, something went wrong!',
            'Oops, I had an error.',
            'Oh dear, an error.',
            'Help, I need a debugger!'
        ));
        if($message) {
            $this->say($message);
        }

    }


    /**
     * Convenience method to pick random items from parameters or arrays
     *
     * @param mixed $item,... a single array, or multiple parameters of any type
     * @return mixed a single random item from the array or parameter list
     */
    protected function randomItem($item) {
        $args = func_get_args();
        if(count($args) == 1 && is_array($item)) {
            $args = $item;
        }
        $key = array_rand($args);
        return $args[$key];
    }

}

?>