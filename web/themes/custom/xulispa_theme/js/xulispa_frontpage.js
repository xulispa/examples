/* Parallax header image */
(function () {
  setTimeout(function () {
    var rellax = new Rellax('.header.images .slide');
  }, 500);
});

(function () {
	$('.header.images .field__items').cycle({
			fx: 'fade',
			timeout: 7000
	});
});

if ((".path-frontpage").length > 0) {
  var preventHash = false;
  var gAppear = null;

  function initAppear() {
    gAppear = appear({
      reappear: true,
      bounds: -($(window).height() / 2),
      elements: function () {
        return document.querySelectorAll('.page-section');
      },
      appear: function (el) {
        var id = el.getAttribute('id');
        $("nav a").removeClass('is-active-menu');
        $('nav a[href*="' + id + '"]').addClass('is-active-menu');
        preventHash = true;
      }
    })

  }

  (function () {
    $(document).ready(function () {
      if (history.pushState !== undefined) {
        initAppear();
        window.addEventListener('resize', function () {
          gAppear.destroy();
          initAppear();
        });
        window.addEventListener("hashchange", function (e) {
          if (preventHash) {
            //e.preventDefault();
            preventHash = false;
          }
        });
      }
    });
  });

} (jQuery);
