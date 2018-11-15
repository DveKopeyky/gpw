(function($, Drupal) {

  Drupal.informeaContent = Drupal.informeaContent || {};

  // Init tabs default data.
  Drupal.informeaContent.tabs = {
    '#tab-1': {
      id: '#treaty_text',
      page: 1,
    },
    '#tab-2': {
      id: '#treaty_decisions',
      page: 1,
    },
    '#tab-3': {
      id: '#documents_and_literature',
      page: 1,
    },
    '#tab-4': {
      id: '#goals_and_declarations',
      page: 1,
    },
  };

  Drupal.informeaContent.init = function() {
    Drupal.informeaContent.prepareTabActions();
    Drupal.informeaContent.hashReader();
  };

  Drupal.informeaContent.hashReader = function() {
    var _tabs = Drupal.informeaContent.tabs;
    var currentTab = '#tab-1';
    if (location.hash) {
      var hashData = location.hash.split(',');
      if (_tabs[hashData[0]]) {
        currentTab = hashData[0];
        if (hashData[1] && hashData[1].indexOf('page-') === 0) {
          _tabs[currentTab].page = parseInt(hashData[1].replace('page-', ''));
        }
      }
    }
    var tabElement = _tabs[currentTab];
    $(tabElement.id).attr('gpleo-page', tabElement.page).click();
  };

  Drupal.informeaContent.prepareTabActions = function() {
    $('.gpleo-related-tab').on('click', function() {
      var _tabPage = $(this).attr('gpleo-page');
      var _url = $(this).attr('gpleo-action') + '?page=' + _tabPage;
      var _tabID = $(this).attr('id');
      var _contentID = $(this).attr('id') + '_content';
      $('.gpleo-related-tab-content, .gpleo-related-tab').removeClass('active');
      $('#' + _contentID).addClass('active');
      $(this).addClass('active');
      Drupal.informeaContent.sendAJAXRequest(_url, _contentID, _tabID);
    });
  };

  Drupal.informeaContent.sendAJAXRequest = function(_url, _contentID, _tabID) {
    $.ajax({
      url: _url,
      success: function(data) {
        if (data.markup) {
          $('#' + _contentID).html(data.markup);
          var _pager = $('#' + _contentID).find('.gpleo-content-pager');
          var _itemsCount = parseInt(_pager.attr('gpleo-items'));
          var _itemsPerPage = parseInt(_pager.attr('gpleo-limit'));
          var _currentPage = parseInt(_pager.attr('gpleo-page'));
          var _pagerTabID = _pager.attr('gpleo-tab-id');

          // Init pager.
          if (_itemsCount > _itemsPerPage) {
            _pager.pagination({
              items: _itemsCount,
              itemsOnPage: _itemsPerPage,
              selectOnClick: false,
              currentPage: _currentPage,
              onPageClick: function(pageNumber, event) {
                if (_currentPage != pageNumber) {
                  location.hash = _pagerTabID + ',page-' + pageNumber;
                  Drupal.informeaContent.hashReader();
                }
                return false;
              },
            }).pagination('selectPage', _currentPage);
          }

          // Init accordion.
          Drupal.informeaContent.initAccordion($('#' + _contentID));
        }
      },
    });
  };

  // Adaptated from accordion.js.
  Drupal.informeaContent.initAccordion = function(_element) {
    _element.find('.accordion-label').on('click', function() {
      var listID = $(this).attr('data-list-id');
      var accordionList = $('#' + listID);
      if (accordionList.hasClass('active')) {
        accordionList.removeClass('active');
        $(this).removeClass('active');
      }
      else {
        accordionList.addClass('active');
        $(this).addClass('active');
      }
    });
  };

  $(document).ready(Drupal.informeaContent.init);

  Drupal.behaviors.informeaContent = {
    attach: function(context, settings) {

    }
  };

}(jQuery, Drupal));
