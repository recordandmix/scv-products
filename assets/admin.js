(function ($) {
  function reindexRows() {
    var rows = $('#scv-colors-list .scv-color-row');
    rows.each(function (idx) {
      var $row = $(this);
      $row.attr('data-index', idx);
      $row.find('[data-name]').each(function () {
        var field = $(this).attr('data-name');
        $(this).attr('name', 'scv_colors[' + idx + '][' + field + ']');
      });
      var label = ($row.find('[data-name="label"]').val() || '').trim();
      $row.find('.scv-color-row-name').text(label ? label : 'Color #' + (idx + 1));
    });
  }

  function syncChip($row) {
    var hex = ($row.find('[data-name="swatch"]').val() || '').trim();
    var valid = /^#[0-9a-fA-F]{6}$/.test(hex);
    $row.find('.scv-color-chip').css('background', valid ? hex : '#cccccc');
    if (valid) {
      $row.find('.scv-hex-picker').val(hex.toLowerCase());
    }
  }

  function renderThumbs($row, items) {
    var $thumbs = $row.find('.scv-photo-thumbs');
    $thumbs.empty();
    var ids = [];
    items.forEach(function (it) {
      if (!it.id) return;
      ids.push(it.id);
      var $box = $('<div class="scv-photo-thumb"></div>').attr('data-id', it.id);
      if (it.url) {
        $box.append($('<img>').attr('src', it.url).attr('alt', ''));
      }
      $box.append('<button type="button" class="scv-photo-remove" title="Remove photo">&times;</button>');
      $thumbs.append($box);
    });
    $row.find('[data-name="photos"]').val(ids.join(','));
  }

  function currentItems($row) {
    var items = [];
    $row.find('.scv-photo-thumb').each(function () {
      var id = parseInt($(this).attr('data-id'), 10);
      var url = $(this).find('img').attr('src') || '';
      if (id > 0) items.push({ id: id, url: url });
    });
    return items;
  }

  $(function () {
    var $list = $('#scv-colors-list');
    if (!$list.length) return;

    $('#scv-add-color').on('click', function () {
      var tmpl = $('#scv-color-row-template').html();
      var $row = $(tmpl);
      $list.append($row);
      reindexRows();
      syncChip($row);
    });

    $list.on('click', '.scv-remove-color', function () {
      $(this).closest('.scv-color-row').remove();
      reindexRows();
    });

    $list.on('click', '.scv-move-up', function () {
      var $row = $(this).closest('.scv-color-row');
      var $prev = $row.prev('.scv-color-row');
      if ($prev.length) {
        $row.insertBefore($prev);
        reindexRows();
      }
    });

    $list.on('click', '.scv-move-down', function () {
      var $row = $(this).closest('.scv-color-row');
      var $next = $row.next('.scv-color-row');
      if ($next.length) {
        $row.insertAfter($next);
        reindexRows();
      }
    });

    $list.on('input', '[data-name="label"]', function () {
      var $row = $(this).closest('.scv-color-row');
      var val = $(this).val().trim();
      $row.find('.scv-color-row-name').text(val ? val : 'Color #' + ($row.index() + 1));
    });

    $list.on('input', '.scv-hex-picker', function () {
      var $row = $(this).closest('.scv-color-row');
      $row.find('[data-name="swatch"]').val($(this).val());
      syncChip($row);
    });

    $list.on('input', '[data-name="swatch"]', function () {
      syncChip($(this).closest('.scv-color-row'));
    });

    $list.on('click', '.scv-photo-remove', function () {
      var $row = $(this).closest('.scv-color-row');
      $(this).closest('.scv-photo-thumb').remove();
      renderThumbs($row, currentItems($row));
    });

    $list.on('click', '.scv-pick-photos', function (e) {
      e.preventDefault();
      var $row = $(this).closest('.scv-color-row');
      var frame = wp.media({
        title: 'Choose Product Photo(s)',
        button: { text: 'Use Selected Photo(s)' },
        library: { type: 'image' },
        multiple: true
      });
      frame.on('select', function () {
        var sel = frame.state().get('selection').toJSON();
        var items = currentItems($row);
        var seen = {};
        items.forEach(function (x) { seen[x.id] = true; });
        sel.forEach(function (att) {
          if (!att || !att.id || seen[att.id]) return;
          seen[att.id] = true;
          var url = (att.sizes && att.sizes.thumbnail && att.sizes.thumbnail.url) ? att.sizes.thumbnail.url : att.url;
          items.push({ id: att.id, url: url });
        });
        renderThumbs($row, items);
      });
      frame.open();
    });

    $list.on('click', '.scv-replace-photos', function (e) {
      e.preventDefault();
      var $row = $(this).closest('.scv-color-row');
      var frame = wp.media({
        title: 'Replace Product Photo(s)',
        button: { text: 'Use Selected Photo(s)' },
        library: { type: 'image' },
        multiple: true
      });
      frame.on('select', function () {
        var sel = frame.state().get('selection').toJSON();
        var items = [];
        sel.forEach(function (att) {
          if (!att || !att.id) return;
          var url = (att.sizes && att.sizes.thumbnail && att.sizes.thumbnail.url) ? att.sizes.thumbnail.url : att.url;
          items.push({ id: att.id, url: url });
        });
        renderThumbs($row, items);
      });
      frame.open();
    });

    reindexRows();
  });
})(jQuery);
