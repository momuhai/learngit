<?php
class Functions {
//post �첽  data��json
    function index(){
    	echo "1";
    }
    function curlPostArray($url,$data){
        $ch = curl_init(); //��ʼ��curl
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt ( $ch, CURLOPT_SAFE_UPLOAD, FALSE);
        curl_setopt($ch, CURLOPT_URL, $url);//��������
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//�����Ƿ񷵻���Ϣ
        curl_setopt($ch, CURLOPT_POST, 1);//����ΪPOST��ʽ
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//POST����
        $response = curl_exec($ch);//���շ�����Ϣ
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

        curl_close($ch); //�ر�curl����
        return $res;
    }
}

?>