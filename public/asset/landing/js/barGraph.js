/*!
Responsive Horizontal Bargraph
jQuery plugin to create bar graphs to fit in a container.
Dual licensed under the MIT and GPL licenses
Author: ThemePerch
https: http://themeforest.net/user/themeperch
**/

(function( $ ){
  "use strict";

  $.fn.barGraph = function( options ) {

    // Defaults
    var settings = $.extend({
        'unit': 10,
        'total': 100,  
        'speed' : 1000, 
    }, options);

    var $selector = $(this);
    var unit = settings.unit;
    var total = settings.total;
    var loopCount = parseInt(total/unit);
    var stripunit = 100/loopCount;
    var loopCount = parseInt(total/unit);
    var stripunit = 100/loopCount;

    var init = function(){
        //class init
        $selector.addClass('horz-bars');

         if( loopCount > 0 ){
            for (var i = 0; i <= loopCount; i++) {
                $('.line-labels').append('<div style="width: '+stripunit+'%;"><span data-value="'+(unit*i)+'">'+(unit*i)+'</span></div>');
                $('.data-wrap').append('<div class="vertical-line" style="width: '+stripunit+'%; left: '+(stripunit*i)+'%"></div>');
            }
        }

    }

   init();

    return $selector.find('li').each(function(){
 
        // Store the object
        var $this = $(this);
        var $settings = settings;

        var barGraphAnimate = function() {

            var unit = settings.unit;
            var total = settings.total;
            var loopCount = parseInt(total/unit);
            var stripunit = 100/loopCount;
            var loopCount = parseInt(total/unit);
            var stripunit = 100/loopCount;

            var color = $(this).data('color');
            var value = $(this).find('.value').html();
            var linevalue = total* parseInt(value)/100;

            if(color) $(this).find('.bar, .bar-label').css({backgroundColor : color});
             $(this).find('.bar').animate({
                width: value
            }, settings.speed);

             if($selector.find('span[data-value='+linevalue+']').length > 0){
                $selector.find('span[data-value='+linevalue+']').css({backgroundColor : color}).addClass('active');                 
             }
             $(this).find('.value').hide();
        };

        // Perform counts when the element gets into view
        $this.waypoint(barGraphAnimate, { offset: '100%', triggerOnce: true });

    });

  };

})( jQuery );