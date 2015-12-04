//计算商品小计
function getsubTotal($buycar,$con){
    var count = 0;
    if($con==1){
        count = $buycar.val();
    }else if($con==2){
        count = $buycar.next().val();
    }else{
        count = $buycar.prev().val();
    }
    var price = $buycar.parent().parent().prev().prev().children().children(".price").text();
    var duty = $buycar.parent().parent().prev().children().children(".duty").text();
    var subTotal = ((parseFloat(price)+parseFloat(duty))*count).toFixed(2);
    $buycar.parent().parent().next().children().children(".subtotal").text(subTotal);
}
//计算商品总额
function getSummer($divs){
    var summer = "";
    var integral = "";
    $divs.each(function(i,dom){
        var carri = $(dom).children().find(".goodInfo-wd-two:eq(1)").children().children(".duty").html();
        
        integral += parseFloat(carri).toFixed(2);
        var price = $(dom).children().find(".goodInfo-buycar-opera").prev().children().children(".subtotal").html();
        summer += parseFloat(price).toFixed(2);
    });
    $("#sumDuty").html(integral);
    $("#money").html(summer);
}
$(function(){
    $(".goodInfo-buycar-input").bind({
        focus:function(){
            $(this).css({"border":"1px solid #fd4d45"});
            $(this).prev().css({"background-color":"#fd4d45"});
            $(this).next().css({"background-color":"#fd4d45"});
        },
        blur:function(){
            $(this).css({"border":"1px solid #dddddd"});
            $(this).prev().css({"background-color":"#dddddd"});
            $(this).next().css({"background-color":"#dddddd"});
            var count = $(this).val();
            if(isNaN($.trim(count))){
                $(this).val(1);
            }
            if($.trim(count)>=100){
                $(this).val(1);
            }
            getsubTotal($(this),1);
            getSummer($("#mybuycar"));
        }
    });
    $(".goodInfo-minus").bind({
        mouseover:function(){
            $(this).next().css({"border":"1px solid #fd4d45"});
            $(this).css({"background-color":"#fd4d45","cursor":"pointer"});
            $(this).next().next().css({"background-color":"#fd4d45","cursor":"pointer"});
        },
        mouseout:function(){
            $(this).next().css({"border":"1px solid #dddddd"});
            $(this).css({"background-color":"#dddddd","cursor":"default"});
            $(this).next().next().css({"background-color":"#dddddd","cursor":"default"});
        }
    });
    $(".goodInfo-add").bind({
        mouseover:function(){
            $(this).prev().css({"border":"1px solid #fd4d45"});
            $(this).css({"background-color":"#fd4d45","cursor":"pointer"});
            $(this).prev().prev().css({"background-color":"#fd4d45","cursor":"pointer"});
        },
        mouseout:function(){
            $(this).prev().css({"border":"1px solid #dddddd"});
            $(this).css({"background-color":"#dddddd","cursor":"default"});
            $(this).prev().prev().css({"background-color":"#dddddd","cursor":"default"});
        }
    });
    //数量+，-
    $(".goodInfo-minus").click(function(){
        var count = $(this).next().val();
        if(isNaN($.trim(count))){
            $(this).next().val(1);
        }
        count--;
        if(count<=0){
            $(this).next().val(1);
        }else{
            $(this).next().val(count);
        }
        getsubTotal($(this),2);
    });
    $(".goodInfo-add").click(function(){
        var count = $(this).prev().val();
        if(isNaN($.trim(count))){
            $(this).prev().val(1);
        }
        count++;
        if(count>=100){
            $(this).prev().val(1);
        }else{
            $(this).prev().val(count);
        }
        getsubTotal($(this),3);
    });
    
    
    
    
    
});