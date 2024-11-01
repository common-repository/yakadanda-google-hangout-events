=== Yakadanda Google+ Hangout Events ===
Contributors: Yakadanda.com
Donate link: http://www.yakadanda.com/
Tags: Calendar, Event, Google, Googleplus, Hangout, Plus, Yakadanda
Requires at least: 4.6
Tested up to: 4.7
Stable tag: 0.3.7
License: GPLv2 or later

Generate linked Google+ Hangout Event notifications in a widget from a Google Calendar with a nifty Countdown Clock to each event.

== Description ==

This plugin will generate linked Google+ Hangout Event notifications in a widget from a Google Calendar with a nifty Countdown Clock to each event. Fully customizable with fonts and colors.

= New Version 0.3.7 =
See https://wordpress.org/plugins/yakadanda-google-hangout-events/changelog/

= Features =
* Display Regular Google+ Events in Posts or Pages via Widgets
* Display Google+ Hangouts Events in Posts or Pages via Widgets
* Display Regular Google+ Events in Posts or Pages via short-code
* Display Google+ Hangouts Events in Posts or Pages via short-code
* Choose colors, fonts, size, and style for the widget (Google Fonts Included)
* Links Directly to Hangout Event on Google+
* Display Single Events (See FAQ)
* Display Public Events from My calendars (exception for contacts calendar and country holidays calendar)
* Display Events via short-code with control using attributes (See plugin settings help tab)
* Support Google+ Embedded Posts
* Ability to filter by only Google+ created Events
* Extend google+ event on WordPress admin

== Installation ==

1. Upload the full directory into your wp-content/plugins directory
2. Activate the plugin at the plugin administration page
3. Configure Google+ Hangout Events using the following pages in the admin panel: Events -> Settings.

That's it your done, you can add the Google Plus Hangout Events Widget to any sidebar.

== Frequently Asked Questions ==

= Do I need an Google account? =
Yes, you need Google account.

= How do I change default style to my preferences? =
Use /wp-content/plugins/yakadanda-google-hangout-events/build/css/google-hangout-events.css file as reference. Copy that file to your /wp-content/themes/active-theme/css/ folder as google-hangout-events.css

= How to find event identifier to create a single event shortcode? =
Single Event Example: https://plus.google.com/u/0/events/csnlc77gi4v519jom5gb28217so
The random letters and numbers after `***.com/u/0/events/c` is an event id, so the event identifier will be `snlc77gi4v519jom5gb28217so` without first letter 'c'.
To create a single event you would place in shortcode `[google+events id="snlc77gi4v519jom5gb28217so"]`

= Shortcode Reference =

**Shortcode Examples**

* `[google+events]`
* `[google+events type="hangout"]`
* `[google+events src="gplus"]`
* `[google+events limit="3"]`
* `[google+events past="2"]`
* `[google+events author="all"]`
* `[google+events limit="5" type="normal" past="1" author="all"]`
* `[google+events id="xxxxxxxxxxxxxxxxxxxxxxxxxx"]`
* `[google+events filter_out="xxxxxxxxxxxxxxxxxxxxxxxxxx,xxxxxxxxxxxxxxxxxxxxxxxxxx"]`
* `[google+events search="free text search terms"]`
* `[google+events attendees="show"]`
* `[google+events timezone="America/Los_Angeles"]`
* `[google+events countdown="true"]`
* `[google+events visibility="private"]`

**Attributes**

1. type	=	`all`, `normal`, or `hangout`, by default type is `all`
2. src	=	`all`, `gcal` (event from calendar), or `gplus` (event from google+), by default source is `all`
3. limit	=	number of events to display (maximum is 20)
4. past	=	number of months to display past events in `X` months ago, by default past is false
5. author	=	`self`, `other`, or `all`, by default author is `all`
6. id	=	Event identifier (string). Single Event Example: https://plus.google.com/u/0/events/csnlc77gi4v519jom5gb28217so To create a single event you would place in shortcode `[google+events id="snlc77gi4v519jom5gb28217so"]`
7. filter_out	=	Filter out certain events by event identifiers, seperated by comma
8. search	=	Text search terms (string) to display events that match these terms in any field, except for extended properties
9. attendees	=	Events can have attendees, the value can be `show`, `show_all`, or `hide`, the default value for attendees attribute is `hide`
10. timezone	=	Time zone used in the response, optional. Default is time zone based on location (hangout event not have location) if not have location it will use google account/calendar time zone. Supported time zones at http://www.php.net/manual/en/timezones.php (string)
11. countdown	=	`true`, or `false`, by default countdown is `false`
12. visibility = Show event based on visibility. Visibilty can `public`, `private`, or `all`, default is `public`

== Screenshots ==

1. Shortcode
2. Widget

== Changelog ==

= 0.3.7 =
* Remove minor bug

= 0.3.6 =
* Update Google API Client Library based on developers.google.com
* Modernize plugin files
* Update Help section

= 0.3.5 =
* Support WordPress 4.4
* Update google library to v1.1.6
* Update jCountdown
* Add week time to countdown

= 0.3.4 =
* Update google library
* Cache preference
* Remove Authorized redirect URI notice
* Improve cache feature

= 0.3.3 =
* Visibility feature
* Update google library

= 0.3.2 =
* Update google library
* Update link and description on Settings page
* Update Setup manual
* Improve cache feature

= 0.3.1 =
* Add Internationalization
* Update google library

= 0.3.0 =
* Update jCountDown
* Update google library
* Update Setup manual
* Improve notification on Events Settings page

= 0.2.9 =
* Update google library
* Improve notice feature
* Add cache feature to event query (frontend only)

= 0.2.8 =
* Update google library
* Update user interface
* Improve enqueue scripts, default settings and menu icon

= 0.2.7 =
* Update google library
* Update settings page and documentation

= 0.2.6 =
* Update google library
* Add extend feature for google+ event
* Move Google+ events menu on wordpress admin

= 0.2.5 =
* Update google library
* Add 'other' value to author options
* Add source options (event from calendar or from google+)

= 0.2.4 =
* Add reset features
* Bug fixes and other small improvements

= 0.2.3 =
* Change plugin redirect URI
* Change connection method to Google API and saving method
* Update google fonts
* Add settings link on plugins page
* Update Help section

= 0.2.2 =
* Add rich snippets to shortcode and widgets

= 0.2.1 =
* Add countdown feature to shortcode
* Improve countdown feature on widgets

= 0.2.0 =
* Differentiate events and hangout events on shortcode
* Fix widget tiltle on Event widget and Hangout widget

= 0.1.9 =
* Upgrade Google APIs Client Library to v0.6.7
* Update Google fonts
* Add Google+ Embedded Posts  feature https://developers.google.com/+/web/embedded-post/

= 0.1.8 =
* Add timezone features to shortcode and widget
* Rewrote help sections

= 0.1.7 =
* Upgrade Google APIs Client Library to v0.6.6
* Move How To manual and Shortcode manual to help tab
* Fix the way to get timezone abbreviation

= 0.1.6 =
* Update Google APIs Client Library

= 0.1.5 =
* Add email verification to google authentication and authorization
* Change default author event from self to all
* Display coworker name who is organizer in shortcode
* Add attendees attribute to shortcode

= 0.1.4 =
* Add line breaks feature on event description

= 0.1.3 =
* Update Google APIs Client Library for PHP to Google API PHP Client 0.6.2
* Add filter_out attribute on shortcode to filter out certain events by event identifiers
* Add search attribute on shortcode to display events that match the search terms in any field, except for extended properties

= 0.1.2 =
* Update countdown feature to support next year's event
* Extend events fetch feature to load public events in more calendars (exception for contacts calendar and country holidays calendar)

= 0.1.1 =
* Improve Save and Connect action when trying to connect with google api

= 0.1.0 =
* Add feature to display an event on shortcode based on event identifier

= 0.0.9 =
* Add link to Event button, Hangout button, and On Air button
* Update Google APIs Client Library for PHP to Google API PHP Client 0.6.1

= 0.0.8 =
* Add custom style feature for user preferences
* Add Event button customization in plugin settings

= 0.0.7 =
* Add handling API error for 403 http status

= 0.0.6 =
* No today events or future events on the shortcode which have past attribute

= 0.0.5 =
* Add author filter feature on widget and shortcode
* Add past option on shortcode only

= 0.0.4 =
* Add new widgets for normal event type
* Add google+ events shortcode
* Add time ago function for event create or event updated on the shortcode
* Add google maps link with query search on the shortcode for normal event
* Add Google fonts

= 0.0.3 =
* Add 2nd widget as widget (extra)
* Add countdown option feature for 2nd widget
* Add logout action

= 0.0.2 =
* Update to Google Calendar API v3
* Add instructions page as manual
* Display the connection status

= 0.0.1 =
* Use calendar feed in Google Calendar API v2

== Upgrade Notice ==

= 0.3.7 =
* Fix upload image feature

= 0.3.6 =
* Fix google-hangout-events.css as custom style

= 0.3.5 =
* -

= 0.3.4 =
* Fix minor bugs
* Fix minor style on manual

= 0.3.3 =
* -

= 0.3.2 =
* Fix calendar identifier validation on settings page

= 0.3.1 =
* -

= 0.3.0 =
* Fix cache feature

= 0.2.9 =
* -

= 0.2.8 =
* Fix widget colors

= 0.2.7 =
* Prevent conflict with other countdown plugin
* Fix redirect uri

= 0.2.6 =
* Fix logout feature and reset feature
* Fix undefined index for timezone

= 0.2.5 =
* Remove undefined index on widget

= 0.2.4 =
* Fix enqueue script load to pass scripting guard from Codestyling Localization plugin
* Fix logout issue

= 0.2.3 =
* Fix Manual page

= 0.2.2 =
* Remove undefined index on shortcode

= 0.2.1 =
* Fix id attribute bugs at shortcode

= 0.2.0 =
* Fix default style for shortcode

= 0.1.9 =
* -

= 0.1.8 =
* Fix bugs on calendar list function

= 0.1.7 =
* Fix illegal string offset issues
* Fix undefined index on plugin settings page

= 0.1.6 =
* Clear up notices on frontend and backend, that is undefined index, undefined offset, and undefined variable
* Fix event id bugs on shortcode
* Improve filter out attribute on shortcode

= 0.1.5 =
* Remove duplicate events on google response
* Fix error messages on shortcode
* Fix Invalid argument supplied for foreach in calendar list

= 0.1.4 =
* -

= 0.1.3 =
* -

= 0.1.2 =
* Fix event time function for next year's event
* Improve event sort function

= 0.1.1 =
* Fix fatal error: Cannot redeclare class URI_Template_Parser

= 0.1.0 =
* -

= 0.0.9 =
* Improve events query function
* Fix PHP notices in custom style/css function

= 0.0.8 =
* Define plugin version, plugin directory/url, active theme directory/url
* Improve the enqueue style

= 0.0.7 =
* Fix events query function to prevent site errors by 403 http status

= 0.0.6 =
* Fix past event on the shortcode
* Fix date time and countdown feature in the widgets for regular calendar event
* Fix date time for regular calendar event in the shortcode

= 0.0.5 =
* Fix PHP notices

= 0.0.4 =
* Improve the event time
* Fix message widget if no hangout event or no normal event

= 0.0.3 =
* Fix bugs in widget if google calendar not connected

= 0.0.2 =
* Fix widget blank issues if Adblock add-on installed on browser
* Fix bugs if no hangout event
