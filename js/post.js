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

$(function () {
  $('#eventhandler-form-title').toggleWithLegend(
    $('#eventhandler-form-content'),
    {
      legend_click: true,
      cookie: 'dcx_eventhandler_admin_form_sidebar',
    }
  );

  $('.event-node').each(function () {
    var nodeValue = $(this)
      .children('label')
      .children('.event-node-value')
      .attr('value');
    var nodeText = $(this).children('label').text();
    var a = $('<a class="event-unbind" href="#">[x]</a>');

    $(a).click(function () {
      dotclear.jsonServicesPost(
        'unbindEventOfPost',
        (data) => {
          alert(data.message);
          $(a).parent().remove();
        },
        { postId: $('#id').val(), eventId: nodeValue }
      );

      return false;
    });

    $(this).empty();
    $(this).text(nodeText).append(' ').append(a);
  });
});
