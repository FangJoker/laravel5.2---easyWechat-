<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>xxx</title>
</head>
<meta name="viewport" content="initial-scale=1.0, width=device-width, user-scalable=no" />
<link rel="stylesheet" type="text/css" href="{{env('_PUBLIC_')}}/css/lanren.css">
<body>
 
<div class="wenx_xx">
  <div class="mz">xxx</div>
  <div class="wxzf_price">999</div>
</div>
<div class="skf_xinf">
  <div class="all_w"> <span class="bt">收款方</span> <span class="fr">xxx</span> </div>
</div>
 <a href="javascript:void(0)"  class="ljzf_but all_w"  onclick="pay()">立即支付</a>

<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js" type="text/javascript" charset="utf-8"></script>  
  
<script type="text/javascript" charset="utf-8">  
        wx.config(<?php echo $js->config(array('chooseWXPay'), false) ?>); //这里改成true就可以打开微信js的调试模式  
  
      function pay(){
          wx.chooseWXPay({  
               timestamp: '{{$config['timestamp']}}',  
                nonceStr: '{{$config['nonceStr']}}',  
                package:  '{{$config['package']}}',  
                signType:  '{{$config['signType']}}',     
                paySign: '{{$config['paySign']}}', // 支付签名 
                   success: function (res) {  
                    // 支付成功后的跳转页面
                    window.location.href='{{URL('')}}';  
                }  
            });  
      }
          
</script>  
</body>
</html>