/**
 * Created by sergii on 18.08.15.
 */

$(function(){
    var timer, angle = 0;
    $('#main').bind({
        mouseover: function(){
            timer = setInterval(function(){
                angle+=3;
                $("#main").rotate(angle);}, 20);
        },
        mouseout: function(){
            clearInterval(timer);
        }
    });
});