<?php

namespace Websto\Payments;

use Websto\Payments\Privat\Privat;
use Websto\Payments\LiqPay\LiqPay;
use Websto\Payments\Bitcoin\Bitcoin;



class Payment {


    protected static $_instance;

    protected $config;

    protected $data;

    protected $param;


    public static function getInstance() {

        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    private function getConfig() {

            $this->config = config('paymentconf');

        if (is_array($this->config)){

            foreach ($this->config as $k=>$v){
                if (!$v['enable']) unset($this->config[$k]);
            }

        }

        return $this->config?:null;
    }

    public function render($data, $view = null)
    {
        if (is_array($data)){
            session()->put('data_',$data);

        }
        if (is_null($view)) {
            $view = 'payment-block::block';
        }
        if (\Request::post()) {

            if ( \Request::header('X-CSRF-Token') != session()->token() ) {

                return;

            }

            $data = session()->get('data_');
            $post = \Request::post()['val'];

            if ($data && $post){

                foreach ($this->getConfig() as $key => $val){

                    foreach ($val['title'] as $k=>$v){

                        if ($post == $k) {

                            $this->param = [
                              'class' =>  $key,
                              'public_key'  =>  $val['public_key'],
                              'private_key' =>  $val['private_key'],
                              'id_pay'      =>  $k,
                              'referrer'    =>  \Request::server('HTTP_REFERER'),
                              'secret'      =>  $val['secret']?:null,
                            ];
                        }
                    }
                }

                  $class = 'init_'.$this->param['class'];
                  if ($this->param['class'])  $this->$class(array_merge($this->param,$data));

            }
            exit;
        }

        return view($view,['pay'=>$this->getConfig()])->render();
    }

    protected function init_LiqPay($param) {

        $class = new LiqPay($param['public_key'], $param['private_key']);

        $array['version'] = 3;
        $array['amount'] = str_replace(",", ".", $param['price']);
        $array['currency'] = 'UAH';  //Можно менять  'EUR','UAH','USD','RUB','RUR'
        $array['description'] = 3;
        $array['result_url'] = $param['referrer']; // URL в Вашем магазине на который покупатель будет переадресован после завершения покупки.
        $array['order_id'] = time();
        $array['sandbox'] = 1; //тестовый заказ

        $html = $class->cnb_form($array);

        echo $html;


    }

    protected function init_Privat($param) {

        $class = new Privat($param['public_key'], $param['private_key']);

        $array['sum'] = str_replace(",", ".", $param['price']);
        $array['products'][] = [
            "name" => "Product #1",
            "count" => 1,
            "price" => str_replace(",", ".", $param['price'])
        ];
        $array['partsCount'] = 3; // Количество частей на которые делится сумма транзакции (Для заключения кредитного договора) Должно быть > 1
        $array['type'] = $param['id_pay']; //Тип кредита, возможные значения: II - Мгновенная рассрочка; PP - Оплата частями; PB - Оплата частями. Деньги в периоде. IA - Мгновенная рассрочка. Акционная.

        $array['redirectUrl'] = $param['referrer']; // URL в Вашем магазине на который покупатель будет переадресован после завершения покупки.

        $html = $class->cnb_form($array);
    }

    protected function init_Bitcoin($param) {

        $class = new Bitcoin($param['public_key'], $param['private_key']);

        $html = $class->cnb_form($param);
    }

    public function payment_callback()
    {

        //bitcoin callback

        $secret = config('paymentconf.Bitcoin.secret');

        // file_put_contents("callback.txt", "GET\n".var_export($_GET,true)."\n\n", FILE_APPEND);
        // file_put_contents("callback.txt", "POST\n".var_export($_POST,true)."\n\n", FILE_APPEND);

        $invoice_id = $_GET['invoice_id'];
        $transaction_hash = $_GET['transaction_hash'];
        $value_in_btc = $_GET['value'] / 100000000;


        if ($_GET['secret'] != $secret) {
            echo 'Invalid Secret';
            return;
        }

        if ($_GET['confirmations'] >= 6) {

            echo "*ok*";
        }else{
            echo "*ok*";
//            echo "Waiting for confirmations";
        }
        exit;

    }







}