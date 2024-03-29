/* -- BEGIN LICENSE BLOCK ----------------------------------
 *
 * This file is part of eventHandler, a plugin for Dotclear 2.
 *
 * Copyright(c) 2014-2023 Nicolas Roudaire <nikrou77@gmail.com> https://www.nikrou.net
 *
 * Copyright (c) 2009-2013 Jean-Christian Denis and contributors
 * contact@jcdenis.fr https://chez.jcdenis.fr/
 *
 * Licensed under the GPL version 2.0 license.
 * A copy of this license is available in LICENSE file or at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * -- END LICENSE BLOCK ------------------------------------*/

if (window.jQuery) {
  (function ($) {
    $.fn.eventHandlerCalendar = function (options) {
      var opts = $.extend({}, $.fn.eventHandlerCalendar.defaults, options);

      return this.each(function () {
        eventHandlerForm(
          this,
          opts.service_url,
          opts.service_func,
          opts.blog_uid,
          opts.msg_wait
        );
      });
    };

    function eventHandlerForm(
      target,
      service_url,
      service_func,
      blog_uid,
      msg_wait
    ) {
      var prev = $(target).find('a.prev');
      var next = $(target).find('a.next');

      $(prev).click(function () {
        return eventHandlerQuery(
          target,
          'prev',
          service_url,
          service_func,
          blog_uid,
          msg_wait
        );
      });
      $(next).click(function () {
        return eventHandlerQuery(
          target,
          'next',
          service_url,
          service_func,
          blog_uid,
          msg_wait
        );
      });
    }

    function eventHandlerQuery(
      target,
      direction,
      service_url,
      service_func,
      blog_uid,
      msg_wait
    ) {
      var weekstart = $(target).hasClass('weekstart') ? '1' : '0';
      var startonly = $(target).hasClass('startonly') ? '1' : '0';
      var dt = $(target).find('caption').attr('title');

      fetch(
        service_url +
          '&' +
          new URLSearchParams({
            f: service_func,
            blogId: blog_uid,
            reqDirection: direction,
            curDate: dt,
            weekStart: weekstart,
            startOnly: startonly,
            json: 1,
          })
      )
        .then((data) => data.json())
        .then((response) => {
          console.log(response);
          if (response.success) {
            console.log('replace table');
            $(target).find('table').replaceWith(response.payload.calendar);
            $(target).eventHandlerCalendar();
            return false;
          } else {
            return true;
          }
        })
        .catch(() => {
          return false;
        });
      return false;
    }

    $.fn.eventHandlerCalendar.defaults = {
      service_url: '',
      service_func: 'eventHandlerCalendar',
      blog_uid: '',
      msg_wait: 'Please wait',
    };
  })(jQuery);
}
