/*------------------------------------------------------------------
Project:    Mosaic
Author:     Simpleqode
URL:        http://simpleqode.com/
            https://twitter.com/YevSim
            https://www.facebook.com/simpleqode
Version:    1.3.1
Created:        20/01/2014
Last change:    06/07/2015
-------------------------------------------------------------------*/


// Isotop Gallery 
// ==============

/* Requires isotope.pkgd.min.js & imagesloaded.pkgd.min.js */

/**
 * Isotope filtering
 */

// init Isotope
var $container = $('#isotope-container').imagesLoaded( function() {
  $container.isotope({
    itemSelector: '.isotope-item',
    layoutMode: 'fitRows'
  });
});
// filter items on button click
$('#filters a').on('click', function() {
  var filterValue = $(this).attr('data-filter');
  $container.isotope({ filter: filterValue });
  return false;
});