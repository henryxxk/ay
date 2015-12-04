$(function(){
    $("#default").css({"background-color":"#d30909"});
    $("#default").children().css({"color":"white"});
    $("#default").children().children().css({"color":"white"});
    $(".search-left-top-con-score ul").bind({
        mouseover:function(){
            /*$(this).css({"background-color":"#d30909"});
            $(this).children().css({"color":"white"});
            $(this).children().children().css({"color":"white"});*/
        },
        mouseout:function(){}
    });
    $(".search-left-top-con-score ul").click(function(){
        $(this).css({"background-color":"#d30909"});
        $(this).children().css({"color":"white"});
        $(this).children().children().css({"color":"white"});
        
        $(this).siblings().css({"background-color":"white"});
        $(this).siblings().children().css({"color":"#000000"});
        $(this).siblings().children().children().css({"color":"#000000"});
    });
    $(".pageUp").bind({
        mouseover:function(){
            $(this).css({"background-color":"#d30909"});
            $(this).children().css({"color":"white"});
            $(this).children().children().css({"color":"white"});
//            $(".search-left-top-con-page-ul-first").css({"background-color":"#e9e9e9"});
        },
        mouseout:function(){}
    });
    $(".pageUp").click(function(){
        $(this).css({"background-color":"#d30909"});
        $(this).children().css({"color":"white"});
        $(this).children().children().css({"color":"white"});
//        $(".search-left-top-con-page-ul-first").css({"background-color":"#e9e9e9"});
        
        $(".pageDown").siblings().css({"background-color":"white"});
        $(".pageDown").siblings().children().css({"color":"#000000"});
        $(".pageDown").siblings().children().children().css({"color":"#000000"});
    });
    
    
    $(".search-left-top-con-page-ul-first").bind({
        mouseover:function(){
            $(this).css({"background-color":"#e9e9e9","color":"#000000","cursor":"default"});
            $(this).children().css({"background-color":"#e9e9e9","color":"#000000","cursor":"default"});
        },
        mouseout:function(){
            $(this).css({"background-color":"#e9e9e9","color":"#000000","cursor":"default"});
        }
    });
    $(".search-left-page ul").bind({
        mouseover:function(){
            $(this).css({"background-color":"#d30909"});
            $(this).children().children().css({"color":"white"});
            $(".search-left-page-ul2").css({"background-color":"white","color":"#000000"});
            $(".search-left-page-ul3").css({"background-color":"white","color":"#000000"});
        },
        mouseout:function(){
            $(this).css({"background-color":"#f4f4f4"});
            $(this).children().children().css({"color":"#000000"});
            $(".search-left-page-ul2").css({"background-color":"white","color":"#000000"});
            $(".search-left-page-ul3").css({"background-color":"white","color":"#000000"});
        }
    });
    /*商品悬浮效果*/
    $(".search-left-bottom-score dl").bind({
        mouseover:function(){
            $(this).css({"background-image":"url(/Public/Image/search-good-bg.png)","background-repeat":"no-repeat","cursor":"pointer"});
        },
        mouseout:function(){
            $(this).css({"background-image":"none","cursor":"default"});
        }
    });
    $(".search-right").bind({
        mouseover:function(){
            $(this).children().css({"cursor":"pointer"});
        },
        mouseout:function(){
            $(this).children().css({"cursor":"default"});
        }
    });
    /*输入框的失去焦点事件*/
    $("#pageNo").bind({
        focus:function(){},
        blur:function(){
            var pageNo = $(this).val();
            if(isNaN($.trim(pageNo))){
                $(this).val(1);
            }
        }
        
    });
    
});