# 准备工作 #

## 第一步 ##
在laravel 上部署easywehcat 
这里不多解释， 请参考：
[https://github.com/overtrue/laravel-wechat](https://github.com/overtrue/laravel-wechat)
## 第二步 ##
我们需要到 *微信支付商平台* 去获取以下3个东西：
1.  商户id
2.  api密匙
3.  相关证书
![](https://i.imgur.com/pOGipQX.png)
![](https://i.imgur.com/jdHzJwT.png)

证书里面的 **rootca.pem**需要放到 php安装目录下的extras/ssl 

如果没有证书 一般报这样的错
![](https://i.imgur.com/mDSlBUO.png)

## 第三步 ##
设置 js 安全域名。 注意是填写域名，不是支付方法的URL
![](https://i.imgur.com/Ee6osxs.png)

# 支付大致流程 #
![](https://i.imgur.com/SaYaEq2.png)

其实实际上还要复杂一点，我个人理解大概就是这样的一个流程



# 开始 #
配置第一步得到的相关信息

	 protected function options(){ //选项设置  
        return [  
            // 前面的appid什么的也得保留哦  
            'app_id' => '', //你的APPID  
            'secret'  => '',     // AppSecret  
           
            // 'token'   => 'your-token',          // Token  
            // 'aes_key' => '',                    // EncodingAESKey，安全模式下请一定要填写！！！  
            // ...  
            // payment  
            'payment' => [  
                'merchant_id'        => '',   //商户id  
                'key'                => '',  //api密匙  
                // 'cert_path'          => 'path/to/your/cert.pem', // XXX: 绝对路径！！！！  
                // 'key_path'           => 'path/to/your/key',      // XXX: 绝对路径！！！！  
                //'notify_url'         => 'http://...',       // 你也可以在下单时单独设置来想覆盖它  
                // 'device_info'     => '013467007045764',  
                // 'sub_app_id'      => '',  
                // 'sub_merchant_id' => '',  
                 
            ],  
        ];  
    }  



统一下单

	 public function pay(){                     
              
              $user = session('wechat.oauth_user');     // 拿到授权用户资料
              $openId=$user['original']['openid'];
              $headUrl=$user['original']['headimgurl'];
              $nickName=$user['original']['nickname'];
              $sex=$user['original']['sex'];

              $userInfo=UserModel::where('openid', $openId)->first();   

           
             if($userInfo){                                    //存在下订用户才进行
            
                     if (TradeModel::where('openid', $openId)->first()->Status==1) {    //如果该用户已经付款了进入链接的业务逻辑  假设付款 回调更新状态为 1
                               //
                           
                      }

                       $userInfo->nickName=$nickName; $userInfo->sex=$sex; $userInfo->head_url=$headUrl;  
                       $userInfo->save();                                    //更新用户信息

                        //更新下单用户信息             
                        $mch_id = "";                     //MCH_ID  
                        $time=date("Y-m-d-h:i:s");

                         /**注意：这个业务逻辑是根据openid 判断一个人只能下单一次。 不需要可以删去直接添加单号*/
                         if ( !TradeModel::where('openid', $openId )->first() ) {        //如果没有订单就添加订单 
                             $id=TradeModel::insertGetId(['openid'=>$openId, 'area'=>$userInfo['area'], 'total_fee'=>39, 'time_start'=>$time,]);  
                                $out_trade_no = $mch_id.$id.date("Ymdhis");     //拼一下订单号 
                         }else{
                             
                               $out_trade_no=TradeModel::where('openid', $openId )->first()->out_trade_no;
                         }
                         /*注意：这个业务逻辑是根据openid 判断一个人只能下单一次。 不需要可以删去直接添加单号*/
                     

                             $options = $this->options();  
                             $app = new Application($options);           //获得 SDK服务对象
                             $payment = $app->payment;                   //得到支付对象                                           
                             $attributes = [  
                                 'trade_type'       => 'JSAPI', // JSAPI，NATIVE，APP...  
                                 'body'             => '',  
                                 'detail'           => '',
                                 'out_trade_no'     => $out_trade_no,  
                                 'total_fee'        => '3900', //因为是以分为单位，所以订单里面的金额乘以100  
                                  'notify_url'       => 'http://....', // 支付结果通知网址，如果不设置则会使用配置里的默认地址  
                                 'openid'           => $openId,//$openId,  // 此参数必传，用户在商户appid下的唯一标识，                 
               
                             ];  
                             $order = new Order($attributes);        //统一下单，预处理,得到一个预处理id, payment->prepare(order);
                             $result = $payment->prepare($order);    //返回预处理结果
                             
                      if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS'){                       
                                 
                                 $order_find->out_trade_no = $out_trade_no;       //在这里再更新订单 
                                 $order_find->save();  

                                 $prepayId = $result->prepay_id;                                  //获得微信的预支付ID
                                 $config = $payment->configForJSSDKPayment($prepayId);         //生成支付JS配置
                                 $js =$app->js;                                      //这个是jssdk里页面上需要用到的js参数信息。                    
                               return view('pay')                               //将得到参数传给支付视图
                              ->withConfig($config)  
                              ->withJs($js) ;                                    
                      } 
                  
             }else{   //没有用户信息则无订单
                   echo "<h1>无订单</h1>";
             }   
           
    }  


回调

    public function paySuccess(){  
        $options = $this->options();  
        $app = new Application($options);  

        $response = $app->payment->handleNotify(function($notify, $successful){  
            // 使用通知里的 "微信支付订单号" 或者 "商户订单号" 去自己的数据库找到订单  
            $order = TradeModel::where('out_trade_no',$notify->out_trade_no)->first(); 
            $user  = UserModel::where('openid',$order['openid'])->first();         //找到用户
           
            if (count($order) == 0) { // 如果订单不存在  
                return 'Order not exist.'; // 告诉微信，我已经处理完了，订单没找到，别再通知我了  
            }  
            // 如果订单存在  
            // 检查订单是否已经更新过支付状态  
            if ($order->time_expire) {  // 假设订单字段“支付时间”不为空代表已经支付  
                
                $order->Status = 1;     //支付成功,  
             
                return true;           // 已经支付成功了就不再更新了  
            }  
            // 用户是否支付成功  
            if ($successful) {  
                // 不是已经支付状态则修改为已经支付状态  
                $order->time_expire = date("Y-m-d-H:i:s");    // 更新支付时间为当前时间  
                $order->Status = 1; //支付成功,
           
            } else { // 用户支付失败  
                $order->Status = 0; //待付款  
            }  
            $order->save(); // 保存订单  
            return true; // 返回处理完成  
        });  
          return $response;        
    }  
 

# 前端H5页面 #

相关 js 配置如下

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