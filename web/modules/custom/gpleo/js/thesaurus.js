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
      var _parent = $(this).parents('.form-type-checkbox');
      if (this.checked) {
        _parent.addClass('active')
      }
      else {
        _parent.removeClass('active')
      }
    });
  };

  Drupal.thesaurusView.prepareSelectedTopics = function() {
    $('.topic-selected-item').remove();

    $('#edit-topics input.form-checkbox:checked').each(function() {
      var _parent = $(this).parents('.form-type-checkbox');
      _parent.addClass('active');
      Drupal.thesaurusView.showSelectedTopic($(this));
    });

    $('.topic-selected-item .close').on('click', function() {
      var topicValue = $(this).attr('value');
      $('#edit-topics-' + topicValue)
        .prop("checked", false)
        .parents('.form-type-checkbox').removeClass('active');
      $(this).parents('.topic-selected-item').remove();
      $('#topic-apply').click();
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
      $('#block-thesaurus-filter-topic-block #edit-submit-thesaurus').click();
      Drupal.thesaurusView.prepareSelectedTopics();
    });
    $('#edit-clear').on('click', function(event) {
      event.preventDefault();
      $('#block-thesaurus-filter-topic-block #edit-reset').click();
      Drupal.thesaurusView.prepareSelectedTopics();
    });
  };

  Drupal.behaviors.thesaurusView = {
    attach: function(context, settings) {
      $('#block-thesaurus-filter-topic-block').once('topic-filter-ready').each(function() {
        Drupal.thesaurusView.init();
      });
    }
  };

}(jQuery, Drupal));
