<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH.'aop\AopClient.php';
class AliZhimaXcx extends CI_Controller {
	public $aliappid;
	public $rsaPrivateKey;
	public $rsaPrivateKeyFilePath;
	public $alipayrsaPublicKey;
	public $token;
	public $aop;
	public $wxuser;

	public function __construct()
	{
		parent::__construct();
		$this->aop = new AopClient ();
		$this->aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
		$this->aop->appId = '2017051807276496';
		//$this->aop->rsaPrivateKey = $this->rsaPrivateKey;
		$this->aop->rsaPrivateKeyFilePath = '';
		$this->aop->alipayrsaPublicKey ='';
		$this->aop->apiVersion = '1.0';
		$this->aop->signType = 'RSA';
		$this->aop->postCharset = 'GBK';
		$this->aop->format = 'json';
		$this->load->library('functions');
		
	}
	public function index()
	{
		$this->load->view('AliRent/welcome_message');
	    $this->functions->index();
	}
	public function qiqi_unlock($orderno,$lockCode,$createtime,$userid){
		delRedisByKey('AliRent'.$userid);
		$url="http://java.xingoxing.com/api/rent";//"http://server.571cn.com:12580/sg-rest-api/api/rent";
		$data=array(
		  'extOrderNo'=>$orderno,
		  'lockCode'=>$lockCode,//'100000004492',
		  'createTime'=>$createtime
		);
		foreach ($data as $key=>$value){
			$arr[$key] = $key;
		}
		sort($arr); //字典排序的作用就是防止因为参数顺序不一致而导致下面拼接加密不同
		// 2. 将Key和Value拼接
		$str = "5ADC9AD1224C38886394C3FB45BD77FC";
		foreach ($arr as $k => $v) {
			$str = $str.$arr[$k].$data[$v];
		}
		$data['sign']=md5($str);
		$data['channel']='5ADC9AD1224C38886394C3FB45BD77FC';
		$res = $this->object_to_array(json_decode(json_decode($this->curlpost($url, $data))));
		debuglog('开锁结果：'.json_encode($res,JSON_UNESCAPED_UNICODE));
		//		$where=array('lockCode'=>$lockCode);
		//		$check = M('rm_lockorder')->where($where)->find();
		//		debuglog('数据库查询'.json_encode($check));
		//		if($check==null){
		//			M('rm_lockorder')->add($data);
		//		}
		S($lockCode,$orderno,180);
		if($res['code'] == '000000'){
			debuglog('开锁成功');
			$data=array(
			'state'=>'1',
			'msg'=>'开锁成功'
			);
			echo json_encode($data,JSON_UNESCAPED_UNICODE);
			//			M('rm_lockorder')->where($where)->save(array('status'=>'1'));
		}else{
			//			M('rm_lockorder')->where($where)->save(array('status'=>'2'));
			$data=array(
			'state'=>'2',
			'msg'=>'开锁失败,请重试'
			);
			echo json_encode($data,JSON_UNESCAPED_UNICODE);
			$post_arr=array(
					             'QueryType'=>'get_orderinfo',
					             'Params'=>'{"id":"'.$orderno.'"}',
					             'UserGuid'=>'ODh8QHJvbWVucw--'
					             );
					             $url='http://mshop.yiyao365.cn/wsapi/v1/alirent';
					             $res=$this->object_to_array($this->functions->curlPostArray($url, $post_arr));
					             $order_no=$res['ORDER_NO'];
					             $request = new ZhimaMerchantOrderRentCancelRequest ();
					             $bizcontentarray=array(
				             'order_no'=>$order_no,
				             'product_code'=>'w1010100000000002858'
				             );
				             $bizcontent0 = json_encode($bizcontentarray, JSON_UNESCAPED_UNICODE);
				             //echo '0^'.$bizcontent0;die;
				             debuglog('撤销数据'.$bizcontent0);
				             $bizcontent = iconv("UTF-8", "GBK", $bizcontent0);
				             $request->setBizContent($bizcontent);
				             $signData = $request->getApiParas();
				             $sign = $this->aop->rsaSign($signData, $this->aop->signType);
				             $result = $this->aop->execute($request);
				             $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
				             $resultCode = $result->$responseNode->code;
				             debuglog('撤销结果'.$resultCode);
				             if(!empty($resultCode)&&$resultCode == 10000){
				             	$post_arr=array(
					             'QueryType'=>'update_order',
					             'Params'=>'{"isclerk":"1","branchguid":"'.$res['LEASEBRANCHGUID'].'","goodsid":"'.$res['RENT_INFO'].'","orgguid":"88","status":"2","order_no":"'.$order_no.'"}',
					             'UserGuid'=>'ODh8QHJvbWVucw--'
					             );
					             $url='http://mshop.yiyao365.cn/wsapi/v1/alirent';
					             $res=$this->curlpost($url, $post_arr);
					              
				             }else{
				             	$data=array(
				             	    'state'=>'3',
				             	    'msg'=>'开锁失败，请联系工作人员'
				             	    );
				             	     
				             }
		}

	}
//反馈奇奇开锁
	public function qiqi_unlocknotice(){
		debuglog('qiqi开锁信息反馈接收:'.json_encode($_POST));
		$msg=array(
			  'code'=>'000000',
			  'msg'=>'ok'
			  );
			  echo json_encode($msg);
			  $post_arr=array(
					             'QueryType'=>'get_orderinfo',
					             'Params'=>'{"id":"'.S($_POST['lockCode']).'"}',
					             'UserGuid'=>'ODh8QHJvbWVucw--'
					             );
					             $url='http://mshop.yiyao365.cn/wsapi/v1/alirent';
					             $res=$this->object_to_array($this->functions->curlPostArray($url, $post_arr));
					             if($_POST['result']== '0'){
					             	$msg=array(
			                           'code'=>'000000',
			                           'msg'=>'ok'
			                           );
			                           $post_arr=array(
					             'QueryType'=>'update_order',
					             'Params'=>'{"status":"1","order_no":"'.$res['ORDER_NO'].'"}',
					             'UserGuid'=>'ODh8QHJvbWVucw--'
					             );
					             $res=$this->functions->curlPostArray($url, $post_arr);
					             }else{
					             	$msg=array(
			  'status'=>'2',
			  'msg'=>'开锁失败'
			  );

			  $order_no=$res['ORDER_NO'];
			  delRedisByKey('AliRent'.$res['USER_ID']);
			  $request = new ZhimaMerchantOrderRentCancelRequest ();
			  $bizcontentarray=array(
				             'order_no'=>$order_no,
				             'product_code'=>'w1010100000000002858'
				             );
				             $bizcontent0 = json_encode($bizcontentarray, JSON_UNESCAPED_UNICODE);
				             //echo '0^'.$bizcontent0;die;
				             debuglog('撤销数据'.$bizcontent0);
				             $bizcontent = iconv("UTF-8", "GBK", $bizcontent0);
				             $request->setBizContent($bizcontent);
				             $signData = $request->getApiParas();
				             $sign = $this->aop->rsaSign($signData, $this->aop->signType);
				             $result = $this->aop->execute($request);
				             $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
				             $resultCode = $result->$responseNode->code;
				             debuglog('撤销结果'.$resultCode);
				             if(!empty($resultCode)&&$resultCode == 10000){
				             	$post_arr=array(
					             'QueryType'=>'update_order',
					             'Params'=>'{"isclerk":"1","branchguid":"'.$res['LEASEBRANCHGUID'].'","goodsid":"'.$res['RENT_INFO'].'","orgguid":"88","status":"2","order_no":"'.$order_no.'"}',
					             'UserGuid'=>'ODh8QHJvbWVucw--'
					             );
					             $url='http://mshop.yiyao365.cn/wsapi/v1/alirent';
					             $res=$this->functions->curlPostArray($url, $post_arr);
					             //echo json_encode($res,JSON_UNESCAPED_UNICODE);
				             }else{
				             	$data=array(
				             	    'state'=>'3',
				             	    'msg'=>'开锁失败，请联系工作人员'
				             	    );
				             	    //echo json_encode($data,JSON_UNESCAPED_UNICODE);die;
				             }
					             }
					             S($_POST['lockCode'],null);
					             debuglog('qiqi开锁信息反馈:'.json_encode($msg,JSON_UNESCAPED_UNICODE));
	}
	//奇奇关锁通知
	public function qiqi_locknotice(){
		//		$_POST=array(
		//		   'extOrderNo'=>'33905190',
		//		   'createTime'=>'2018-01-16 16:00:27',
		//		   'channel'=>'1007',
		//		   'sign'=>'0d5537a9fbaa874296463a993589015b',
		//		   'lockCode'=>'100000004492'
		//		);
		debuglog('关锁信息：'.json_encode($_POST));
		$where=array(
		   'extOrderNo'=>$_POST['extOrderNo']
		);
		$post_arr=array(
					             'QueryType'=>'get_orderinfo',
					             'Params'=>'{"id":"'.$_POST['extOrderNo'].'"}',
					             'UserGuid'=>'ODh8QHJvbWVucw--'
					             );
					             $url='http://mshop.yiyao365.cn/wsapi/v1/alirent';
					             $res=$this->object_to_array($this->functions->curlPostArray($url, $post_arr));
					             $lockinfo=$this->object_to_array(json_decode($res['LOCKINFO']));
					             $rtime=date("Y-m-d H:i:s");
					             $rentdate=(strtotime($rtime)-strtotime($res['CREATETIME']))%86400/3600;
					             $rent = $res['RENT_AMOUNT'];
					             $hassale = 'no';
					             for($i=0;$i<count($lockinfo);$i++){
					             	if($rentdate<(float)$lockinfo[$i]['SPEC2VALUE'] && $rentdate>=(float)$lockinfo[$i]['SPEC1VALUE']){
					             		$rent = $lockinfo[$i]['USERPRICE'];
					             		$hassale = 'yes';
					             		break;
					             	}
					             }
					             if($hassale == 'no'){
					             	$rent =  ceil(($rentdate - (float)$lockinfo[$i-1]['SPEC2VALUE'])*2)*(float)$res['RENT_AMOUNT']*0.5;
					             	var_dump(($rentdate - (float)$lockinfo[$i-1]['SPEC2VALUE']));
					             	if($rent>30){
					             		$rent = 30;
					             	}
					             }
					             $rent = (String)$rent;
					             //完成订单
					             $request = new ZhimaMerchantOrderRentCompleteRequest ();
					             $bizcontentarray=array(
				                     'order_no'=>$res['ORDER_NO'],
				                     'product_code'=>$res['PRODUCT_CODE'],
				                     'restore_time'=>$rtime,
				                     'pay_amount_type'=>'RENT',
				                     'pay_amount'=>$rent
					             );
					             $bizcontent0 = json_encode($bizcontentarray, JSON_UNESCAPED_UNICODE);
					             //debuglog('转换前数据:'.$bizcontent0);
					             //echo '0^'.$bizcontent0;die;
					             $bizcontent = iconv("UTF-8", "GBK", $bizcontent0);
					             debuglog('转换后数据：'.$bizcontent);
					             $request->setBizContent($bizcontent);
					             $signData = $request->getApiParas();
					             $sign = $this->aop->rsaSign($signData, $this->aop->signType);
					             $result = $this->aop->execute($request);
					             $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
					             $resultCode = $result->$responseNode->code;
					             if(!empty($resultCode)&&$resultCode == 10000){
					             	//$this->success('归还成功！',U('alirent_info',array('token'=>$this->token)));
					             	$post_arr=array(
					             'QueryType'=>'update_order',
					             'Params'=>'{"goodsid":"'.$res['RENT_INFO'].'","branchguid":"'.$res['LEASEBRANCHGUID'].'","money":"'.$rent.'","returngoodstime":"'.$rtime.'","isclerk":"1","iswarning":"2","orgguid":"88","status":"3","order_no":"'.$res['ORDER_NO'].'"}',
					             'UserGuid'=>'ODh8QHJvbWVucw--'
					             );
					             $url='http://mshop.yiyao365.cn/wsapi/v1/alirent';
					             $res=$this->functions->curlPostArray($url, $post_arr);
					             //debuglog('归还结果:'.json_encode($post_arr));
					             //echo json_encode($res,JSON_UNESCAPED_UNICODE);
					              
					             $msg=array(
			      'status'=>'1',
			      'msg'=>'关锁成功'
			      );
			      echo json_encode($msg);


					             } else {
					             	echo "失败".iconv("GBK", "UTF-8",$result->$responseNode->sub_msg);
					             }

	}
	//附近门店距离计算
	public function near_store(){
		$post_arr=array(
					             'QueryType'=>'getBranchInfo',
					             'Params'=>'{"businessesId":"'.$_POST['businessesId'].'","goodsid":"'.$_POST['goodsid'].'"}',
					             'UserGuid'=>'ODh8QHJvbWVucw--'
					             );
					             $url='http://mshop.yiyao365.cn/wsapi/v1/alirent';
					             $res=$this->curlpost($url, $post_arr);
					             echo json_encode($res,JSON_UNESCAPED_UNICODE);
	}
    //主单预计归还时间
	public function firstreturn_time(){
		if($_POST['rentunit'] == 'HOUR_YUAN'){
			$isrent = getRedisByKey('AliRent'.$_POST['userid']);
            if($isrent){
                if($isrent == 'hasrent'){
					echo json_encode($isrent);die;
				}
            }
		}
		$create_time=date("Y-m-d H:i:s");
		$time=(int)$_POST['borrow_cycle'];
		$timestart=$create_time;
		if($time == 31){
			$returntime = date("Y-m-d H:i:s",strtotime("$timestart + 1 month"));
		}else{
			$returntime = date("Y-m-d H:i:s",strtotime("$timestart + $time day"));
		}
		$data=array(
		   'create_time' => $create_time,
		   'return_time' => $returntime
		);
		echo json_encode($data);
	}
//计算预计归还时间  -2017-8-10-
	public function return_time(){
		if(!$_POST['create_time']){
			$create_time=date("Y-m-d H:i:s");
		}else{
			$create_time=$_POST['create_time'];
		}
		$time=(int)$_POST['borrow_cycle'];
		$timestart=$create_time;
		if($time == 31){
			$returntime = date("Y-m-d H:i:s",strtotime("$timestart + 1 month"));
		}else{
			$returntime = date("Y-m-d H:i:s",strtotime("$timestart + $time day"));
		}
		$post_arr=array(
					             'QueryType'=>'get_orderinfo',
					             'Params'=>'{"id":"'.$_POST['parentguid'].'"}',
					             'UserGuid'=>'ODh8QHJvbWVucw--'
					             );
					             $url='http://mshop.yiyao365.cn/wsapi/v1/alirent';
					             $res=$this->functions->curlPostArray($url, $post_arr);
					             $res=$this->object_to_array($res);
					             //					             if(strtotime($res['ESTIMATERETURNDATETIME'])<strtotime($returntime)){
					             //					             	$data = array(
					             //					             	    'data'=>1,
					             //					             	    'time'=>$res['ESTIMATERETURNDATETIME']
					             //					             	);
					             //					             	echo json_encode($data);
					             //					             }else{
					             $data = array(
					             	    'data'=>2,
					             	    'time'=>$returntime
					             );
					             //debuglog('returntime:'.json_encode($data));
					             echo json_encode($data);
					             //					             }

	}
	//用户提货
	public function pick_up(){
		$request = new ZhimaMerchantOrderRentModifyRequest ();
		$time=(int)$_POST['borrow_cycle'];
		$timestart=date("Y-m-d H:i:s");
		if($time == 31){
			$expiry_time = date("Y-m-d H:i:s",strtotime("$timestart + $time day"));
		}else{
			$expiry_time = date("Y-m-d H:i:s",strtotime("$timestart + $time day"));
		}
		$bizcontentarray=array(
				'order_no'=>$_POST['order_no'],
				'product_code'=>$_POST['product_code'],
				'borrow_time'=>$timestart,
				'expiry_time'=>$expiry_time,
		);
		$bizcontent0 = json_encode($bizcontentarray, JSON_UNESCAPED_UNICODE);
		//debuglog('转换前数据:'.$bizcontent0);
		//echo '0^'.$bizcontent0;die;
		$bizcontent = iconv("UTF-8", "GBK", $bizcontent0);
		//debuglog('转换后数据：'.$bizcontent);
		$request->setBizContent($bizcontent);
		$signData = $request->getApiParas();
		$sign = $this->aop->rsaSign($signData, $this->aop->signType);
		$result = $this->aop->execute($request);
		//debuglog('修改结果:'.json_encode($result));
		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		$resultCode = $result->$responseNode->code;
		if(!empty($resultCode)&&$resultCode == 10000){
			$post_arr=array(
					'QueryType'=>'update_order',
					'Params'=>'{"takegoodstime":"'.$timestart.'","returntime":"'.$expiry_time.'","iswarning":"0","numberno":"'.$_POST['numberno'].'","status":"1","order_no":"'.$_POST['order_no'].'"}',
					'UserGuid'=>'ODh8QHJvbWVucw--'
					);
					//debuglog('修改订单的信息：'.json_encode($post_arr));
					$url='http://mshop.yiyao365.cn/wsapi/v1/alirent';
					$res=$this->functions->curlPostArray($url, $post_arr);
					echo json_encode($res);
		} else {
			//echo "失败".iconv("GBK", "UTF-8",$result->$responseNode->sub_msg);
			$msg=array(
            	   'msg'=>'修改失败',
            	   'state'=>'1003'
            	   );
            	   echo json_encode($msg);
		}
	}
	//续租
	public function rent_continue(){
		$request = new ZhimaMerchantOrderRentQueryRequest ();
		$bizcontentarray=array(
				'out_order_no'=>$_POST['out_order_no'],
				'product_code'=>$_POST['product_code']
		);
		$bizcontent0 = json_encode($bizcontentarray, JSON_UNESCAPED_UNICODE);
		//echo '0^'.$bizcontent0;die;
		$bizcontent = iconv("UTF-8", "GBK", $bizcontent0);
		$request->setBizContent($bizcontent);
		//$signData = $request->getApiParas();
		//$sign = $this->aop->rsaSign($signData, $this->aop->signType);
		$result = $this->aop->execute($request);
		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		$resultCode = $result->$responseNode->code;
		if(!empty($resultCode)&&$resultCode == 10000){
			if(!$_POST['create_time']){
				$create_time=date("Y-m-d H:i:s");
			}else{
				$create_time=$_POST['create_time'];
			}
			$time=(int)$_POST['borrow_cycle'];
			$timestart=$create_time;
			if($time == 31){
				$returntime = date("Y-m-d H:i:s",strtotime("$timestart + $time day"));
			}else{
				$returntime = date("Y-m-d H:i:s",strtotime("$timestart + $time day"));
			}
			$order_no = $result->$responseNode->order_no;
			$datas= array (
          'id'=>uniqid(),
		  'create_time'=>$create_time,
		  'return_time'=>$returntime,
          'token'=>$_info['token'],
          'out_order_no'=>$_POST['out_order_no'],
          'order_no'=>$result->$responseNode->order_no,
          'user_id'=>$result->$responseNode->user_id,
          'admit_state'=>$result->$responseNode->admit_state,
          'name'=>'BK',
          'product_code'=>$_POST['product_code'],
          'goods_name'=>$_POST['goods_name'],
          'rent_info'=>$_POST['rent_info'],
          'rent_unit'=>$_POST['rent_unit'],
          'rent_amount'=>$_POST['rent_amount'],
          'deposit_amount'=>$_POST['deposit_amount'],
          'deposit_state'=>$_POST['deposit_state'],
          'borrow_cycle'=>$_POST['borrow_cycle'],
          'borrow_cycle_unit'=>$_POST['borrow_cycle_unit'],
          'borrow_shop_name'=>$_POST['borrow_shop_name'],
		  'leaseprice'=>$_POST['leaseprice'],
          'leasebranchguid'=>$_POST['leasebranchguid'],
          'status'=>'1', 
          'orgguid'=>$_POST['orgguid'],
		  'has_sub'=>$_POST['has_sub'],
		  'parentguid'=>$_POST['parentguid'],
		  'phone'=>$_POST['phone'],
		  'assistantno'=>$_POST['assistantno'],
		  'assistantname'=>$_POST['assistantname'],
		  'assistantphone'=>$_POST['assistantphone'],
			);
			$post_arr=array(
         'QueryType'=>'sub_order',
		 'Params'=>json_encode($datas,true),
		 'UserGuid'=>'ODh8QHJvbWVucw--'
		 );
		 $url='http://mshop.yiyao365.cn/wsapi/v1/alirent';
		 $res=$this->functions->curlPostArray($url, $post_arr);
		 debuglog('续租创建订单:'.$res);
		 //买断完成
		 if($_POST['buy'] == 'ye'){
		 	$request = new ZhimaMerchantOrderRentCompleteRequest ();
		 	$rtime = date("Y-m-d H:i:s");
		 	$bizcontentarray=array(
				'order_no'=>$result->$responseNode->order_no,
				'product_code'=>$_POST['product_code'],
				'restore_time'=>$rtime,
				'pay_amount_type'=>'RENT',
				'pay_amount'=>$_POST['rent_amount']
		 	);
		 	$bizcontent0 = json_encode($bizcontentarray, JSON_UNESCAPED_UNICODE);
		 	//debuglog('转换前数据:'.$bizcontent0);
		 	//echo '0^'.$bizcontent0;die;
		 	$bizcontent = iconv("UTF-8", "GBK", $bizcontent0);
		 	//debuglog('转换后数据：'.$bizcontent);
		 	$request->setBizContent($bizcontent);
		 	$signData = $request->getApiParas();
		 	$sign = $this->aop->rsaSign($signData, $this->aop->signType);
		 	$result = $this->aop->execute($request);
		 	$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		 	$resultCode = $result->$responseNode->code;
		 	if(!empty($resultCode)&&$resultCode == 10000){
		 		//$this->success('归还成功！',U('alirent_info',array('token'=>$this->token)));
		 		$post_arr=array(
					             'QueryType'=>'update_order',
					             'Params'=>'{"goodsid":"'.$_POST['rent_info'].'","branchguid":"'.$_POST['leasebranchguid'].'","money":"'.$_POST['rent_amount'].'","returntime":"'.$rtime.'","returngoodstime":"'.date("Y-m-d H:i:s").'","iswarning":"2","orgguid":"88","status":"7","order_no":"'.$order_no.'"}',
					             'UserGuid'=>'ODh8QHJvbWVucw--'
					             );
					             $url='http://mshop.yiyao365.cn/wsapi/v1/alirent';
					             $res=$this->functions->curlPostArray($url, $post_arr);

		 	} else {
		 		debuglog('买断完成失败');
		 	}
		 }
		 if($_POST['l_parentguid']=='0'){
		 	/*修改订单关系*/
		  $post_arr=array(
		  'QueryType'=>'update_order',
		  'Params'=>'{"has_sub":"1","order_no":"'.$_POST['l_order_no'].'"}',
		  'UserGuid'=>'ODh8QHJvbWVucw--'
		  );
		  $url='http://mshop.yiyao365.cn/wsapi/v1/alirent';
		  $res=$this->functions->curlPostArray($url, $post_arr);
		  //echo json_encode($res,JSON_UNESCAPED_UNICODE);
		 }
		 //完成上一笔订单
		 $request = new ZhimaMerchantOrderRentCompleteRequest ();
		 $bizcontentarray=array(
				'order_no'=>$_POST['l_order_no'],
				'product_code'=>$_POST['l_product_code'],
				'restore_time'=>date("Y-m-d H:i:s"),
				'pay_amount_type'=>'RENT',
				'pay_amount'=>$_POST['l_pay_amount']
		 );
		 $bizcontent0 = json_encode($bizcontentarray, JSON_UNESCAPED_UNICODE);
		 //debuglog('转换前数据:'.$bizcontent0);
		 //echo '0^'.$bizcontent0;die;
		 $bizcontent = iconv("UTF-8", "GBK", $bizcontent0);
		 //debuglog('转换后数据：'.$bizcontent);
		 $request->setBizContent($bizcontent);
		 $signData = $request->getApiParas();
		 $sign = $this->aop->rsaSign($signData, $this->aop->signType);
		 $result = $this->aop->execute($request);
		 $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		 $resultCode = $result->$responseNode->code;
		 if(!empty($resultCode)&&$resultCode == 10000){
		 	//$this->success('归还成功！',U('alirent_info',array('token'=>$this->token)));
		 	$post_arr=array(
					             'QueryType'=>'update_order',
					             'Params'=>'{"money":"'.$_POST['l_pay_amount'].'","returngoodstime":"'.$rtime.'","returngoodstime":"'.date("Y-m-d H:i:s").'","iswarning":"2","orgguid":"88","status":"3","order_no":"'.$_POST['l_order_no'].'"}',
					             'UserGuid'=>'ODh8QHJvbWVucw--'
					             );
					             $url='http://mshop.yiyao365.cn/wsapi/v1/alirent';
					             $res=$this->functions->curlPostArray($url, $post_arr);
					             echo json_encode($res,JSON_UNESCAPED_UNICODE);
		 } else {
		 	echo "失败".iconv("GBK", "UTF-8",$result->$responseNode->sub_msg);
		 }

		} else {
			echo "没有查询到订单".$result->$responseNode;
		}
	}
	//列表页
	public function alirent_info(){
		$info=M('rm_alirent_info')->where(array('token'=>$_GET['token']))->select();
		$this->assign('info',$info);
		$this->display();
	}
	//商家确认订单
	public function sure_rent(){
		$post_arr=array(
					'QueryType'=>'update_order',
					'Params'=>'{"clerkno":"'.$_POST['clerk'].'","isclerk":"1","status":"1","order_no":"'.$_POST['order_no'].'"}',
					'UserGuid'=>'ODh8QHJvbWVucw--'
					);
					$url='http://mshop.yiyao365.cn/wsapi/v1/alirent';
					$res=$this->functions->curlPostArray($url, $post_arr);
					echo json_encode($res,JSON_UNESCAPED_UNICODE);
	}
	//选择租用时间
	public function alirent_howlong(){
		$this->assign('goods_name',$_POST['goods_name']);
		$this->assign('product_code',$_POST['product_code']);
		$this->assign('rent_info',$_POST['rent_info']);
		$this->assign('borrow_shop_name',$_POST['borrow_shop_name']);
		$this->assign('deposit_amount',$_POST['deposit_amount']);
		$this->assign('rent_amount',$_POST['rent_amount']);
		$this->assign('rent_unit',$_POST['rent_unit']);
		$this->display();
	}
	//直接续租查询
	public function toreturn(){
		$post_arr=array(
					'QueryType'=>'get_orderinfo',
					'Params'=>'{"parentguid":"'.$_POST['parentguid'].'","user_id":"'.$_POST['userid'].'"}',
					'UserGuid'=>'ODh8QHJvbWVucw--'
					);
					$url='http://mshop.yiyao365.cn/wsapi/v1/alirent';
					$res=$this->functions->curlPostArray($url, $post_arr);
					echo json_encode($res,JSON_UNESCAPED_UNICODE);

	}
	//发放修改订单日期
	public function updata_time(){
		$_POST=json_encode($_POST);
		//debuglog('传入数据'.$_POST);
		$_POST=json_decode($_POST,true);
		if($_POST['sign']){
			if($_POST['sign']!=md5('ROMENS'.date('Ymd'))){
				$msg=array(
            	   'msg'=>'签名错误',
            	   'state'=>'1001'
            	   );
            	   echo json_encode($msg);exit;
                //exit('ERROR SIGN');
			}
		}else{
			$msg=array(
            	   'msg'=>'未传入签名',
            	   'state'=>'1002'
            	   );
            	   echo json_encode($msg);exit;
            	   //exit('NO SIGN');
		}

		if(!$_POST['order_no']){
			$msg=array(
            	   'msg'=>'未传入订单号',
            	   'state'=>'1004'
            	   );
            	   echo json_encode($msg);exit;
		}
		if(!$_POST['product_code']){
			$msg=array(
            	   'msg'=>'未传入产品号',
            	   'state'=>'1005'
            	   );
            	   echo json_encode($msg);exit;
		}
		if(!$_POST['borrow_time']){
			$msg=array(
            	   'msg'=>'未传入开始时间',
            	   'state'=>'1006'
            	   );
            	   echo json_encode($msg);exit;
		}
		if(!$_POST['borrow_cycle']){
	 	$msg=array(
            	   'msg'=>'未传入租借时间',
            	   'state'=>'1007'
            	   );
            	   echo json_encode($msg);exit;
		}
		$request = new ZhimaMerchantOrderRentModifyRequest ();
		$time=(int)$_POST['borrow_cycle'];
		$timestart=$_POST['borrow_time'];
		if($time == 31){
			$expiry_time = date("Y-m-d 23:59:59",strtotime("$timestart + $time day"));
		}else{
			$expiry_time = date("Y-m-d H:i:s",strtotime("$timestart + $time day"));
		}
		$bizcontentarray=array(
				'order_no'=>$_POST['order_no'],
				'product_code'=>$_POST['product_code'],
				'borrow_time'=>$_POST['borrow_time'],
				'expiry_time'=>$expiry_time,
		        'sign'=>$_POST['sign']
		);
		$bizcontent0 = json_encode($bizcontentarray, JSON_UNESCAPED_UNICODE);
		//debuglog('转换前数据:'.$bizcontent0);
		//echo '0^'.$bizcontent0;die;
		$bizcontent = iconv("UTF-8", "GBK", $bizcontent0);
		//debuglog('转换后数据：'.$bizcontent);
		$request->setBizContent($bizcontent);
		$signData = $request->getApiParas();
		$sign = $this->aop->rsaSign($signData, $this->aop->signType);
		$result = $this->aop->execute($request);
		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		$resultCode = $result->$responseNode->code;
		if(!empty($resultCode)&&$resultCode == 10000){
			$msg=array(
            	   'msg'=>'修改成功',
            	   'state'=>'1',
			       'time'=>$expiry_time
			);
			echo json_encode($msg);
		} else {
			//echo "失败".iconv("GBK", "UTF-8",$result->$responseNode->sub_msg);
			$msg=array(
            	   'msg'=>'修改失败',
            	   'state'=>'1003'
            	   );
            	   echo json_encode($msg);
		}
		//debuglog('res:'.json_encode($result));
	}
	//创建订单
	public function alirent_create(){
		$time=date("YmdHis").rand(1000,9999);
		$request = new ZhimaMerchantOrderRentCreateRequest();
		$bizcontentarray=array(
				'invoke_type'=>'WINDOWS',
				'invoke_return_url'=>'http://weixin.yiyao365.cn/alizhima/return_url.php',
				'invoke_state'=>array(
						'product_code'=>$_POST['product_code'],
						'goods_name'=>$_POST['goods_name'],
						'rent_info'=>$_POST['rent_info'],
						'rent_unit'=>$_POST['rent_unit'],
						'rent_amount'=>$_POST['rent_amount'],
						'deposit_amount'=>$_POST['deposit_amount'],
						'deposit_state'=>'Y',
						'borrow_cycle'=>$_POST['borrow_cycle'],
						'borrow_cycle_unit'=>$_POST['borrow_cycle_unit'],
						'borrow_shop_name'=>$_POST['borrow_shop_name']
		),
				'out_order_no'=>$time,
				'product_code'=>$_POST['product_code'],
				'goods_name'=>$_POST['goods_name'],
				'rent_info'=>$_POST['rent_info'],
				'rent_unit'=>$_POST['rent_unit'],
				'rent_amount'=>$_POST['rent_amount'],
				'deposit_amount'=>$_POST['deposit_amount'],
				'deposit_state'=>'Y',
				'borrow_cycle'=>$_POST['borrow_cycle'],
				'borrow_cycle_unit'=>$_POST['borrow_cycle_unit'],
				'borrow_shop_name'=>$_POST['borrow_shop_name']
		);
		//存入数据库的数据
		$data=array(
				'name'=>'BK',
				'token'=>$this->token,
				'out_order_no'=>$time,
				'product_code'=>$_POST['product_code'],
				'goods_name'=>$_POST['goods_name'],
				'rent_info'=>$_POST['rent_info'],
				'rent_unit'=>$_POST['rent_unit'],
				'rent_amount'=>$_POST['rent_amount'],
				'deposit_amount'=>$_POST['deposit_amount'],
				'deposit_state'=>'Y',
				'borrow_cycle'=>$_POST['borrow_cycle'],
				'borrow_cycle_unit'=>$_POST['borrow_cycle_unit'],
				'borrow_shop_name'=>$_POST['borrow_shop_name']
		);
		$check=M('rm_alirent')->where(array('out_order_no'=>$time))->find();
		if(!$check){
			M('rm_alirent')->add($data);
		}else{
			$this->error('操作失败');die;
		}
		$bizcontent0 = json_encode($bizcontentarray, JSON_UNESCAPED_UNICODE);
		$bizcontent = iconv("UTF-8", "GBK", $bizcontent0);
		$request->setBizContent($bizcontent);
		$result = $this->aop->pageExecute($request,"GET");
		header("Location:$result");
		//echo $result;
	}
	//完成订单
	public function myalirent_complete(){
		$request = new ZhimaMerchantOrderRentCompleteRequest ();
		$rtime=date("Y-m-d H:i:s");
		$bizcontentarray=array(
				'order_no'=>$_POST['order_no'],
				'product_code'=>$_POST['product_code'],
				'restore_time'=>$rtime,
				'pay_amount_type'=>$_POST['pay_amount_type'],
				'pay_amount'=>$_POST['pay_amount']
		);
		$bizcontent0 = json_encode($bizcontentarray, JSON_UNESCAPED_UNICODE);
		//debuglog('转换前数据:'.$bizcontent0);
		//echo '0^'.$bizcontent0;die;
		$bizcontent = iconv("UTF-8", "GBK", $bizcontent0);
		//debuglog('转换后数据：'.$bizcontent);
		$request->setBizContent($bizcontent);
		$signData = $request->getApiParas();
		$sign = $this->aop->rsaSign($signData, $this->aop->signType);
		$result = $this->aop->execute($request);
		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		$resultCode = $result->$responseNode->code;
		if(!empty($resultCode)&&$resultCode == 10000){
			//$this->success('归还成功！',U('alirent_info',array('token'=>$this->token)));
			$post_arr=array(
					             'QueryType'=>'update_order',
					             'Params'=>'{"goodsid":"'.$_POST['goodsid'].'","branchguid":"'.$_POST['branchguid'].'","money":"'.$_POST['pay_amount'].'","returngoodstime":"'.$rtime.'","isclerk":"1","iswarning":"2","orgguid":"88","status":"3","order_no":"'.$_POST['order_no'].'"}',
					             'UserGuid'=>'ODh8QHJvbWVucw--'
					             );
					             $url='http://mshop.yiyao365.cn/wsapi/v1/alirent';
					             $res=$this->functions->curlPostArray($url, $post_arr);
					             //debuglog('归还结果:'.json_encode($post_arr));
					             echo json_encode($res,JSON_UNESCAPED_UNICODE);
		} else {
			echo "失败".iconv("GBK", "UTF-8",$result->$responseNode->sub_msg);
		}
	}
	//完成订单
	public function alirent_complete(){
		$_POST=json_encode($_POST);
		//debuglog('传入数据'.$_POST);
		$_POST=json_decode($_POST,true);
		if($_POST['sign']){
			if($_POST['sign']!=md5('ROMENS'.date('Ymd'))){
				$msg=array(
            	   'msg'=>'签名错误',
            	   'state'=>'1001'
            	   );
            	   echo json_encode($msg,JSON_UNESCAPED_UNICODE);exit;
                //exit('ERROR SIGN');
			}
		}else{
			$msg=array(
            	   'msg'=>'未传入签名',
            	   'state'=>'1002'
            	   );
            	   echo json_encode($msg,JSON_UNESCAPED_UNICODE);exit;
            	   //exit('NO SIGN');
		}

		if(!$_POST['order_no']){
			$msg=array(
            	   'msg'=>'未传入订单号',
            	   'state'=>'1004'
            	   );
            	   echo json_encode($msg,JSON_UNESCAPED_UNICODE);exit;
		}
		if(!$_POST['product_code']){
			$msg=array(
            	   'msg'=>'未传入产品号',
            	   'state'=>'1005'
            	   );
            	   echo json_encode($msg,JSON_UNESCAPED_UNICODE);exit;
		}
		if(!$_POST['pay_amount']){
			$msg=array(
            	   'msg'=>'未传入金额',
            	   'state'=>'1006'
            	   );
            	   echo json_encode($msg,JSON_UNESCAPED_UNICODE);exit;
		}
		if(!$_POST['pay_amount_type']){
	 	$msg=array(
            	   'msg'=>'未传入金额类型',
            	   'state'=>'1007'
            	   );
            	   echo json_encode($msg,JSON_UNESCAPED_UNICODE);exit;
		}
		$request = new ZhimaMerchantOrderRentCompleteRequest ();
		$bizcontentarray=array(
				'order_no'=>$_POST['order_no'],
				'product_code'=>$_POST['product_code'],
				'restore_time'=>date("Y-m-d H:i:s"),
				'pay_amount_type'=>$_POST['pay_amount_type'],
				'pay_amount'=>$_POST['pay_amount']
		);
		$bizcontent0 = json_encode($bizcontentarray, JSON_UNESCAPED_UNICODE);
		debuglog('归还数据:'.$bizcontent0);
		//echo '0^'.$bizcontent0;die;
		$bizcontent = iconv("UTF-8", "GBK", $bizcontent0);
		//debuglog('转换后数据：'.$bizcontent);
		$request->setBizContent($bizcontent);
		$signData = $request->getApiParas();
		$sign = $this->aop->rsaSign($signData, $this->aop->signType);
		$result = $this->aop->execute($request);
		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		$resultCode = $result->$responseNode->code;
		debuglog('归还结果:'.$resultCode.'msg:'.$result->$responseNode->msg.'流水号：'.$result->$responseNode->alipay_fund_order_no);
		if(!empty($resultCode)&&$resultCode == 10000){
			//$this->success('归还成功！',U('alirent_info',array('token'=>$this->token)));
			$msg=array(
            	   'msg'=>'归还成功',
            	   'state'=>'1'
            	   );
            	   echo json_encode($msg,JSON_UNESCAPED_UNICODE);
		} else {
			$request = new ZhimaMerchantOrderRentQueryRequest ();
			$bizcontentarray=array(
		    'out_order_no'=>$_POST['out_order_no'],
	        'product_code'=>$_POST['product_code']
			);
			$bizcontent0 = json_encode($bizcontentarray, JSON_UNESCAPED_UNICODE);
			//echo '0^'.$bizcontent0;die;
			$bizcontent = iconv("UTF-8", "GBK", $bizcontent0);
			$request->setBizContent($bizcontent);
			//$signData = $request->getApiParas();
			//$sign = $this->aop->rsaSign($signData, $this->aop->signType);
			$result = $this->aop->execute($request);
			$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
			$resultCode = $result->$responseNode->code;
			if(!empty($resultCode)&&$resultCode == 10000){
				//echo $result->$responseNode->order_no."<br/>状态:".iconv("GBK", "UTF-8",$result->$responseNode->use_state)."<br/>借出时间:".$result->$responseNode->borrow_time."归还时间:".$result->$responseNode->restore_time;
				if(iconv("GBK", "UTF-8",$result->$responseNode->use_state)== 'restore' && $result->$responseNode->pay_amount == $_POST['pay_amount']){
					$msg=array(
            	   'msg'=>'归还成功',
            	   'state'=>'1'
            	   );
				}else{
					$msg=array(
            	   'msg'=>'归还失败',
            	   'state'=>'1003'
            	   );
				}
			} else {
				$msg=array(
            	   'msg'=>'归还失败',
            	   'state'=>'1003'
            	   );
			}
				
			echo json_encode($msg,JSON_UNESCAPED_UNICODE);
			//debuglog('归还失败:'.iconv("GBK", "UTF-8",$result->$responseNode->sub_msg));
			//echo "失败".iconv("GBK", "UTF-8",$result->$responseNode->sub_msg);
		}
		//debuglog('res:'.json_encode($result));
	}
	//查询订单
	public function alirent_query(){
		$request = new ZhimaMerchantOrderRentQueryRequest ();
		$bizcontentarray=array(
		    'out_order_no'=>$_GET['out_order_no'],
	        'product_code'=>'w1010100000000002858'
	        );
	        $bizcontent0 = json_encode($bizcontentarray, JSON_UNESCAPED_UNICODE);
	        //echo '0^'.$bizcontent0;die;
	        $bizcontent = iconv("UTF-8", "GBK", $bizcontent0);
	        $request->setBizContent($bizcontent);
	        //$signData = $request->getApiParas();
	        //$sign = $this->aop->rsaSign($signData, $this->aop->signType);
	        $result = $this->aop->execute($request);
	        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
	        $resultCode = $result->$responseNode->code;
	        if(!empty($resultCode)&&$resultCode == 10000){
	        	echo $result->$responseNode->order_no."<br/>状态:".iconv("GBK", "UTF-8",$result->$responseNode->use_state)."<br/>借出时间:".$result->$responseNode->borrow_time."归还时间:".$result->$responseNode->restore_time;
	        } else {
	        	echo "失败".$result->$responseNode;
	        }
	}
	//测试撤销
	public function tc(){
		$request = new ZhimaMerchantOrderRentCancelRequest ();
		$bizcontentarray=array(
				'order_no'=>$_GET['order_no'],
				'product_code'=>'w1010100000000002858'
				);
				$bizcontent0 = json_encode($bizcontentarray, JSON_UNESCAPED_UNICODE);
				//echo '0^'.$bizcontent0;die;
				$bizcontent = iconv("UTF-8", "GBK", $bizcontent0);
				$request->setBizContent($bizcontent);
				$signData = $request->getApiParas();
				$sign = $this->aop->rsaSign($signData, $this->aop->signType);
				$result = $this->aop->execute($request);
				$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
				$resultCode = $result->$responseNode->code;
				if(!empty($resultCode)&&$resultCode == 10000){
					echo "成功";
				}else{
					echo "失败";
				}
	}
	//撤销接口
	public function ali_cancel(){
		$request = new ZhimaMerchantOrderRentCancelRequest ();
		$bizcontentarray=array(
				             'order_no'=>$_POST['order_no'],
				             'product_code'=>$_POST['product_code']//'w1010100000000002858'
		);
		$bizcontent0 = json_encode($bizcontentarray, JSON_UNESCAPED_UNICODE);
		//echo '0^'.$bizcontent0;die;
		$bizcontent = iconv("UTF-8", "GBK", $bizcontent0);
		$request->setBizContent($bizcontent);
		$signData = $request->getApiParas();
		$sign = $this->aop->rsaSign($signData, $this->aop->signType);
		$result = $this->aop->execute($request);
		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		$resultCode = $result->$responseNode->code;
		if(!empty($resultCode)&&$resultCode == 10000){
			$data=array(
				             	    'state'=>1,
				             	    'msg'=>'成功'
				             	    );
				             	    echo json_encode($data,JSON_UNESCAPED_UNICODE);
		}else{
			$data=array(
				             	    'state'=>2,
				             	    'msg'=>'失败'
				             	    );
				             	    echo json_encode($data,JSON_UNESCAPED_UNICODE);
		}
	}
	//小程序创建订单
	public function xcx_create(){
		$request = new ZhimaMerchantOrderRentQueryRequest ();
		$bizcontentarray=array(
				'out_order_no'=>$_POST['out_order_no'],
				'product_code'=>$_POST['product_code']
		);
		$cons=$this->object_to_array(json_decode($_POST['consumables']));
		//debuglog('耗材：'.$_POST['consumables']);
		$consumables=array();
		$consumablesamount=0;
		for($i=0;$i<count($cons);$i++){
			$consumables[$i]['consumablesguid']=$cons[$i]['GUID'];
			$consumables[$i]['goodsprice']=$cons[$i]['PRODUCT_PRICE'];
			$consumables[$i]['price']=$cons[$i]['PRODUCT_PRICE'];
			$consumables[$i]['num']=$cons[$i]['num'];
			$consumables[$i]['tamount']=(int)$cons[$i]['PRODUCT_PRICE']*(int)$cons[$i]['num'];
			$consumablesamount=$consumablesamount+$consumables[$i]['tamount'];
		}
		//debuglog('耗材2：'.json_encode($consumables));
		$bizcontent0 = json_encode($bizcontentarray, JSON_UNESCAPED_UNICODE);
		//echo '0^'.$bizcontent0;die;
		$bizcontent = iconv("UTF-8", "GBK", $bizcontent0);
		$request->setBizContent($bizcontent);
		//$signData = $request->getApiParas();
		//$sign = $this->aop->rsaSign($signData, $this->aop->signType);
		$result = $this->aop->execute($request);
		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		$resultCode = $result->$responseNode->code;
		if(!empty($resultCode)&&$resultCode == 10000){
			if(!$_POST['create_time']){
				$create_time=date("Y-m-d H:i:s");
			}else{
				$create_time=$_POST['create_time'];
			}
			$time=(int)$_POST['borrow_cycle'];
			$timestart=$create_time;
			if($time == 31){
				$returntime = date("Y-m-d H:i:s",strtotime("$timestart + 1 month"));
			}else{
				$returntime = date("Y-m-d H:i:s",strtotime("$timestart + $time day"));
			}
			if($_POST['serialno'] == '' && $_POST['lockCode'] == ''){
				$status = '0';
			}else{
				$status = '1';
			}
			$datas= array (
          'id'=>uniqid(),
		  'create_time'=>$create_time,
		  'return_time'=>$returntime,
          'token'=>$_info['token'],
          'out_order_no'=>$_POST['out_order_no'],
          'order_no'=>$result->$responseNode->order_no,
          'user_id'=>$result->$responseNode->user_id,
          'admit_state'=>$result->$responseNode->admit_state,
          'name'=>'BK',
          'product_code'=>$_POST['product_code'],
          'goods_name'=>$_POST['goods_name'],
          'rent_info'=>$_POST['rent_info'],
          'rent_unit'=>$_POST['rent_unit'],
          'rent_amount'=>$_POST['rent_amount'],
          'deposit_amount'=>$_POST['deposit_amount'],
          'deposit_state'=>$_POST['deposit_state'],
          'borrow_cycle'=>$_POST['borrow_cycle'],
          'borrow_cycle_unit'=>$_POST['borrow_cycle_unit'],
          'borrow_shop_name'=>$_POST['borrow_shop_name'],
		  'leaseprice'=>$_POST['leaseprice'],
          'leasebranchguid'=>$_POST['leasebranchguid'],
          'status'=>$status, 
          'orgguid'=>$_POST['orgguid'],
		  'phone'=>$_POST['phone'],
		  'user_name'=>S('user_name'.$result->$responseNode->user_id),
		  'consumables'=>$consumables,
		  'consumablesamount'=>$consumablesamount,
		  'usercouponid'=>$_POST['usercouponid'],
		  'numberno'=>$_POST['serialno'],
		  'lockinfo'=>$_POST['lockinfo'],
		  'lockcode'=>$_POST['lockCode']
		 );
		 //debuglog('XCX_user_name:'.S('user_name'.$result->$responseNode->user_id));
		 $post_arr=array(
         'QueryType'=>'sub_order',
		 'Params'=>json_encode($datas,true),
		 'UserGuid'=>'ODh8QHJvbWVucw--'
		 );
		 $url='http://mshop.yiyao365.cn/wsapi/v1/alirent';
		 $res=$this->functions->curlPostArray($url, $post_arr);
		 //echo $res;
		 if($_POST['lockCode'] != ''){
		 	$isrent = getRedisByKey('AliRent'.$result->$responseNode->user_id);
		 	if(!$isrent){
				setRedisKeyVal('AliRent'.$result->$responseNode->user_id,'hasrent',86400);
			}
		 	$this->qiqi_unlock($datas['id'],$_POST['lockCode'],$create_time,$result->$responseNode->user_id);
		 }else{
		 	echo json_encode($res,JSON_UNESCAPED_UNICODE);
		 	//		 	debuglog('1234'.json_encode($res));
		 	//		 	if($res['state']  == '1'){
		 	//		 		echo json_encode($res,JSON_UNESCAPED_UNICODE);
		 	//		 	}else{
		 	//		 		$request = new ZhimaMerchantOrderRentCancelRequest ();
		 	//		 		$bizcontentarray=array(
		 	//				'order_no'=>$result->$responseNode->order_no,
		 	//				'product_code'=>'w1010100000000002858'
		 	//				);
		 	//				$bizcontent0 = json_encode($bizcontentarray, JSON_UNESCAPED_UNICODE);
		 	//				//echo '0^'.$bizcontent0;die;
		 	//				$bizcontent = iconv("UTF-8", "GBK", $bizcontent0);
		 	//				$request->setBizContent($bizcontent);
		 	//				$signData = $request->getApiParas();
		 	//				$sign = $this->aop->rsaSign($signData, $this->aop->signType);
		 	//				$result = $this->aop->execute($request);
		 	//				$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		 	//				$resultCode = $result->$responseNode->code;
		 	//				if(!empty($resultCode)&&$resultCode == 10000){
		 	//					$data=array(
		 	//			'state'=>'2',
		 	//			'msg'=>'系统繁忙请重试'
		 	//			);
		 	//			echo json_encode($data,JSON_UNESCAPED_UNICODE);
		 	//				}else{
		 	//					$data=array(
		 	//			'state'=>'3',
		 	//			'msg'=>'系统繁忙请重试'
		 	//			);
		 	//			echo json_encode($data,JSON_UNESCAPED_UNICODE);
		 	//				}
		 	//		 	}
		 }
		} else {
			echo "没有查询到订单".$result->$responseNode;
		}
	}
	//撤销订单
	public function alirent_cancel(){
		$post_arr=array(
					'QueryType'=>'get_orderinfo',
					'Params'=>'{"id":"'.$_POST['id'].'","orgguid":"88"}',
					'UserGuid'=>'ODh8QHJvbWVucw--'
					);
					$url='http://mshop.yiyao365.cn/wsapi/v1/alirent';
					$res=$this->functions->curlPostArray($url, $post_arr);
					if ($res->STATUS == '0' || $res->STATUS == '6'){
						$request = new ZhimaMerchantOrderRentCancelRequest ();
						$bizcontentarray=array(
				            'order_no'=>$_POST['order_no'],
				             'product_code'=>$_POST['product_code']
						);
						$bizcontent0 = json_encode($bizcontentarray, JSON_UNESCAPED_UNICODE);
						//echo '0^'.$bizcontent0;die;
						$bizcontent = iconv("UTF-8", "GBK", $bizcontent0);
						$request->setBizContent($bizcontent);
						$signData = $request->getApiParas();
						$sign = $this->aop->rsaSign($signData, $this->aop->signType);
						$result = $this->aop->execute($request);
						$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
						$resultCode = $result->$responseNode->code;
						if(!empty($resultCode)&&$resultCode == 10000){
							//echo "成功";
							$post_arr=array(
					             'QueryType'=>'update_order',
					             'Params'=>'{"isclerk":"1","branchguid":"'.$_POST['branchguid'].'","goodsid":"'.$_POST['goodsid'].'","orgguid":"88","status":"2","order_no":"'.$_POST['order_no'].'"}',
					             'UserGuid'=>'ODh8QHJvbWVucw--'
					             );
					             $url='http://mshop.yiyao365.cn/wsapi/v1/alirent';
					             $res=$this->functions->curlPostArray($url, $post_arr);
					             echo json_encode($res,JSON_UNESCAPED_UNICODE);
						}else{
							$data=array('state'=>'2');
							echo json_encode($data);
						}
					}else{
						$data=array('state'=>'2');
						echo json_encode($data);
					}

	}

	//借用实体地图上传
	public function alirent_map(){
		$request = new ZhimaMerchantBorrowEntityUploadRequest ();
		$bizcontentarray=array(
		'product_code'=>'w1010100000000002858',
		'category_code'=>'test',
		'entity_code'=>'2016000100010011',
		'longitude'=>'120.41391',
		'latitude'=>'36.077506',
		'entity_name'=>'医用制氧机',
		'address_desc'=>'宁夏路市南软件园3号楼517',
		'office_hours_desc'=>'09:00—22:00',
		'contact_number'=>'0532-85886309',
		'collect_rent'=>'Y',
		'can_borrow'=>'Y',
		'can_borrow_cnt'=>'10',
		'total_borrow_cnt'=>'1',
		'upload_time'=>date("Y-m-d h:i:s")
		);
		$bizcontent0 = json_encode($bizcontentarray, JSON_UNESCAPED_UNICODE);
		//echo '0^'.$bizcontent0;die;
		$bizcontent = iconv("UTF-8", "GBK", $bizcontent0);
		$request->setBizContent($bizcontent);
		$signData = $request->getApiParas();
		$sign = $this->aop->rsaSign($signData, $this->aop->signType);
		$result = $this->aop->execute($request);
		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		$resultCode = $result->$responseNode->code;
		if(!empty($resultCode)&&$resultCode == 10000){
			echo "成功";
		} else {
			echo "失败";
		}
	}
	public function curlpost($url,$data){
		$ch = curl_init(); //初始化curl
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt ( $ch, CURLOPT_SAFE_UPLOAD, FALSE);
		curl_setopt($ch, CURLOPT_URL, $url);//设置链接
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设置是否返回信息
		curl_setopt($ch, CURLOPT_POST, 1);//设置为POST方式
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//POST数据
		$response = curl_exec($ch);//接收返回信息
		$res=json_encode($response);
		if(curl_errno($ch)){//出错则显示错误信息
			echo"错误：";
			print curl_error($ch);
		}
		curl_close($ch); //关闭curl链接
		return $res;
	}
	function object_to_array($obj){
		$_arr = is_object($obj)? get_object_vars($obj) :$obj;
		foreach ($_arr as $key => $val){
			$val=(is_array($val)) || is_object($val) ? $this->object_to_array($val) :$val;
			$arr[$key] = $val;
		}
		return $arr;
	}
}
