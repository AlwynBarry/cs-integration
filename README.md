# CS Integration
Contributors: Alwyn Barry
Tags: Events, ChurchSuite, Featured
Requires at least: 4.1
Tested up to: 6.3.1
Stable tag: 1.0.0
License: GPLv2 or later

CS Integration is a plugin to enable display of data from ChurchSuite JSON feeds

## Description

CS Integration is a plugin that requests JSON data from a supplied ChurchSuite feed and
displays the data returned. The API feed currently provides for querying Calendar Events
and SmallGroups. The plugin allows you to provide parameters which will filter the
request.

## Current features include:

* Shortcode to return events as 'cards' with the event image and details
* Shortcode to return events in a 'list' group by date
* Shortcode to return groups as 'cards' with the group image and details
* All API requests are cached with a 1 hour cache to ensure fast performance 


## Support

If you have a problem or a feature request, please send a message to the author.


## Demo

Currently there is no demo site, but the Features Events on https://www.cambray.org/,
the Small Groups list on https://www.cambray.org/connect/smallgroups/ and the
events list on https://www.cambray.org/whats-on/ each use this plugin on a live church site.


## Contributions

This plugin relies on information provided by ChurchSuite relating to their JSON request API


# Installation

* Install the plugin through the admin page "Plugins" - Download the zip file to your computer, than use the Install New Plugin page to upload the zip file and install it.
* Alternatively, unpack and upload the contents of the zipfile to your '/wp-content/plugins/' directory.
* Once you have done either of the above, Activate the plugin through the 'Plugins' menu in WordPress.

# Usage
* For the Event Cards shortcode, place the shortcode into a page or post or into a shortcode block.
The shortcode will be: `[cs-event-cards church_name="mychurch" num_results="3"]` (where `mychurch` is
the name of your church and `3` is changed to the number of future featured events you need in a page
or post.  Use the parameter `featured="1"` to obtain only featured events.  Because your calendar
will have _many_ events, make sure you include `num_results` to get the number of events you want.
If you want events on a specific day, use the parameters `start-date` and `end-date`. If you want
events from a particular Calendar category, use `category=1` where `1` is replaced by the category
number for the Calendar category you want.
* For the Event List shortcode, place the shortcode into a page or post or into a shortcode block.
The shortcode will be: `[cs-event-list church_name="mychurch" num_results="10"]`.  The comments above
about parameters also apply to this shortcode.
* For the Smallgroups shortcode, place the shortcode into a page or post or into a shortcode block.
The shortcode will be: [cs-smallgroups church_name="mychurch"]' (where 'mychurch' is the name of your
church.  The more limited set of parameters provided by ChurchSuite for small groups can all be used. 

See https://github.com/ChurchSuite/churchsuite-api/blob/master/modules/embed.md#calendar-json-feed
for a full list of parameters that can be used.


# License

The plugin itself is released under the GNU General Public License. A copy of this license
can be found at the license homepage or in the `cs-integration.php` file in the top comment.


# Frequently Asked Questions

- How do I add my church so that I get the JSON feed for my church

You must use the shortcode `church_name` parameter:

	`[cs-event-cards church_name="mychurch" num_results="3"]`

- I want to limit the number of events in the shortcode.

You can use a shortcode parameter for showing a particular number of events:

	`[cs-events-list church_name="mychurch" num_results="6"]`

- I want to change how the output looks:

	The output is formatted via css - just override the defaults in your theme


# Screenshots

None as yet


# Changelog

## 1.0.0
**2025-02-20**
* Added caching of responses

**2025-02-18**
* All files changes to ensure all code was according to the Wordpress Style Guide
* Added the Events List shortcode

**2025-02-11**
* Major changes to everything to code the release in the style required for submission to Wordpress
* - Changed to plugin scaffolding based on WPPB
* - Change from procedural code to Classes and Objects

## 0.0.1
**2025-02-04**
* Initial release - really only a test of what was possible
