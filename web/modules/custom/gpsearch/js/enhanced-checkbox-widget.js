/**
 * @file
 * Transforms links into a dropdown list.
 */

(function ($) {

  'use strict';

  Drupal.facets = Drupal.facets || {};
  Drupal.behaviors.facetsEnhancedDropdownWidget = {
    attach: function (context, settings) {
      Drupal.facets.facetsEnhancedDropdownWidget(context, settings);
    }
  };

  /**
   * Turns on/off selections in the facet.
   *
   * @param {object} context
   *   Context.
   * @param {object} settings
   *   Settings.
   */
  Drupal.facets.facetsEnhancedDropdownWidget = function (context, settings) {
    $('.enhanced-select-facet').once('facets-enhanced-select-transform').each(function () {
      var $this = $(this);
      $this.find('a.toggle').on('click', function() {
        $this.find('.fieldset-wrapper').toggle();
        return false;
      });
    });
  };

})(jQuery);
