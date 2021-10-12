/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import '../css/app.scss';
import $ from 'jquery';
import 'popper.js';

import 'bootstrap';
import 'bootstrap-slider/src/js/bootstrap-slider'
import 'bootstrap-slider/src/sass/bootstrap-slider.scss'
import 'bootstrap-select/js/bootstrap-select'
import 'bootstrap-select/sass/bootstrap-select.scss'

Array.prototype.move = function(from, to) {
    this.splice(to, 0, this.splice(from, 1)[0]);
};

Array.prototype.last = function(){
    return this[this.length - 1];
};

document.addEventListener('mercure_alert', function (e) {
    alert(e.detail.data);
});

$(document).ready(function() {
    $('[data-toggle="popover"]').popover();

    $('.bs-slider').slider();
});