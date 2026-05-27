/*!
 * Krajee Font Awesome 5.x Theme configuration for bootstrap-star-rating.
 * This file must be loaded after 'star-rating.css'.
 *
 * bootstrap-star-rating v4.1.3
 * http://plugins.krajee.com/star-rating
 *
 * Author: Kartik Visweswaran
 * Copyright: 2013 - 2021, Kartik Visweswaran, Krajee.com
 *
 * Licensed under the BSD 3-Clause
 * https://github.com/kartik-v/bootstrap-star-rating/blob/master/LICENSE.md
 */
(function (factory) {
    'use strict';
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else if (typeof module === 'object' && typeof module.exports === 'object') { 
        factory(require('jquery'));
    } else { 
        factory(window.jQuery);
    }
}(function ($) {
    "use strict";
    $.fn.ratingThemes['krajee-fas'] = {
        filledStar: '<i class="fa-solid fa-star"></i>',
        emptyStar: '<i class="fa-regular fa-star"></i>',
        clearButton: '<i class="fa-solid fa-minus-circle"></i>'
    };
}));
