=== Integration for ChurchSuite ===
* Contributors: dramb
* Tags: Events, ChurchSuite, Featured
* Requires at least: 6.4
* Tested up to: 6.7
* Stable tag: 1.0.3
* License: GPLv2 or later

Integration for ChurchSuite is a plugin to enable display of data from ChurchSuite JSON feeds


== Description ==

Integration for ChurchSuite (AKA CS Integration) allows you to display certain
data from ChurchSuite on your Wordpress website without resorting to embedding
iframes. This plugin provides shortcodes that are easy to drop into any page
or post. Each shortcode will, behind the scenes, request data from your
ChurchSuite feed, and will display the data returned in a similar way to the
usual ChurchSuite iframes, but natively to your website.  Many aspects of the
display can be modified in your theme to make the display match your website
theme. The shortcodes allow you to use a range of query parameters so that you
can display just the data you want for each part of your website.


== Current features include: ==

* Shortcode to return events as 'cards' with the event image and details
* Shortcode to return events in a 'list' group by date
* Shortcode to return a full month calendar, for the current month or a date
* Shortcode to return groups as 'cards' with the group image and details
* All API requests are cached with a 4 hour cache to ensure fast performance


== A little Technical information ==
For the technical among you: This shortcode works on the 'server side',
building the response which is delivered to your browser from churchsuite.


= Difference between this plugin and cs-js-integration =

We also provide the `cs-js-integration` plugin.  That plugin uses the more
recent v3 ChurchSuite API which does permit such flexibility because it
requires you to create a ChurchSuite 'embed configuration' to pass in the
shortcode call.  It also does all the work on the 'client side' so that
the client browser holds the cached data and the client browser creates all
the html for output.  That plugin use Javascript to create the response
whereas this plugin uses php.  This server-side implementation can be faster
for many repeated requests, and is less speed dependent on the client
provision. However the client-side implementation can be faster for an
individual user. The cs-js-integration plugin uses Alpine.js to output the
HTML, which means an end user could change the output by changing the HTML
files without having to get into the php of the plugin. However, the
Alpine.js code isn't straightforward and so this is likely to be of little
advantage. Really, it's simply 'horses for courses' - you have the choice
of which to use!



== Support ==

If you have a problem or a feature request, please send a message to the author.


== Demo ==

Currently there is no demo site, but you can view examples on a church website:

- the [Featured Events](https://www.cambray.org/),
- the [Small Groups list](https://www.cambray.org/connect/smallgroups/),
- the [Events List](https://www.cambray.org/whats-on/),
- the [Calendar](https://www.cambray.org/whats-on/calendar)


== Contributions ==

This plugin relies on information provided by ChurchSuite using their 'embed'
JSON feed.  Details of this JSON feed can be found here:

https://github.com/ChurchSuite/churchsuite-api/blob/master/modules/embed.md


== Installation ==

* From within Wordpress - In the Wordpress Dashboard use the menu to go to
  Plugins and from there choose 'Add new plugin'.  Search for 'churchsuite'
  and then look for this plugin.  Select the 'install' button on the plugin
  to install it, and once installed use the 'activate' link to activate the
  plugin.

* If you want to install from github:

	- Download from 'releases'
	- Rename the zip file downloaded 'cs-integration.zip' (i.e. remove any
	  version info in the filename)
	- In Wordpress use the Install New Plugin page to upload the zip file, or
	  alternatively, unpack and upload the cs-integration directory to your
	  '/wp-content/plugins/' directory.
	- Once you have done either of the above, Activate the plugin through the
	  'Plugins' page in the WordPress dashboard.

* Once you have used either method to install the plugin, you need to then
  add a shortcode (see examples below) to your wordpress posts or pages where
  you need them


== Usage ==

* For the *Event Cards shortcode*, place the shortcode into a page or post or
  into a shortcode block. The shortcode will be:

		[cs-event-cards church_name="mychurch" num_results="3"]

    where `mychurch` is the name of your church and `3` is changed to the
    number of future featured events you need in a page or post.  Use the
    parameter `featured="1"` to obtain only featured events.  Because your
    calendar will have _many_ events, make sure you include `num_results`
    to get the number of events you want.

    If you want events on a specific day, use the parameters `date_start`
    and `date_end`. If you want events from a particular Calendar category,
    use `category=1` where `1` is replaced by the category number for the
    Calendar category you want.

* For the *Event List shortcode*, place the shortcode into a page or post
  or into a shortcode block. The shortcode will be:

		[cs-event-list church_name="mychurch" num_results="10"]

	The comments above about parameters also apply to this shortcode.  By
	default only a maximum of 5 days events are returned, but this can be
	overridden by adding the `date_end` parameter.

* For the *Calendar shortcode* place the shortcode into a page or post or into a
  shortcode block. The shortcode will look like:

		[cs-calendar church_name="mychurch"]

	(where `mychurch` is the name of your church used to get into your churchsuite).
	The only parameter that might be used with this apart from `church_name` is
	`date_from` which will can be any date which will identify the month to be
	displayed - so `2025-01-15` and `2025-01-30` will both display the month
	January in 2025.

* For the *Smallgroups shortcode*, place the shortcode into a page or post or into
  a shortcode block. The shortcode will be:

		[cs-smallgroups church_name="mychurch"]

	(where `mychurch` is the name of your church.  The more limited set of
	parameters provided by ChurchSuite for small groups can all be used. 

See `https://github.com/ChurchSuite/churchsuite-api/blob/master/modules/embed.md=calendar-json-feed`
for a full list of parameters that can be used.


== License ==

The plugin itself is released under the GNU General Public License. A copy of
this license can be found at the license homepage or in the `cs-integration.php`
file in the top comment.


== Frequently Asked Questions ==

= The shortcode produces no output =

	The default behaviour when there is an error is to give no output rather
	than produce error messages all over your website.  Check that you have
	supplied the correct churchname, or test it with the churchname 'cambray'
	to see if that is the problem.  Check that you can actually get to your
	ChurchSuite JSON api url - try entering the following URL in a browser
	with your church name instead of `mychurch`:

		https://mychurch.churchsuite.com/embed/calendar/json?num_results=3

= How do I add my church so that I get the JSON feed for my church? =

	You must use the shortcode `church_name` parameter:

	    [cs-event-cards church_name="mychurch" num_results="3"]

= I want to limit the number of events in the shortcode =

	You can use a shortcode parameter for showing a particular number of events:

	    [cs-events-list church_name="mychurch" num_results="6"]

= I want to change how the output looks: =

	The output is formatted via css - just override the defaults in your theme


== Screenshots ==

1. Featured Events
2. Event List
3. Calendar
4. Small Groups
5. Example shortcode for Featured Events


== Changelog ==

**2025-03-17**
* Change to the README files to make them more readable on the Wordpress
    Directory and to include install instructions suitable for the directory.

**2025-03-13**
* Changes to how the JSON feed is read to remove a security vulnerability. Other
    minor changes to allow for final Wordpress Directory approval.

= 1.0.3 =

**2025-03-03**
* Added Event Categories to cs_event class, and the inclusion of an event category
    class to the html output by the event calendar so that we can colour the events.
* Added scrolling to the event description within the pop-up
* Added an additional control on font-size of paragraph tags within the event
    description to prevent theme definitions of for paragraphs causing large text
    to be displayed within the small calendar cells, unless the user overrides this.

= 1.0.2 =

**2025-03-03**
* CSS and HTML changes to allow the dates to be better formatted in the small
    responsive calendar display
* Minor changes to respond to two problems reported by the Wordpress plugin checker
* Change to the main plugin name to fit with requirements for inclusion on the
    Wordpress Plugin Directory
* Changes to the README files to reflect the version bump

= 1.0.1 =

**2025-02-27**
* Added the first of the functionality for the `cs-calendar` shortcode.  Updated
    `README.md` to reflect these changes.

**2025-02-25**
* Changed caching to cache API responses rather than the final HTMl, for security reasons
* Found that num_results alone causes a 8sec response time, but adding a date_end reduces
    the API response time to 1.5s regardless of the amount of events returned.  So defaulted
    the cs-events-list to 5 days just to get reasonable response time. 
* Added basic i18n support
* Changed all files to reflect 1.0.1 release status

= 1.0.0 =

**2025-02-20**
* Added caching of responses and displaying cancelled events in the event list correctly

**2025-02-18**
* All files changes to ensure all code was according to the Wordpress Style Guide
* Added the Events List shortcode

**2025-02-11**
* Major changes to everything to code in the style required for submission to Wordpress
    - Changed to plugin scaffolding based on WPPB
    - Change from procedural code to Classes and Objects

= 0.0.1 =

**2025-02-04**
* Initial release - really only a test of what was possible
