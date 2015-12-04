$(function(){
    $(".hot-keys a").bind({
        mouseover:function(){
            $(this).css({"text-decoration":"none","color":"orange"});
        },
        mouseout:function(){
            $(this).css({"color":"#000"});
        }
    });
    $(".search-input input").bind({
        focus:function(){
            $(this).css({"outline":"none"});
        },
        blurs:function(){}
    });
    /*全部商品分类*/
    $("#menu-daohang-total").hide();
    $("#all-good-type").bind({
        mouseover:function(){
            $("#menu-daohang-total").show();
        },
        mouseout:function(){
            $("#menu-daohang-total").hide();
        }
    
    });
    $("#menu-daohang-total").bind({
        mouseover:function(){
            $(this).show();
        },
        mouseout:function(){
            $(this).hide();
        }
    });
});