(function($, Drupal, drupalSettings) {

  Drupal.getURLParam = function (name) {
    var results = new RegExp('[\?&]' + name + '=([^&#]*)')
      .exec(window.location.search);

    return (results !== null) ? results[1] || 0 : false;
  };

  Drupal.searchAutocomplete = Drupal.searchAutocomplete || {};
  Drupal.searchAutocomplete.timeoutID = null;

  Drupal.searchAutocomplete.init = function() {
    Drupal.searchAutocomplete.prepopulateSearchField();
    Drupal.searchAutocomplete.prepareSearchField();
    Drupal.searchAutocomplete.prepareSubmit();
  };

  Drupal.searchAutocomplete.prepareSubmit = function() {
    $('#search_icon').on('click', function() {
      $('#gpsearch_form').submit();
    });
  };

  Drupal.searchAutocomplete.prepareSearchField = function() {
    $("#gpsearch_text").keypress(function(event) {
      if (Drupal.searchAutocomplete.timeoutID) {
        clearTimeout(Drupal.searchAutocomplete.timeoutID);
      }
      Drupal.searchAutocomplete.timeoutID = setTimeout(Drupal.searchAutocomplete.prepareListWrapper, 200);
    });
    $('#gpsearch_text').on('focusout', function() {
      if ($('#gpsearch_terms_list').hasClass('active')) {
          $('#gpsearch_terms_list').removeClass('active');
      }
    });
  };

  Drupal.searchAutocomplete.prepareListWrapper = function() {
    var listWrapper = $('#gpsearch_terms_list');
    $.ajax({
      url: '/gpsearch/autocomplete/thesaurus/' + $('#gpsearch_text').val(),
      dataType: 'json',
      success: function(data) {
        listWrapper.empty();
        if (data && data.length) {
          $.each(data, function (i, v) {
            var newItem = '<a href="' + v.url + '" class="gpsearch-autocomplete-item">' + v.name
              + '<span class="help-text">' + Drupal.t('See definition and related content') + '</span></a>';
            listWrapper.append(newItem);
          });
          listWrapper.addClass('active');
        } else {
          listWrapper.removeClass('active');
        }
      }
    });
  };

  Drupal.searchAutocomplete.prepopulateSearchField = function() {
    var _searchInputField = $('#gpsearch_text');
    var _searchParamName = _searchInputField.attr('name');
    var _searchParamValue = Drupal.getURLParam(_searchParamName);
    if (_searchParamValue) {
      var _searchText = decodeURI(_searchParamValue.replace(/\+/g, ' '));
      if (_searchText) {
        _searchInputField.val(_searchText);
      }
    }

    if (location.pathname == $('#gpsearch_form').attr('action')) {
      $.ajax({
        url: '/gpsearch/autocomplete/thesaurus/' + _searchText,
        dataType: 'json',
        success: function(data) {
          if ((data.length == 1) && (data[0].name == _searchText)) {
            $('#gpsearch_help_link')
              .attr('href', data[0].url)
              .html(data[0].name);
            $('#gpsearch_help').addClass('active');
          }
        }
      });
    }
  };

  Drupal.behaviors.searchAutocomplete = {
    attach: function(context, settings) {
      Drupal.searchAutocomplete.init();
    }
  };

}(jQuery, Drupal, drupalSettings));
