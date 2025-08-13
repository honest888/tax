<?php


namespace app\mobile\controller;



class KuaiDi100 extends Base
{
//    private $Key             =       "uvCnGQKk7654";
    private $Key;
//    private $Customer        =       "2A6670DA81CF003CB8B3E4507CAE85C0";
    private $Customer;
//    private $Secret          =       "404c78b11fc54689beced49970c1e310";
    private $Secret          =       "38c13650d87c41658ecf83908e3ca94e";
    private $url             =       "http://poll.kuaidi100.com/poll/query.do";    //实时查询请求地址
    protected function initialize()
    {
        parent::initialize();
        $this->Key=getSysConfig('ship.ship_key');
        $this->Customer=getSysConfig('ship.ship_customer');
    }
    /**
     * @param $com 快递公司编码
     * @param $num 快递单号
     * @param string $phone 手机号 如果是顺丰 则必须传入
     * https://api.kuaidi100.com/manager/page/document/kdbm 快递编码
     */
    public function query($com,$num,$phone=""){
        $param['com']       =   $com;
        $param['num']       =   $num;
        $param['phone']     =   $phone;
        $param['from']      =   '';//出发地城市
        $param['to']        =   '';//目的地城市
        //添加此字段表示开通行政区域解析功能。0：关闭（默认），1：开通行政区域解析功能以及物流轨迹增加物流状态值，2：开通行政解析功能以及物流轨迹增加物流状态值并且返回出发、目的及当前城市信息
        $param['resultv2']  =   '1';
        //返回结果排序:desc降序（默认）,asc 升序
        $param['order']     =   'desc';

        $post_param         =   json_encode($param);
        //请求参数
        $post_data = array();
        $post_data["customer"] = $this->Customer;
        $post_data["param"] = $post_param;
        $sign = md5($post_data["param"].$this->Key.$post_data["customer"]);
        $post_data["sign"] = strtoupper($sign);

        $params = "";
        foreach ($post_data as $k=>$v) {
            $params .= "$k=".urlencode($v)."&";              //默认UTF-8编码格式
        }
        $post_data = substr($params, 0, -1);

        $result=$this->curl_post($post_data);
        //错误返回示例
        //{
        //    "result": false,
        //    "returnCode": "400",
        //    "message": "找不到对应公司"
        //}
        if ($result['returnCode']){
            return ['code'=>0,'msg'=>$result['message']];
        }else{
            //0	在途	快件处于运输过程中
            //1	揽收	快件已由快递公司揽收
            //2	疑难	快递100无法解析的状态，或者是需要人工介入的状态， 比方说收件人电话错误。
            //3	签收	正常签收
            //4	退签	货物退回发货人并签收
            //5	派件	货物正在进行派件
            //6	退回	货物正处于返回发货人的途中
            //7	转投	货物转给其他快递公司邮寄
            //10	待清关	货物等待清关
            //11	清关中	货物正在清关流程中
            //12	已清关	货物已完成清关流程
            //13	清关异常	货物在清关过程中出现异常
            //14	拒签	收件人明确拒收
            switch ($result['state']){
                case 0:
                    $state_str="运输中";
                    break;
                case 1:
                    $state_str="已揽件";
                    break;
                case 3:
                    $state_str="已签收";
                    break;
                case 5:
                    $state_str="派件中";
                    break;
                case 6:
                    $state_str="退回中";
                    break;
                case 14:
                    $state_str="已拒收";
                    break;
                default:
                    $state_str="未知状态";
            }
            return ['code'=>1,'msg'=>"查询成功",'result'=>$result['data'],'state_str'=>$state_str];
        }
    }
    private function curl_post($post_data){
        //发送post请求
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        $data = str_replace("\"", '"', $result );
        $data = json_decode($data,true);
        return $data;
    }
}