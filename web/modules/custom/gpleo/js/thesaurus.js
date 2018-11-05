(function($, Drupal) {

  Drupal.thesaurusView = Drupal.thesaurusView || {};

  Drupal.thesaurusView.init = function() {
    Drupal.thesaurusView.prepareFilter();
    Drupal.thesaurusView.prepareSelectedTopics();
    Drupal.thesaurusView.prepareSubmitActions();
  };

  Drupal.thesaurusView.activeClassCheck = function(_element) {
    if (_element.hasClass('active')) {
      _element.removeClass('active')
    }
    else {
      _element.addClass('active')
    }
  };

  Drupal.thesaurusView.prepareFilter = function() {
    var filterTopic = $('#block-thesaurus-filter-topic-block #edit-topics');
    $('#topic-actions').appendTo('#block-thesaurus-filter-topic-block #edit-topics');

    $('#edit-topics--wrapper legend').on('click', function() {
      var _element = $('#block-thesaurus-filter-topic-block #edit-topics');
      if (_element.hasClass('active')) {
        _element.removeClass('active')
      }
      else {
        _element.addClass('active')
      }
    });

    $('#block-thesaurus-filter-topic-block #edit-topics .form-type-checkbox input.form-checkbox').on('change', function() {
      location.hash = 'custom';
      var _parent = $(this).parents('.form-type-checkbox');
      if (this.checked) {
        _parent.addClass('active')
      }
      else {
        _parent.removeClass('active')
      }

      // BEF autosubmit works incorrect.
      Drupal.thesaurusView.filterSubmit();
    });
  };

  Drupal.thesaurusView.transformTextAsHash = function(_text) {
    var transformedText = _text.replace(/\s+/g, '-').toLowerCase();

    return '#' + transformedText;
  };

  Drupal.thesaurusView.prepareColumns = function() {
    if ($('#column-1').length == 0) {
      var columnsMarkup = '<div id="column-1" class="col-sm-4"></div>'
        + '<div id="column-2" class="col-sm-4"></div>'
        + '<div id="column-3" class="col-sm-4"></div>';
      $('.view-id-thesaurus.view-display-id-page_alphabetically .view-content').append(columnsMarkup);
    }

    $('.thesaurus-column').each(function() {
      var _columnNumber = $(this).attr('columnNumber');
      var _columnID = '#column-' + _columnNumber;
      $(this).appendTo(_columnID);
    });
  };

  Drupal.thesaurusView.checkHashTopic = function() {
    if (location.hash) {
      $('#edit-topics .form-type-checkbox .control-label').each(function () {

        var label = $(this).text();
        if (Drupal.thesaurusView.transformTextAsHash(label) == location.hash) {
          $('#edit-topics input.form-checkbox:checked').prop('checked', false);
          $(this).find('input.form-checkbox').prop('checked', true);
          Drupal.thesaurusView.filterSubmit();
        }
      });
    }
  };

  Drupal.thesaurusView.prepareSelectedTopics = function() {
    $('.topic-selected-item').remove();

    $('#edit-topics input.form-checkbox:checked').each(function() {
      var _parent = $(this).parents('.form-type-checkbox');
      _parent.addClass('active');
      Drupal.thesaurusView.showSelectedTopic($(this));
    });

    $('.topic-selected-item .close').on('click', function() {
      location.hash = 'custom';
      var topicValue = $(this).attr('value');
      $('#edit-topics-' + topicValue)
        .prop("checked", false)
        .parents('.form-type-checkbox').removeClass('active');
      $(this).parents('.topic-selected-item').remove();
      Drupal.thesaurusView.filterSubmit();
    });
  };

  Drupal.thesaurusView.showSelectedTopic = function(_topic) {
    var topicName = _topic.parent().text();
    var topicValue = _topic.attr('value');
    var topicMarkup = '<div class="topic-selected-item"><span class="topic-name">' + topicName + '</span><span class="close" value="' + topicValue + '">x</span></div>';
    $('#topic-selected-items-wrapper').append(topicMarkup);
  };

  Drupal.thesaurusView.prepareSubmitActions = function() {
    $('#topic-apply').on('click', function(event) {
      event.preventDefault();
      Drupal.thesaurusView.filterClose();
      Drupal.thesaurusView.filterSubmit();
      Drupal.thesaurusView.prepareSelectedTopics();
    });

    $('#topic-clear').on('click', function(event) {
      location.hash = 'custom';
      event.preventDefault();
      $('#edit-topics input.form-checkbox')
        .prop("checked", false)
        .parents('.form-type-checkbox').removeClass('active');
      $('#block-thesaurus-filter-topic-block #edit-submit-thesaurus').click();
      Drupal.thesaurusView.prepareSelectedTopics();
    });
  };

  Drupal.thesaurusView.filterClose = function() {
    $('#block-thesaurus-filter-topic-block #edit-topics').removeClass('active');
  };

  Drupal.thesaurusView.filterSubmit = function() {
    $('#block-thesaurus-filter-topic-block #edit-submit-thesaurus').click();
  };

  Drupal.behaviors.thesaurusView = {
    attach: function(context, settings) {
      Drupal.thesaurusView.prepareColumns();
      $('#block-thesaurus-filter-topic-block').once('topic-filter-ready').each(function() {
        Drupal.thesaurusView.init();
      });
      Drupal.thesaurusView.checkHashTopic();
    }
  };

}(jQuery, Drupal));
