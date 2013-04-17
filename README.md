###About Martha

Meet Martha, our trusty helper bot. You can ask her anything!

Martha is powered by many of the APIs in the [Temboo Library](https://temboo.com/library/). Here's what she can do:

 * Take your requests via web, SMS, or voice with [Twilio](https://temboo.com/library/Library/Twilio/)
 * Find photos on [Flickr](https://temboo.com/library/Library/Flickr/)
 * Search for videos on [YouTube](https://temboo.com/library/Library/YouTube/)
 * Search for definitions on [Wordnik](https://temboo.com/library/Library/Wordnik/)
 * Search for places via [Google Geocoding](https://temboo.com/library/Library/Google/Geocoding/)
 * Search for tweets on [Twitter](https://temboo.com/library/Library/Twitter/)
 * Find descriptions via [DuckDuckGo](https://temboo.com/library/Library/DuckDuckGo/)
 * Find school programs you can donate to on [DonorsChoose.org](https://temboo.com/library/Library/DonorsChoose/)
 * Tell you whether a location is safe from toxic facilities via [EnviroFacts](https://temboo.com/library/Library/EnviroFacts/)
 * Upload the results of her searches to [Dropbox](https://temboo.com/library/Library/Dropbox/) or [S3](https://temboo.com/library/Library/Amazon/S3/) (mobile only)
 * Shorten those URLs with [Bitly](https://temboo.com/library/Library/Bitly/), the better to txt them back to you with [Twilio](https://temboo.com/library/Library/Twilio/) (mobile only)

And that's just to get you started - you can add more services to her really easily.

In fact, you can generate most of the code necessary live in the [Temboo library](https://temboo.com/library/), then paste it in. Browse through `martha.php` and you'll find library links above every method. If you follow those links you'll find code snippets that look very familiar.

###Quickstart
 1. Sign up for a free account at [temboo.com](http://temboo.com)
 2. Clone the repo: `git clone git@github.com:temboo/martha.git`
 3. Download the [Temboo PHP SDK](https://temboo.com/download) into the `martha` directory
 4. Copy `config.php.template` to `config.php` and edit with your Temboo credentials. Follow the links in this file to establish credentials for each API.
 5. When creating a Twilio developer account, add a phone number with callbacks `martha/query/sms.php` and `martha/query/voice.php`. Be sure to include a username and password in the callback URLs matching the values you supplied in `config.php`.

Martha can be dropped into any web host running PHP 5 or later.

No server? No problem! Here's a [handy beginner's guide](http://www.alexkorn.com/blog/2011/03/getting-php-mysql-running-amazon-ec2/) to launching your own server with PHP on Amazon EC2.

###Why PHP? Why no framework?
Well, there's really not much to this but Temboo calls and some hairy regular expressions. But that's the point! Martha is here to show off the awesome power of Temboo, not our Natural Language Processing expertise (which may or may not exist). That said, if you happen to be a bored NLP domain expert, pull requests welcome!

Bare bones PHP because a good demo shouldn't involve much in the way of installation or deployment. Just upload Martha to any old web host. The majority of setup will just be the time it takes you to sign up for developer accounts with all those APIs.

(And don't worry, we also have [SDKs](https://temboo.com/download) for Android, iOS, Java, Python, Ruby, and Node.js.)

###About Temboo
The Temboo SDK provides normalized access to 100+ APIs, databases and more so you can spend less time wrestling with API specifics and more time building what matters to you.

Learn more, and get the Temboo SDK in your favorite language, at [temboo.com](https://temboo.com).

###Contact Information
Have a question or see a bug? We'd love to hear from you at support@temboo.com.

###Copyright and License
Copyright 2013, Temboo Inc.

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in compliance with the License. You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.