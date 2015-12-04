$(function(){
    $(".header a").bind({
        mouseover:function(){
            $(this).css({"color":"#c00000"});
        },
        mouseout:function(){
            $(this).css({"color":"#000"});
        }
    });
});