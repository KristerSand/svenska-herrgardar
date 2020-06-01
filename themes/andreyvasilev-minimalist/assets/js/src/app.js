window.$ = window.jQuery = require('jquery');
window.Popper = require('popper.js').default;
require('bootstrap');

$(function() {
    "use strict";
    $('#sidebarCollapse').on('click', function () {
        $('#sidebar').toggleClass('active');
    });
    
    $('[data-toggle="popover"]').popover();

});