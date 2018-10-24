(function($, Drupal) {

  if (Drupal.getURLParam === undefined) {
    Drupal.getURLParam = function (name) {
      var results = new RegExp('[\?&]' + name + '=([^&#]*)')
        .exec(window.location.search);

      return (results !== null) ? results[1] || 0 : false;
    };
  }

  Drupal.searchAutocomplete = Drupal.searchAutocomplete || {};
  Drupal.searchAutocomplete.timeoutID = null;

  Drupal.searchAutocomplete.init = function() {
    Drupal.searchAutocomplete.prepopulateSearchField();
  };

  Drupal.searchAutocomplete.prepopulateSearchField = function() {
    var _searchInputField = $('#gpsearch_text');
    var _searchText = Drupal.getURLParam(_searchInputField.attr('name'));
    _searchInputField.val(_searchText);
  };

  Drupal.behaviors.searchAutocomplete = {
    attach: function(context, settings) {
      Drupal.searchAutocomplete.init();
    }
  };

}(jQuery, Drupal));
