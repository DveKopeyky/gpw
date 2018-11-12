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
    $("#gpsearch_text").keyup(function(event) {
      var code = (event.keyCode ? event.keyCode : event.which);
      var termsList = $('#gpsearch_terms_list');
      if (code == 40 || code == 38 || code == 13) {
        var autocomleteItems = termsList.find('.gpsearch-autocomplete-item');
        var currentIndex = -1;
        var enterEnabled = false;
        if (termsList.find('.gpsearch-autocomplete-item.hover').length) {
          currentIndex = termsList.find('.gpsearch-autocomplete-item.hover').index();
          enterEnabled = true;
        }
        var newIndex;
        if (code == 40 && currentIndex < autocomleteItems.length - 1) {
          newIndex = currentIndex + 1;
        }
        else if (code == 38 && currentIndex > 0) {
          newIndex = currentIndex - 1;
        }
        else if (code == 13 && enterEnabled) {
          location.href = $(autocomleteItems[newIndex]).attr('href');
        }
        autocomleteItems.removeClass('hover');
        var selectedItem = $(autocomleteItems[newIndex]);
        selectedItem.addClass('hover');
        var scrollTo = selectedItem.offset().top - termsList.offset().top - termsList.height() + selectedItem.height() * (newIndex + 1);
        termsList.animate({scrollTop: scrollTo}, 200);
      }
      else {
        if (Drupal.searchAutocomplete.timeoutID) {
          clearTimeout(Drupal.searchAutocomplete.timeoutID);
        }
        Drupal.searchAutocomplete.timeoutID = setTimeout(Drupal.searchAutocomplete.prepareListWrapper, 200);
      }
    }).keypress(function(event) {
      // We shell catch Enter action before keyUp because by default it will work for form submit.
      var code = (event.keyCode ? event.keyCode : event.which);
      var termsList = $('#gpsearch_terms_list');

      if (code == 13 && termsList.hasClass('active') && termsList.find('.gpsearch-autocomplete-item.hover').length) {
        // This method is not work here idk why, So I will create clear url for redirect.
        // termsList.find('.gpsearch-autocomplete-item.hover').click();
        location.pathname = termsList.find('.gpsearch-autocomplete-item.hover').attr('href');

        return false;
      }
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
