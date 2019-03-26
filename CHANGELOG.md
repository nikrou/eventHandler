plugin eventHandler 2019.03.26
==============================
* Issue : style.css color are not suitable for admin darkmode.
  Sets an alternative admin css (dark-style.css) when darkmode is enabled
  - adminEventHandler::adminCss() returns css link according to darkmode user setting
  - all hardcoded references to css/style.css replaced

plugin eventHandler 2019-03-07
==============================
* Fix issue #42. Archibve plugin name must be prefix by plugin-
  Thanks to Franck @franck-tomek for report.

plugin eventHandler 2019-02-24
==============================
* Fix issue #41. Allow sql_only params for getEvents like in core for getPosts()
  Thanks to @onurb-taktile for report

plugin eventHandler 2019-02-22
==============================
* Fix issue #40. Navigation in widget of events works only for first click.
  Thanks to @onurb-taktile for report

plugin eventHandler 2018-01-25
==============================
* Fix issue #38. entry content is now wrap in #entry-wrapper
* Add new template tag (EventsCount) to display special message when there's no events

plugin eventHandler 2017-06-15
==============================
* Fix issue #37. The same protocol (https) must be used. Thanks to @franck-tomek for report.

plugin eventHandler 2016-11-10
==============================
* Fix issue with CSP (for maps)
* Fix issue #35 (CSP for tags).

plugin eventHandler 2016-11-07
==============================
* Add API Key to allow request to googlemaps API.

plugin eventHandler 2016-09-08
==============================
* Avoid lock in db transaction because of SQLite. Thanks to @scolin
* Fix a bug cause by the lack of TIMESTAMP function in sqlite. Thanks to @scolin.
* Fixes #7 : deals with CSP policy. Add related csp header. Thanks to @franck-tomek for report.

plugin eventHandler 2015-08-05
==============================
* Fix issue with preview

plugin eventHandler 2015-08-04
==============================
* Allow admin to choose betwween googleMaps and OpenStreetMap to display maps.
* Update icons
* Update translations.

plugin eventHandler 2015-07-27
==============================
* Fix #28. Thanks for Tomek and ReBoLyF

plugin eventHandler 2015-05-31
==============================
* Update translation. Thanks to @franck-tomek.
* Fix issue with link on calendar widget. Thanks to Onurb Teva <dev@taktile.fr>.

plugin eventHandler 2015-04-22 - 2015-04-22
===========================================
Fixes #23 - Lost automatic address completion from GMaps when entering partial address.
  Thanks to @franck-tomek for reporting issue.

plugin eventHandler 2015-03-28 - 2015-03-28
===========================================
* Fix breadcrumb and message info
* Fix widget names "EventHandler: widgetname" + subtitle
* Add  content_only, class, and offline mode for widgets
* Fix localization and misspelling
* Add html5 switch using mustek/currywurst default templates
* Add options to _define
  Thanks to Pierre Van Glabeke
* Split some classes in their own file
* Fix misspelling, thanks to Onurb Teva <dev@taktile.fr>.

plugin eventHandler 2015-03-22 - 2015-03-22
===========================================
* Remove deprecated behaviors (adminPostsActionsCombo, adminPostsActionsHeaders, adminPostsActionsContent)
* Add severals behaviors to customize admin pages.
* And many others fixes.
* A big thanks to Onurb Teva <dev@taktile.fr>.

plugin eventHandler 2014-12-17 - 2014-12-17
===========================================
* Fix issue for bulk actions (authors and categories).
  Thanks to vdanjean for reporting issue.

plugin eventHandler 2014-12-08 - 2014-12-08
===========================================
* Fix misspeling in template. Thanks to @franck-tomek.

plugin eventHandler 2014-12-02 - 2014-12-02
===========================================
* Add more translations
* Prepare for new dotclear release (editor)

plugin eventHandler 2014-12-01 - 2014-12-01
===========================================
* Fix javascript issue preventing editor to be displayed.
* Add some translations.

Previous history
----------------
2013.07.07
 * Added options to widgets (closes #693)
 * Added dashboard icon
 * Fixed admin pages titles and messages
 * Fixed map on Ductile theme's widget
 * Fixed typo

1.0-RC4 20110102
 * Removed priority (useless with new version of plugin kUtRL)

1.0-RC3 20100918
 * Fixed bug on arrayObject

1.0-RC2 20100915
 * Fixed some bugs and typo

1.0-RC1 20100903
 * First lab release

1.0-beta 20100724
 * First public beta release
