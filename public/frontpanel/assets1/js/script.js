var $body = $('body'),
  $overlay = $('.global-overlay');



/***** Sticky Header *******/

// function stickyHeader(selectors) {
// var { main } = selectors;
// var $headerPosition = (main.elExists()) ? main.offset().top : '';
// var $mainHeaderHeight = (main.elExists()) ? main[0].getBoundingClientRect().height : 0;
// var $headerTotalHeight = $headerPosition + $mainHeaderHeight;


// $(window).on('scroll', function () {
//     var $scroll = $(window).scrollTop();
//     if ($scroll > $headerTotalHeight) {
//         main.addClass('is-sticky');
//     } else {
//         main.removeClass('is-sticky');
//     }
// });
// }




// $(window).on('load', function () {
// var mainSelectors = {
//     main: $('.header')
// }
// stickyHeader(mainSelectors);
// });



/***** Toolbar Button Click Function *******/

$('.js-toolbar-btn').on('click', function (e) {
  e.preventDefault();
  e.stopPropagation();
  var $this = $(this);
  var target = $this.data('target');
  $body.toggleClass('body-open');
  $(target).toggleClass('open');
  $($overlay).addClass('overlay-open');
  $this.toggleClass('open');
});


/***** Document Click Function *******/

// $body.on('click', function (e) {
// var $target = e.target;
// var dom = $('.wrapper').children();

// if (!$($target).is('.js-toolbar-btn') && !$($target).parents().is('.open')) {
//     dom.removeClass('open');
//     $body.removeClass('body-open');
//     dom.find('.open').removeClass('open');
//     $overlay.removeClass('overlay-open');
// }

// });


/***** Close Button Click Function *******/

$('.btn-close').on('click', function (e) {
  e.preventDefault();
  var $this = $(this);
  $this.parents('.open').removeClass('open');
  $($overlay).removeClass('overlay-open');
});


// STICKY HEADER & MENU

// Hide header on scroll down
var didScroll;
var lastScrollTop = 0;
var delta = 5;
var navbarHeight = $('.nav_header').outerHeight();

$(window).scroll(function (event) {
  didScroll = true;
});

setInterval(function () {
  if (didScroll) {
    hasScrolled();
    didScroll = false;
  }
}, 250);

function hasScrolled() {
  var st = $(this).scrollTop();

  // Make scroll more than delta
  if (Math.abs(lastScrollTop - st) <= delta)
    return;

  // If scrolled down and past the navbar, add class .nav-up.
  if (st > lastScrollTop && st > navbarHeight) {
    // Scroll Down
    $('.nav_header').removeClass('nav-down').addClass('nav-up');
  } else {
    // Scroll Up
    if (st + $(window).height() < $(document).height()) {
      $('.nav_header').removeClass('nav-up').addClass('nav-down');
    }
  }

  lastScrollTop = st;
}

// NAV STICKY ENDS 

// COUNTER 
function startCounter(wrapper) {
  const counterElement = wrapper.querySelector(".exp_flx h1");
  const targetValue = parseInt(counterElement.innerText);
  let currentValue = 0;

  const increment = Math.ceil(targetValue / 100);

  const interval = setInterval(() => {
    if (currentValue >= targetValue) {
      clearInterval(interval);
    } else {
      currentValue += increment;
      currentValue = Math.min(currentValue, targetValue);
      counterElement.innerText = currentValue;
    }
  }, 20);
}



