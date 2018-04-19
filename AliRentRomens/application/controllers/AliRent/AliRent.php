<?php
require_once APPPATH.'aop/AopClient.php';
require_once APPPATH.'aop/request/ZhimaMerchantOrderRentCreateRequest.php';
require_once APPPATH.'aop/request/ZhimaMerchantOrderRentCancelRequest.php';
require_once APPPATH.'aop/request/ZhimaMerchantOrderRentCompleteRequest.php';
require_once APPPATH.'aop/request/ZhimaMerchantOrderRentQueryRequest.php';
require_once APPPATH.'aop/request/AlipaySystemOauthTokenRequest.php';
require_once APPPATH.'aop/request/AlipayUserInfoShareRequest.php';
require_once APPPATH.'aop/request/AlipayUserUserinfoShareRequest.php';
require_once APPPATH.'aop/request/AlipayOpenAppMiniTemplatemessageSendRequest.php';
class AliRent extends CI_Controller {
	public $aliappid;
	public $rsaPrivateKeyFilePath;
	public $alipayrsaPublicKey;
	public $token;
	public $aop;
	public $wxuser;
    public function __construct()
	{
		parent::__construct();
		$this->aliappid = '2017051807276496';
		//merchant_rsa_private_key.pem·��
		//$this->rsaPrivateKeyFilePath ='';
		$this->alipayrsaPublicKey ='MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA0HPBPqCEGAMxrGpP/YeethRP8XyEBdwVrLgnc4U12mdSY0UGpVqbwBYJdx56Sj0U2uzinREp6IutDpy+Oi8nbAobj8W55+GiK8OT3zAII0C4uDO1O0ddUY0lGhH4KAoDogupYmFVUOA8s3mEj6+ZphGIBOyDBXeSREZf0efL+rDjnv26EIdyFRn7Sg49AIkgW711n8xr0YyW0MF9tsAOjk/zeHPJdsA1IG+TBfW/qJExmAzp1qpgKM3WssWws2ZGB1UsVtfEPQG7rkon8PGrxwm8tf0qcfktIy3Bwk5YLG2OosDG8TwkQYPZRIrlUnLxlh7uwHxbiXr43pKxBwsjVwIDAQAB';
		$this->rsaPrivateKey='MIIEpAIBAAKCAQEA0HPBPqCEGAMxrGpP/YeethRP8XyEBdwVrLgnc4U12mdSY0UGpVqbwBYJdx56Sj0U2uzinREp6IutDpy+Oi8nbAobj8W55+GiK8OT3zAII0C4uDO1O0ddUY0lGhH4KAoDogupYmFVUOA8s3mEj6+ZphGIBOyDBXeSREZf0efL+rDjnv26EIdyFRn7Sg49AIkgW711n8xr0YyW0MF9tsAOjk/zeHPJdsA1IG+TBfW/qJExmAzp1qpgKM3WssWws2ZGB1UsVtfEPQG7rkon8PGrxwm8tf0qcfktIy3Bwk5YLG2OosDG8TwkQYPZRIrlUnLxlh7uwHxbiXr43pKxBwsjVwIDAQABAoIBAQCA+s7cmGeDkB5hR5rdDdh3Y1Qf4OKz2X0T1RKcGRW8YOgKgoBdOhZbIYeTzCjw3KCV4bNKan9a42oeO4A88kZbRFnPeRHR17wHhklt9QNkBL0HRP9jgYHNXx9Q5UN+Ssv6rWqOdBldJJKKnqsWWRoiNoDKQynC7Tx0wHKzp9B/+WrWtD9MbKlkttc/KMyEpRRj2+T9cO0mhwYNbroo+ezKlYhfED/3idgNCrfOhMxhllHmb6jm0BRIUtEuuTpc4O6cSlGFuLNN2ZOye+jcP2ibQLmo7MO4rrX1QUSGfZTg+0OYH3zvgAUjRgeYqiqdqmzHn2PxMsoZxqVtl1ZiqHTRAoGBAPrQmykJ6jF+4TJ9Ic7V8uO3yXPPFn1Z1O5bSZkr6a8pqTNSSvIBtVBKHrrC9fJnxz4q/C7A19G8LCJ6nwvKCBsq79uAWK+6bwQ93Y3dnOzKr7wjc6X+XpEVU+PgzEZIxNkh2MDjuPmxEOxfauWA3UED16Qp80lnoj/oLLDMSfn/AoGBANTC85orT+zPd4rjdffdELdFhvm+vonsX/w3pWmNuM9lWACE88LsFrVLXgcAgAui1qsn6kUHSJ9HEYYMdk7QTz2FVuREGHydaRw1HZ+i5OYcGqOKFNK3wbvXKOap8kToX7ujKquGHe7D6waCSiF33rDeKP3UlOfoNC2en5noZuapAoGBAKMJgSK/IC4GZQq1zokuCBJAgMI4Bk17XG+IhaH8qo3DTgpfXvpLY/oKBEmwu8FT9m8R8BXQIzph0GqlPMekD3rhgUM0/fFVBh9Cu8chHIXMB0oL3Xw0inJS49JIaWDyoormdoiEPtSIZhDQwaLoDmrZvY4n+s5ngE98c7iFQz0vAoGAcISlVdwgCanym4YNpkbIB1SCvGNu2vwiCv3WwcrMeQosjyHA1E4M+FXiZSuTjBPTGXMjhtwCQRHRp6XBj47EyVFSEagdlxGcO+mvP/Riv3sPb3uf5Yx+rXttSweHc3+82TvCXjGwdMwx6CBRWf/NypXC8fJRyY9YwOOJnlh0yvkCgYBuFIP0RlBa7066HL6MYUhLonp6EMGAKNFpGPZzJOMjm/jf0GaSmSiF3NdvTBmR0dQuNjV1GIBXOD2PybCGRD96UexdMt3S16jqrlYGHJSeoIg8h4X/QAUH+LnzO0HvaQTHwt/V+X6SBCtp3cFhTkQABxjSK4kXjla/psfUtkAdsQ==';
		$this->aop = new AopClient ();
		$this->aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
		$this->aop->appId=$this->aliappid;//С����APPID
		$this->aop->alipayrsaPublicKey=$this->alipayrsaPublicKey;
		//$this->aop->rsaPrivateKeyFilePath=APP_PATH.'Lib/ORG/aop/key/merchant_rsa_private_key.pem';


		$rsaPublicKeyFilePath=APP_PATH.'Lib/ORG/aop/key/xcxkey/rsa_public_key.pem';
		//$this->aop->alipayPublicKey=$rsaPublicKeyFilePath;
		$this->aop->rsaPublicKeyFilePath=APP_PATH.'Lib/ORG/aop/key/xcxkey/rsa_public_key.pem';
		$this->aop->rsaPrivateKey=$this->rsaPrivateKey;
		$this->aop->apiVersion = '1.0';
		$this->aop->signType = 'RSA2';
		$this->aop->postCharset = 'GBK';
		$this->aop->format = 'json';
	}
 //��������ģ����Ϣ����
	public function tempmsg(){
		$request = new AlipayOpenAppMiniTemplatemessageSendRequest();
		$bizcontentarray=array(
				'user_template_id'=>'MjM0ZmQ3ZTA3ZGYyMzNhMjllNGFkZjhlZjI3YmMxMGQ=',
				'form_id'=>'MjA4ODkwMjE0NTE1MDc3M18xNTE5Mzc1NTQ0OTgyXzAxMQ==',
				'to_user_id'=>'2088902145150773',
				'page'=>'pages/index/diyindex',
		        'data'=>'{"keyword1": {"value" : "12:00"}, "keyword2": {"value" : "20180808"}, "keyword3": {"value" : "֧����"}}'
		);
		$bizcontent0 = json_encode($bizcontentarray, JSON_UNESCAPED_UNICODE);
		//debuglog('ת��ǰ����:'.$bizcontent0);
		//echo '0^'.$bizcontent0;die;
		$bizcontent = iconv("UTF-8", "GBK", $bizcontent0);
		//debuglog('ת�������ݣ�'.$bizcontent);
		$request->setBizContent($bizcontent);
		$signData = $request->getApiParas();
		$sign = $this->aop->rsaSign($signData, $this->aop->signType);
		$result = $this->aop->execute($request);
		//debuglog('�޸Ľ��:'.json_encode($result));
		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		$resultCode = $result->$responseNode->code;
		debuglog('����ģ����:'.$resultCode.'msg:'.$result->$responseNode->msg);
		if(!empty($resultCode)&&$resultCode == 10000){
			echo "�ɹ�";
		} else {
			//echo "ʧ��".iconv("GBK", "UTF-8",$result->$responseNode->sub_msg);
			var_dump($result->$responseNode);
		}
	}
	//�ж��Ƿ��ڹ黹������
	public function isreturndata(){
		$thisdata=strtotime(date("Y-m-d H:i:s"));
		$endtime=strtotime($_POST['endtime']);
		$retime=$_POST['endtime'];
		$returntime = strtotime("$retime - 1 day");
		if($thisdata>$returntime && $thisdata<=$endtime){
			echo "1";
		}else{
			echo "2";
		}
	}
	//��ȡ�Ż�ȯ
	public function coupon(){
		$post_arr=array(
					             'QueryType'=>'get_goodsinfo',
					             'Params'=>'{"id":"'.$_POST['id'].'","branchid":"'.$_POST['branchid'].'","userid":"'.$_POST['userid'].'"}',
					             'UserGuid'=>'ODh8QHJvbWVucw--'
					             );
					             $url='http://mshop.yiyao365.cn/wsapi/v1/alirent';
					             $res=$this->functions->curlPostArray($url, $post_arr);
					             $res=$this->object_to_array($res);
					             //debuglog('�Ż�ȯ��Ϣ��'.json_encode($res));
					             $result=array();
					             $array0=array(
					                      'NAME'=>'��ѡ���Ż�ȯ',
					                     );
					             $result[0] = $array0;
					             $i=1;
					             foreach ($res['coupon'] as $key=>$val){
					             	if($key != 'CREATETIME'){
					             		$result[$i] = $val;
					             		$i++;
					             	}						             		             	
					             }
					             echo json_encode($result);
	}
	//��������ѡ��
	public function date_chose(){
		if($_POST['create_time']){
			$t=strtotime($_POST['create_time']);
			$create_time=date("Y-m-d",$t);
		}else{
			$create_time=date("Y-m-d");
		};
		$datechose=array();
		for($i=1;$i<31;$i++){
			$datechose[] = $i.'��';
		}
		echo json_encode($datechose);
	}
	//����ʱ������
	public function renttime(){
		$second=floor((strtotime(date("Y-m-d H:i:s"))-strtotime($_POST['createtime'])));
		echo $second;
	}
	//����ת������
	public function get_location(){
		$url='http://api.map.baidu.com/geocoder/v2/?output=json&ak=hZ4eRI4DgupkNCyK5g9qj8lCjSKGMrfc&address='.$_POST['cityname'];
		$res=file_get_contents($url);
		$result=json_decode($res,true);
		$gol=$this->bd_decrypt($result['result']['location']['lng'],$result['result']['location']['lat']);
		//debuglog('ת��������:'.json_encode($gol));
		$data=array(
	       'lng'=>$gol['gg_lon'],
	       'lat'=>$gol['gg_lat']
		);
		echo json_encode($data);
	}
	//�о�����λ�õó���ַ
	public function get_address(){
		$auth_code = $_POST['auth_code'];
		$request = new AlipaySystemOauthTokenRequest();
		$request->setCode($_POST['auth_code']);
		$request->setGrantType('authorization_code');
		$result = $this->aop->execute($request,null,null,1);
		$res=json_decode($result,true);
		$userid=$res['user_id'];
		if(S('xcx_address'.$userid)){
			echo json_encode(S('xcx_address'.$userid),JSON_UNESCAPED_UNICODE);;
		}else{
			$gol=$this->bd_encrypt($_POST['longitude'], $_POST['latitude']);
			$url='http://api.map.baidu.com/geocoder/v2/?output=json&ak=hZ4eRI4DgupkNCyK5g9qj8lCjSKGMrfc&location='.(String)$gol['bd_lat'].','.(String)$gol['bd_lon'];
			//hZ4eRI4DgupkNCyK5g9qj8lCjSKGMrfc
			$res=file_get_contents($url);
			$address=json_decode($res,true);
			$data = $address['result']['addressComponent']['city'];
			//debuglog('����ʡ��:'.json_encode($address));
			S('xcx_address'.$userid,$data,60);
			echo json_encode($data,JSON_UNESCAPED_UNICODE);
		}
	}
    //�о�����λ�õó���ַ
	public function get_local(){
		$auth_code = $_POST['auth_code'];
		$request = new AlipaySystemOauthTokenRequest();
		$request->setCode($_POST['auth_code']);
		$request->setGrantType('authorization_code');
		$result = $this->aop->execute($request,null,null,1);
		$res=json_decode($result,true);
		$userid=$res['user_id'];
		if(S('xcx_address'.$userid)){
			echo json_encode(S('xcx_address'.$userid),JSON_UNESCAPED_UNICODE);;
		}else{
			$gol=$this->bd_encrypt($_POST['longitude'], $_POST['latitude']);
			$url='http://api.map.baidu.com/geocoder/v2/?output=json&ak=hZ4eRI4DgupkNCyK5g9qj8lCjSKGMrfc&location='.(String)$gol['bd_lat'].','.(String)$gol['bd_lon'];
			//hZ4eRI4DgupkNCyK5g9qj8lCjSKGMrfc
			$res=file_get_contents($url);
			$address=json_decode($res,true);
			$data = array(
			     'city'=>$address['result']['addressComponent']['city'],
			     'district'=>$address['result']['addressComponent']['district'],
			     'street'=>$address['result']['addressComponent']['street'],
			     'street_number'=>$address['result']['addressComponent']['street_number']
			);

			S('xcx_address'.$userid,$data,60);
			echo json_encode($data,JSON_UNESCAPED_UNICODE);
		}
	}
	//�л�����
	public function changecity(){
		$orientationList=array(
		0=>array(
		     'id'=>'02',
		     'region'=>'B'
		     ),
		     1=>array(
		     'id'=>'09',
		     'region'=>'J'
		     ),
		     2=>array(
		     'id'=>'15',
		     'region'=>'Q'
		     ),
		     3=>array(
		     'id'=>'17',
		     'region'=>'S'
		     )
		     );
		     $act_addList=array(
		     0=>array(
		     'id'=>'02',
		     'region'=>'B',
		     'city'=>array(
		     0=>array(
		        'id'=>'110100',
		        'name'=>'������'
		        )
		        )
		        ),
//		        1=>array(
//		     'id'=>'09',
//		     'region'=>'J',
//		     'city'=>array(
//		        0=>array(
//		              'id'=>'330400',
//		              'name'=>'������'
//		              )
//		              )
//		              ),
		              1=>array(
		     'id'=>'15',
		     'region'=>'Q',
		     'city'=>array(
		              0=>array(
		        'id'=>'370200',
		        'name'=>'�ൺ��'
		        )
		        )
		        ),
		        2=>array(
		     'id'=>'17',
		     'region'=>'S',
		     'city'=>array(
		        0=>array(
		        'id'=>'310100',
		        'name'=>'�Ϻ���'
		        )
		        )
		        )
		        );
		        $data=array(
		   'orientationList'=>$orientationList,
		   'act_addList'=>$act_addList,
		   'menu'=>'no'
		        );
		        echo json_encode($data,JSON_UNESCAPED_UNICODE);
	}
	//�ߵ�����λ�õó���ַ
	public function gd_get_address(){
		$url="http://restapi.amap.com/v3/geocode/ regeo?location=120.413712,36.076259&key=f97955d5f79b2768313e560acb40ffcd";
		$arr=file_get_contents($url);
		$newarr=json_decode($arr,true);
		print_R($newarr);
	}
	//��ͼ�����ŵ�
	public function nearstore(){
		$post_arr=array(
					             'QueryType'=>'getBranch',
					             'Params'=>'{"lat":"'.$_POST['lat'].'","lng":"'.$_POST['lng'].'"}',
					             'UserGuid'=>'ODh8QHJvbWVucw--'
					             );
					             $url='http://mshop.yiyao365.cn/wsapi/v1/alirent';
					             $res=$this->functions->curlPostArray($url, $post_arr);
					             $res=$this->object_to_array($res);
					             //debuglog('store:'.json_encode($res));
					             //echo"<pre>";
					             //var_dump($res);
					             for($i=0;$i<10;$i++){
					             	$gol=$this->bd_decrypt($res[$i]['lng'], $res[$i]['lat']);
					             	$lat=(String)$gol['gg_lat'];
					             	$lng=(String)$gol['gg_lon'];
					             	$result[$i]=array(
					                'iconPath'=>'http://romens-10034140.image.myqcloud.com/conew_2_dingwei.png?imageView2/100/w/640/h/0/format/png/q/85',
					                'id'=>$res[$i]['ORGGUID'],
					                'latitude'=>$lat,
					                'longitude'=>$lng,
					                'width'=>25,
					                'height'=>25,
					                'title'=>$res[$i]['NAME'],
					             	'callout'=>array('content'=>$res[$i]['ADDRESS'])
					             	);
					             }
					             echo json_encode($result);

					             //echo json_encode($res,JSON_UNESCAPED_UNICODE);
	}
	//�̼Ұ��½У��
	public function login(){
		echo "1";
	}
	//�����ŵ�������
	public function near_store(){
		$gol=$this->bd_encrypt($_POST['longitude'], $_POST['latitude']);
					             	$lat=(String)$gol['bd_lat'];
					             	$lng=(String)$gol['bd_lon'];
		//echo $_POST['latitude'];die;
		$post_arr=array(
					             'QueryType'=>'getBranchInfo',
					             'Params'=>'{"businessesId":"'.$_POST['businessesId'].'","goodsid":"'.$_POST['goodsid'].'"}',
					             'UserGuid'=>'ODh8QHJvbWVucw--'
					             );
					             $url='http://mshop.yiyao365.cn/wsapi/v1/alirent';
					             $res=$this->functions->curlPostArray($url, $post_arr);
					             $res=$this->object_to_array($res);
					             //�������
					             for($i=0;$i<count($res);$i++){
					             	$dis=$this->getDistance($lat,$lng,$res[$i]['lat'],$res[$i]['lng']);
					             	$res[$i]['dis']=(float)($dis/1000);
					             }
					             //����������
					             $len=count($res);
					             for($k=0;$k<=$len;$k++)
					             {
					             	for($j=$len-1;$j>$k;$j--){
					             		if($res[$j]['dis']<$res[$j-1]['dis']){
					             			$temp = $res[$j];
					             			$res[$j] = $res[$j-1];
					             			$res[$j-1] = $temp;
					             		}
					             	}
					             }
					             //KM��M����ʾ
					             for($i=0;$i<count($res);$i++){
					             	if($res[$i]['dis']<1){
					             		$res[$i]['dis']=(int)($res[$i]['dis']*1000).'m';
					             	}else{
					             		$res[$i]['dis']=(int)($res[$i]['dis']).'km';
					             	}
					             }
					             //debuglog('�ŵ���Ϣ��'.json_encode($res[0],JSON_UNESCAPED_UNICODE));
					             echo json_encode($res,JSON_UNESCAPED_UNICODE);
	}
	//��ȡuser_id
	public function get_user_id(){
		$auth_code = $_POST['auth_code'];
		if($_POST['check']){
			$this->aop->appId='2017092608942796';
		}
		$request = new AlipaySystemOauthTokenRequest();
		$request->setCode($_POST['auth_code']);
		$request->setGrantType('authorization_code');
		//$signData = $request->getApiParas();
		//debuglog('sign:'.json_encode($signData));
		//$sign = $this->aop->rsaSign($signData, $this->aop->signType);
		//debuglog('sign data:'.$sign);
		/*$result = $this->aop->execute($request);
		 $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		 $resultCode = $result->$responseNode->code;
		 debuglog('result data1:'.$resultCode);
		 if(!empty($resultCode)&&$resultCode == 10000){
		 echo "�ɹ�".$result->$responseNode->user_id;
		 } else {
		 echo "ʧ��".$result->$responseNode->code;
		 }*/
		$result = $this->aop->execute($request,null,null,1);
		//debuglog('userid:'.json_encode($result));
		$res=json_decode($result,true);
		exit($res['user_id']);
	}

	//��ȡuser_tel
	public function get_user_tel(){
		$auth_code = $_POST['auth_code'];
		$request = new AlipaySystemOauthTokenRequest();
		$request->setCode($_POST['auth_code']);
		$request->setGrantType('authorization_code');
		//$signData = $request->getApiParas();
		//debuglog('sign:'.json_encode($signData));
		//$sign = $this->aop->rsaSign($signData, $this->aop->signType);
		//debuglog('sign data:'.$sign);
		/*$result = $this->aop->execute($request);

		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		$resultCode = $result->$responseNode->code;
		debuglog('result data1:'.$resultCode);
		if(!empty($resultCode)&&$resultCode == 10000){
		echo "�ɹ�".$result->$responseNode->user_id;
		} else {
		echo "ʧ��".$result->$responseNode->code;
		}*/
		$result = $this->aop->execute($request,null,null,1);
		$res=json_decode($result,true);
		$access_token = $res['access_token'];
		$request = new AlipayUserInfoShareRequest ();
		$signData = $request->getApiParas();
		$sign = $this->aop->rsaSign($signData, $this->aop->signType);
		$result = $this->aop->execute($request,$access_token);
		//debuglog('userinfo:'.json_encode($result));
		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		$resultCode = $result->$responseNode->code;
		$tel = $result->$responseNode->mobile;
		$userid = $result->$responseNode->user_id;
		$username = $result->$responseNode->user_name;
		$user_name=iconv('GB2312', 'UTF-8',$username);
		S('user_name'.$userid,null);
		if(!empty($resultCode)&&$resultCode == 10000){
			echo json_encode($tel);
		}else{
			echo "ʧ��";
		}
	}
	//��ȡ�û�������Ϣ
	public function get_user_info(){
		$auth_code = $_POST['auth_code'];
		$request = new AlipaySystemOauthTokenRequest();
		$request->setCode($_POST['auth_code']);
		$request->setGrantType('authorization_code');
		//$signData = $request->getApiParas();
		//debuglog('sign:'.json_encode($signData));
		//$sign = $this->aop->rsaSign($signData, $this->aop->signType);
		//debuglog('sign data:'.$sign);
		/*$result = $this->aop->execute($request);

		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		$resultCode = $result->$responseNode->code;
		debuglog('result data1:'.$resultCode);
		if(!empty($resultCode)&&$resultCode == 10000){
		echo "�ɹ�".$result->$responseNode->user_id;
		} else {
		echo "ʧ��".$result->$responseNode->code;
		}*/
		$result = $this->aop->execute($request,null,null,1);
		$res=json_decode($result,true);
		$access_token = $res['access_token'];
		$request = new AlipayUserInfoShareRequest ();
		$signData = $request->getApiParas();
		$sign = $this->aop->rsaSign($signData, $this->aop->signType);
		$result = $this->aop->execute($request,$access_token);
		//debuglog('userinfo:'.json_encode($result));
		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		$resultCode = $result->$responseNode->code;
		$tel = $result->$responseNode->mobile;
		$userid = $result->$responseNode->user_id;
		$username = $result->$responseNode->user_name;
		$user_name=iconv('GB2312', 'UTF-8',$username);
		$data = array(
		  'userid'=>$userid,
		  'username'=>$username,
		  'tel'=>$tel
		);
					             //S($userid.$data);
					             if(!empty($resultCode)&&$resultCode == 10000){
					             	//������Ա��
		$post_arr=array(
					             'QueryType'=>'addUser',
					             'Params'=>'{"orgguid":"88","id":"'.$userid.'","name":"'.$username.'","phone":"'.$tel.'"}',
					             'UserGuid'=>'ODh8QHJvbWVucw--'
					             );
					             $urls='http://mshop.yiyao365.cn/wsapi/v1/alirent';
					             $ress=$this->functions->curlPostArray($urls, $post_arr);
					             //debuglog('������Ա���:'.json_encode($ress));
					             	echo json_encode($data,JSON_UNESCAPED_UNICODE);
					             }else{
					             	echo "ʧ��";
					             }
	}
	public function index(){
		$post_arr=array(
         'QueryType'=>'get_orderinfo',
		 'Params'=>'{"orgguid":"88"}',
		 'UserGuid'=>'ODh8QHJvbWVucw--'
		 );
		 $url='http://mshop.yiyao365.cn/wsapi/v1/alirent';
		 $res=$this->curlpost($url, $post_arr);
		 $result=json_decode($res,true);
		 date_default_timezone_set("PRC");
		 for($i=0;$i<count($result);$i++){
		 	$time=(int)$result[$i]['BORROW_CYCLE'];
		 	$timestart=$result[$i]['CREATETIME'];
		 	$returntime[$i] = date("Y-m-d H:i:s",strtotime("$timestart + $time day"));
		 	$result[$i]['returntime']=$returntime[$i];
		 	if(strtotime($returntime[$i])<strtotime(date("Y-m-d H:i:s")) && $result[$i]['STATUS']=='1'){
		 		$result[$i]['STATUS']='5';
		 	}
		 }
		 var_dump($result[0]);
		 $this->assign('data',$result);
		 $this->display();
	}
	//��ά�������б�ҳ
	public function order_list(){
		$post_arr=array(
					             'QueryType'=>'get_goodsinfo',
					             'Params'=>'{"orgguid":"88"}',
					             'UserGuid'=>'ODh8QHJvbWVucw--'
					             );
					             $url='http://mshop.yiyao365.cn/wsapi/v1/alirent';
					             $res=$this->object_to_array($this->functions->curlPostArray($url, $post_arr));
					             $this->assign('info',$res);
					             $this->display();
	}
	//������תС��������(��ά��)
	public function goto_order(){
		$info="code=".$_GET['code'];
		$info=urlencode($info);
		$html='alipays://platformapi/startapp?appId=2017051807276496&query='.$info;
		echo $html;
		//header("Location:$html");
	}
	//������Ʒ
	public function updata_status(){
		$post_arr=array(
					'QueryType'=>'update_order',
					'Params'=>'{"status":"1","order_no":"'.$_GET['order_no'].'"}',
					'UserGuid'=>'ODh8QHJvbWVucw--'
					);
					$url='http://mshop.yiyao365.cn/wsapi/v1/alirent';
					$res=$this->functions->curlPostArray($url, $post_arr);
					if($res->state == '1'){
						$this->success('�ѷ���',U('index',array('token'=>$_GET['token'])));
					}
	}
	public function to_return(){
		$post_arr=array(
					'order_no'=>$_GET['order_no'],
					'product_code'=>$_GET['product_code'],
					'pay_amount'=>$_GET['pay_amount'],
		            'pay_amount_type'=>$_GET['pay_amount_type']
		);
		$_POST=json_encode($post_arr,JSON_UNESCAPED_UNICODE);
		$url='http://weixin.yiyao365.cn/index.php?g=User&m=AliZhimaXcx&a=myalirent_complete&token=fdpbbq1480645793';
		$res=$this->curlpost($url, $post_arr);
		$check = json_decode($res,true);
		if($check['state'] == '1' && $_GET['pay_amount_type'] == 'RENT'){
			$this->success('�ѹ黹',U('index',array('token'=>$_GET['token'])));
		}else if($check['state'] == '1' && $_GET['pay_amount_type'] == 'DAMAGE'){
			$this->success('���⸶',U('index',array('token'=>$_GET['token'])));
		}else{
			$this->error('����ʧ��',U('index',array('token'=>$_GET['token'])));
		}
	}
	//��������
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
		//�������ݿ������
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
			$this->error('����ʧ��');die;
		}
		$bizcontent0 = json_encode($bizcontentarray, JSON_UNESCAPED_UNICODE);
		$bizcontent = iconv("UTF-8", "GBK", $bizcontent0);
		$request->setBizContent($bizcontent);
		$result = $this->aop->pageExecute($request,"GET");
		//header("Location:$result");
		echo $result;
	}
	//��ע�����
	public function isfocuson(){
		$data =array(
		   'EventType'=>'follow',
		   'ActionParam'=>array(
		     'scene'=>array(
		         'sceneId'=>'tinyApp'
		      ),
		   ),
		   'FromAlipayUserId'=>'2088902145150773'
		);
		if($data['EventType'] == 'follow'){
			if($data['ActionParam']['scene']['sceneId'] == 'tinyApp'){
				$post_arr=array(
					             'QueryType'=>'updatefocus',
					             'Params'=>'{"userid":"'.$data['FromAlipayUserId'].'","isfocuson":"1"}',
					             'UserGuid'=>'ODh8QHJvbWVucw--'
					             );
					             $url='http://mshop.yiyao365.cn/wsapi/v1/alirent';
					             $res=$this->functions->curlPostArray($url, $post_arr);
					             echo json_encode($res,JSON_UNESCAPED_UNICODE);
			}
		}else{
			$post_arr=array(
					             'QueryType'=>'updatefocus',
					             'Params'=>'{"userid":"'.$data['FromAlipayUserId'].'","isfocuson":"2"}',
					             'UserGuid'=>'ODh8QHJvbWVucw--'
					             );
					             $url='http://mshop.yiyao365.cn/wsapi/v1/alirent';
					             $res=$this->functions->curlPostArray($url, $post_arr);
					             echo json_encode($res,JSON_UNESCAPED_UNICODE);
		}
	}
	//��ѯ����
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
	        	echo "������:".$result->$responseNode->order_no."<br/>����:".iconv("GBK", "UTF-8",$result->$responseNode->goods_name);
	        } else {
	        	echo "ʧ��".$result->$responseNode;
	        }
	}
	//�޸Ķ���״̬
	public function test_cx(){
		$post_arr=array(
					             'QueryType'=>'update_order',
					             'Params'=>'{"status":"2","order_no":"'.$_GET['order_no'].'"}',
					             'UserGuid'=>'ODh8QHJvbWVucw--'
					             );
					             $url='http://mshop.yiyao365.cn/wsapi/v1/alirent';
					             $res=$this->functions->curlPostArray($url, $post_arr);
					             echo json_encode($res,JSON_UNESCAPED_UNICODE);
	}
	//�����ӿ�
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
				             	    'msg'=>'�ɹ�'
				             	    );
				             	    echo json_encode($data,JSON_UNESCAPED_UNICODE);
		}else{
			$data=array(
				             	    'state'=>2,
				             	    'msg'=>'ʧ��'
				             	    );
				             	    echo json_encode($data,JSON_UNESCAPED_UNICODE);
		}
	}
	//���Գ���
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
					             echo "�ɹ�";
				             }else{
				             	echo "ʧ��";
				             }
	}
	//��������
	public function alirent_cancel(){
		$post_arr=array(
					'QueryType'=>'get_orderinfo',
					'Params'=>'{"id":"'.$_POST['id'].'","orgguid":"88"}',
					'UserGuid'=>'ODh8QHJvbWVucw--'
					);
					$url='http://mshop.yiyao365.cn/wsapi/v1/alirent';
					$res=$this->functions->curlPostArray($url, $post_arr);
					if ($res->STATUS == '0'){
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
							//echo "�ɹ�";
							$post_arr=array(
					             'QueryType'=>'update_order',
					             'Params'=>'{"status":"2","order_no":"'.$_POST['order_no'].'"}',
					             'UserGuid'=>'ODh8QHJvbWVucw--'
					             );
					             $url='http://mshop.yiyao365.cn/wsapi/v1/alirent';
					             $res=$this->functions->curlPostArray($url, $post_arr);
					             echo "�ɹ�";
						}else{
							echo "ʧ��";
						}
					}else{
						echo "��Ʒ�Ѿ�����";
					}

	}
	//����ʵ���ͼ�ϴ�
	public function alirent_borrowentityupload(){
		$request = new ZhimaMerchantBorrowEntityUploadRequest ();
		$bizcontentarray=array(
		    'product_code'=>'w1010100000000002858',
	        'category_code'=>'',
	        'entity_code'=>'',
	        'longitude'=>'',
	        'latitude'=>'',
	        'entity_name'=>'',
	        'address_desc'=>'',
	        'office_hours_desc'=>'',
	        'contact_number'=>'',
	        'collect_rent'=>'',
	        'can_borrow'=>'',
	        'can_borrow_cnt'=>'',
	        'total_borrow_cnt'=>'',
	        'upload_time'=>''
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
	        	echo "�ɹ�";
	        } else {
	        	echo "ʧ��";
	        }
	}
	public function curlpost($url,$data){
		$ch = curl_init(); //��ʼ��curl
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt ( $ch, CURLOPT_SAFE_UPLOAD, FALSE);
		curl_setopt($ch, CURLOPT_URL, $url);//��������
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//�����Ƿ񷵻���Ϣ
		curl_setopt($ch, CURLOPT_POST, 1);//����ΪPOST��ʽ
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//POST����
		$response = curl_exec($ch);//���շ�����Ϣ
		$res=$response;
		if(curl_errno($ch)){//��������ʾ������Ϣ
			echo"����";
			print curl_error($ch);
		}
		curl_close($ch); //�ر�curl����
		return $res;
	}
	private function object_array($array) {
		if(is_object($array)) {
			$array = (array)$array;
		} if(is_array($array)) {
			foreach($array as $key=>$value) {
				$array[$key] = $this->object_array($value);
			}
		}
		return $array;
	}
	function object_to_array($obj){
		$_arr = is_object($obj)? get_object_vars($obj) :$obj;
		foreach ($_arr as $key => $val){
			$val=(is_array($val)) || is_object($val) ? $this->object_to_array($val) :$val;
			$arr[$key] = $val;
		}
		return $arr;
	}
	//��������侭γ�ȵľ���
	function getDistance($lat1, $lng1, $lat2, $lng2)
	{
		$earthRadius = 6367000; //approximate radius of earth in meters

		/*
		 Convert these degrees to radians
		 to work with the formula
		 */

		$lat1 = ($lat1 * pi() ) / 180;
		$lng1 = ($lng1 * pi() ) / 180;

		$lat2 = ($lat2 * pi() ) / 180;
		$lng2 = ($lng2 * pi() ) / 180;

		/*
		 Using the
		 Haversine formula

		 http://en.wikipedia.org/wiki/Haversine_formula

		 calculate the distance
		 */

		$calcLongitude = $lng2 - $lng1;
		$calcLatitude = $lat2 - $lat1;
		$stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
		$stepTwo = 2 * asin(min(1, sqrt($stepOne)));
		$calculatedDistance = $earthRadius * $stepTwo;
		return round($calculatedDistance);
	}
	//�ߵ�����ת�ٶ�
	public function bd_encrypt($gg_lon,$gg_lat)

	{

		$x_pi = 3.14159265358979324 * 3000.0 / 180.0;

		$x = $gg_lon;

		$y = $gg_lat;

		$z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);

		$theta = atan2($y, $x) - 0.000003 * cos($x * $x_pi);

		$data['bd_lon'] = $z * cos($theta) + 0.0065;

		$data['bd_lat'] = $z * sin($theta) + 0.006;

		return $data;

	}
	//�ٶ����ת�ߵ�����
	function bd_decrypt($bd_lon,$bd_lat)
	{
		$x_pi = 3.14159265358979324 * 3000.0 / 180.0;
		$x = $bd_lon - 0.0065;
		$y = $bd_lat - 0.006;
		$z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
		$theta = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
		$data['gg_lon'] = $z * cos($theta);
		$data['gg_lat'] = $z * sin($theta);
		return $data;
	}
}
?>