(function($){

// Skip binding in Elementor editor mode to avoid capturing clicks
if (window.elementorFrontend && elementorFrontend.isEditMode && elementorFrontend.isEditMode()) {
  return;
}

  $(document).on('click', '.project-open-modal', function(e){
    e.preventDefault();
    var target = $(this).data('modal');
    var $m = $(target);
    if ($m.length){ $m.addClass('is-open'); $('body').css('overflow','hidden'); }
  });
  $(document).on('click', '.anima-modal [data-close], .anima-modal__overlay', function(){
    var $m = $(this).closest('.anima-modal');
    $m.removeClass('is-open'); $('body').css('overflow','');
  });
  $(document).on('keyup', function(e){
    if (e.key === 'Escape'){
      $('.anima-modal.is-open').removeClass('is-open');
      $('body').css('overflow','');
    }
  });
})(jQuery);
