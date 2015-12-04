$(function(){
    $(".mycoll-content dl").bind({
        mouseover:function(){
            $(this).css({"cursor":"pointer","border":"1px solid #90c4f0"});
            $(this).siblings().css({"position":"relative"});
        },
        mouseout:function(){
            $(this).css({"float":"left","cursor":"default","border":"1px solid white"});
            $(this).siblings().css({"position":"static","border":"1px solid white"});
        }
    });
});