<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ip extends Model
{
    protected $api = 'https://api01.aliyun.venuscn.com/ip';
    protected $appcode  = '';

    public function __construct($appcode)
    {
        $this->appcode = $appcode;
    }

    public function getlocation($ip = ''){
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $this->appcode);
        $url = $this->api . "?ip=" . $ip;
        if (!isset($this->appcode)){
            return 'apicode未配置';
        }
        $data = json_decode($this->https_request_GET($url, $headers),true);
        $res = $data["data"];
        $province = $res["region"].$res["city"];
        return $province;
    }

    public function https_request_GET($url, $headers) {

        $ch= curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    // 将curl_exec()获取的信息以文件流的形式返回,不直接输出。
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);    // 连接等待时间
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);           // curl允许执行时间
//        curl_setopt($ch, CURLOPT_POST, 1);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
        $data = curl_exec($ch);

        if($data=== FALSE ){
            $data = "CURL Error:".curl_error($ch);
        }
        curl_close($ch);


        return $data;
    }
}
