(function($, Drupal) {

  Drupal.informeaContent = Drupal.informeaContent || {};

  Drupal.informeaContent.init = function() {
    Drupal.informeaContent.prepareTabActions();
  };

  Drupal.informeaContent.prepareTabActions = function() {
    $('.gpleo-related-tab').on('click', function() {
      var _url = $(this).attr('gpleo-action');
      var _contentID = $(this).attr('id') + '_content';
      $('.gpleo-related-tab-content, .gpleo-related-tab').removeClass('active');
      $('#' + _contentID).addClass('active');
      $(this).addClass('active');
      Drupal.informeaContent.sendAJAXRequest(_url, _contentID);
    });
  };

  Drupal.informeaContent.sendAJAXRequest = function(_url, _contentID) {
    $.ajax({
      url: _url,
      success: function(data) {
        if (data.markup) {
          $('#' + _contentID).html(data.markup);
          var _pager = $('#' + _contentID).find('.gpleo-content-pager');
          var _itemsCount = parseInt(_pager.attr('gpleo-items'));
          var _itemsPerPage = parseInt(_pager.attr('gpleo-limit'));
          var _currentPage = parseInt(_pager.attr('gpleo-page'));
          if (_itemsCount > _itemsPerPage) {
            _pager.pagination({
              items: _itemsCount,
              itemsOnPage: _itemsPerPage,
              selectPage: _currentPage,
            });
          }
        }
      },
    });
  };

  $(document).ready(Drupal.informeaContent.init);

  Drupal.behaviors.informeaContent = {
    attach: function(context, settings) {

    }
  };

}(jQuery, Drupal));
