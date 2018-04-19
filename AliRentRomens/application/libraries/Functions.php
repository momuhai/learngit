<?php
class Functions {
//post 异步  data非json
    function index(){
    	echo "1";
    }
    function curlPostArray($url,$data){
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt ( $ch, CURLOPT_SAFE_UPLOAD, FALSE);
        curl_setopt($ch, CURLOPT_URL, $url);//设置链接
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设置是否返回信息
        curl_setopt($ch, CURLOPT_POST, 1);//设置为POST方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//POST数据
        $response = curl_exec($ch);//接收返回信息
        $charset='UTF-8';
        if(!curl_errno($ch)) {
            $info = curl_getinfo($ch);

            $content_type=explode('charset=', $info['content_type']);
            if(count($content_type)==2){
                $charset=$content_type[1];
            }
        }

        if($charset=='GBK'){
            $res=json_decode(iconv("GBK","UTF-8",$response));
        }else{
            $res=json_decode($response);
        }

        curl_close($ch); //关闭curl链接
        return $res;
    }
}

?>