(function($) {

  $(document).ready(function(){
    $('.glossary-tabs-block a.glossary-tab').each(function() {
      if($(this).attr('href') == location.pathname){
        $(this).addClass('active');
      }
      else {
        $(this).removeClass('active');
      }
    });
  });

}(jQuery));
