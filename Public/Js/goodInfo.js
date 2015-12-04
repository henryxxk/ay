$(function(){

//    $(".good-border :eq(0)").css({"cursor":"pointer","border":"2px solid #90c4f0"});

//    $(".good-border :gt(0)").css({"border":"1px solid white"});

    $(".good-border").bind({

        mouseover:function(){

            $(this).css({"cursor":"pointer","border":"2px solid #90c4f0"});

            $(this).siblings().css({"position":"static","border":"1px solid white"});

        },

        mouseout:function(){

            $(this).css({"float":"left","cursor":"default","border":"1px solid white"});

            $(this).siblings().css({"position":"static","border":"1px solid white"});

        }

    });

    $(".good-Info-bo-le-le ul").css("cursor","pointer");

    $(".good-Info-bo-le-le ul:eq(0)").css({"background-color":"white","border-top":"3px solid #71acf4"});

    $(".good-Info-bo-le-le ul").click(function(){

        $(this).css({"background-color":"white","border-top":"3px solid #71acf4"});

        $(this).siblings().css({"background-color":"#dfdfdf","border-top":"none"});

    });

    

});