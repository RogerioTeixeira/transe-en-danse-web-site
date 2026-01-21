(function ($) {
  'use strict';

  function initMasonry($grid) {
    $grid.imagesLoaded(function () {
      $grid.masonry({
        itemSelector: '.ted-masonry-item',
        columnWidth: '.ted-masonry-sizer',
        percentPosition: true,
        gutter: 5
      });
    });
  }

  function initLightbox() {
    if (typeof GLightbox === 'function') {
      GLightbox({
        selector: '.ted-masonry-link'
      });
    } else {
      console.warn('GLightbox non trovato');
    }
  }

  $(function () {
    $('[data-ted-masonry]').each(function () {
      initMasonry($(this));
    });

    initLightbox();
  });

})(jQuery);