'use strict';

(function () {
  if (!window.jQuery || !$.fn.dataTable) return;

  // Force bootstrap renderer
  $.extend(true, $.fn.dataTable.defaults, { renderer: 'bootstrap' });

  // Override page button renderer
  $.fn.dataTable.ext.renderer.pageButton.bootstrap = function (settings, host, idx, buttons, page, pages) {
    var api = new $.fn.dataTable.Api(settings);

    function attach(container, buttons) {
      var i, ien, button, node, btnDisplay, btnClass;

      function clickHandler(e) {
        e.preventDefault();
        if (!$(e.currentTarget).closest('li').hasClass('disabled')) {
          api.page(e.data.action).draw('page');
        }
      }

      for (i = 0, ien = buttons.length; i < ien; i++) {
        button = buttons[i];

        if ($.isArray(button)) {
          attach(container, button);
          continue;
        }

        btnDisplay = '';
        btnClass = '';

        switch (button) {
          case 'ellipsis':
            btnDisplay = '&#8230;';
            btnClass = 'disabled';
            break;

          case 'first':
            btnDisplay = '<i class="tf-icon mdi mdi-chevron-double-left"></i>';
            btnClass = page > 0 ? '' : 'disabled';
            break;

          case 'previous':
            btnDisplay = '<i class="tf-icon mdi mdi-chevron-left"></i>';
            btnClass = page > 0 ? '' : 'disabled';
            break;

          case 'next':
            btnDisplay = '<i class="tf-icon mdi mdi-chevron-right"></i>';
            btnClass = page < pages - 1 ? '' : 'disabled';
            break;

          case 'last':
            btnDisplay = '<i class="tf-icon mdi mdi-chevron-double-right"></i>';
            btnClass = page < pages - 1 ? '' : 'disabled';
            break;

          default:
            btnDisplay = (button + 1);
            btnClass = (page === button) ? 'active' : '';
            break;
        }

        node = $('<li>', { class: 'page-item ' + btnClass })
          .append($('<a>', { href: '#', class: 'page-link', html: btnDisplay }))
          .appendTo(container);

        node.on('click', 'a', { action: button }, clickHandler);
      }
    }

    $(host).empty();
    var ul = $('<ul class="pagination pagination-rounded pagination-outline-primary"></ul>').appendTo(host);
    attach(ul, buttons);
  };
})();
