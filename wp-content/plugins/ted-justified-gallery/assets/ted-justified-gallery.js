(function () {
  "use strict";

  function initFjGallery() {
    if (typeof window.fjGallery !== "function") {
      console.warn("fjGallery non è disponibile su window.");
      return;
    }

    var galleries = document.querySelectorAll(".ted-justified-gallery.fj-gallery");
    if (!galleries.length) return;

    window.fjGallery(galleries, {
      itemSelector: ".fj-gallery-item",
      rowHeight: 300,      
      gutter: 8,           
      lastRow: "center"   
    });
  }

  function initLightbox() {
    if (typeof window.GLightbox !== "function") {
      console.warn("GLightbox non è disponibile su window.");
      return;
    }

    window.GLightbox({
      selector: ".tjg-link"
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    initFjGallery();
    initLightbox();
  });
})();