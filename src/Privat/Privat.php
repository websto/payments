<?php

namespace Websto\Payments\Privat;


class Privat
{

    private $url = 'https://payparts2.privatbank.ua/ipp/v2/payment/create';
    private $_public_key;
    private $_private_key;


    public function __construct($public_key, $private_key)
    {
        if (empty($public_key)) {
            throw new InvalidArgumentException('public_key is empty');
        }

        if (empty($private_key)) {
            throw new InvalidArgumentException('private_key is empty');
        }

        $this->_public_key = $public_key;
        $this->_private_key = $private_key;
    }


    private function generateOrderId($orderId,$length = 128){
        $chars = 'abdefhiknrstyzABDEFGHKNQRSTYZ23456789';
        $numChars = strlen($chars);
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= substr($chars, rand(1, $numChars) - 1, 1);
        }

        $stringRes = substr($string,0,(int)strlen($string)-(int)strlen('_'.$orderId)).'_'.$orderId;

        return $stringRes;
    }

    private function generateAnswerSignature ($dataAnsweArr){

        $passwordStore = $this->_private_key;
        $storeId = $this->_public_key;

        $signatureAnswerStr = $passwordStore.
            $storeId.
            $dataAnsweArr['orderId'].
            $dataAnsweArr['paymentState'].
            $dataAnsweArr['message'].
            $passwordStore;

        $signatureAnswer = base64_encode(hex2bin(SHA1($signatureAnswerStr)));

        return $signatureAnswer;

    }

    private function generateSignature ($dataArr){
        $productsString = '';
        $signatureStr = '';
        $amountStr ='';
        $passwordStore ='';
        $signature ='';
        $decimalSeparatorArr = array(",", ".");
        foreach ($dataArr['products'] as $key_product=>$val_product) {
            if(!fmod(round($val_product['price'],2),1)){
                $valProductPrice = round($val_product['price'],2).'00';
            }else{
                $valProductPrice = round($val_product['price'],2);
                $valProductPriceRateArr = explode('.', $valProductPrice);
                if(strlen($valProductPriceRateArr[1])==1){
                    $valProductPrice = $valProductPrice.'0';
                }
            }
            $productPrice = str_replace($decimalSeparatorArr,'',$valProductPrice);

            $productsString .= trim($val_product['name']).$val_product['count'].$productPrice;
        }

        if(!fmod(round($dataArr['amount'],2),1)){
            $dataArrAmount = round($dataArr['amount'],2).'00';
        }else{
            $dataArrAmount = round($dataArr['amount'],2);
            $dataArrAmountRateArr = explode('.', $dataArrAmount);
            if(strlen($dataArrAmountRateArr[1])==1){
                $dataArrAmount = $dataArrAmount.'0';
            }
        }
        $amountStr = str_replace($decimalSeparatorArr,'',$dataArrAmount);

        $signatureStr = $this->_private_key.
            $dataArr['storeId'].
            $dataArr['orderId'].
            $amountStr.
            $dataArr['currency'].
            $dataArr['partsCount'].
            $dataArr['merchantType'].
            $dataArr['responseUrl'].
            $dataArr['redirectUrl'].
            $productsString.
            $this->_private_key;

        $signature = base64_encode(hex2bin(SHA1($signatureStr)));

        return $signature;
    }


    public function cnb_form($data_deal)
    {


        $data_deal['storeId'] = $this->_public_key;
        $data_deal['orderId'] = $this->generateOrderId(substr($data_deal['sum'], 0, -3),50);
        $data_deal['amount'] = $data_deal['sum'];
        $data_deal['currency'] = 980;
        $data_deal['partsCount'] = $data_deal['partsCount'];
        $data_deal['merchantType'] = $data_deal['type'];

        $data_deal['products'] = $data_deal['products'];



        $data_deal['responseUrl'] = ''; //URL, на который Банк отправит результат сделки
        $data_deal['redirectUrl'] = $data_deal['redirectUrl'];
        $data_deal['signature'] = $this->generateSignature($data_deal);

        $data_string = json_encode($data_deal);

        try{
            $curl = curl_init($this->url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json','Accept-Encoding: UTF-8','Content-Type: application/json; charset=UTF-8'));
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            //execute curl
            $response = curl_exec($curl);

            //get execute result
            $curl_errno = curl_errno($curl);
            $curl_error = curl_error($curl);
            $aInfo = @curl_getinfo($curl);
            //close curl
            curl_close($curl);

            if($curl_errno!=0){
                echo 'PRIVATBANK_PAYMENTPARTS_II :: CURL failed ' . $curl_error . '(' . $curl_errno . ')';

            }

            if($aInfo["http_code"]!='200') {
                echo ' HTTP failed ' . $aInfo["http_code"] . '(' . $response . ')';
            }


            $result = json_decode($response,true);

            if ($result['state'] == 'SUCCESS') {
                echo redirect('https://payparts2.privatbank.ua/ipp/v2/payment?token=' . $result[token] . '');
            }else{
                // var_dump($result);
                // exit;
            }


        } catch(Exception $e){
            return false;
        }

    }

    public function callback() {

        $requestPostRaw = file_get_contents('php://input');
        $requestArr = json_decode(trim($requestPostRaw),true);


        $orderIdArr = explode('_',$requestArr['orderId']);
        $order_id = $orderIdArr[1];
        $comment = '';
        $localAnswerSignature = $this->generateAnswerSignature ($requestArr);
        $order_info = 1;

        if ($order_info) {
            if (strcmp($requestArr['signature'], $localAnswerSignature) == 0) {
                switch($requestArr['paymentState']) {
                    case 'SUCCESS':
                        //$order_status_id = $this->config->get('privatbank_paymentparts_pp_completed_status_id');
                        //                  header('Location: '.$this->url->link('checkout/success'));
                        echo 'SUCCESS';
                        break;
                    case 'CANCELED':
                        //$order_status_id = $this->config->get('privatbank_paymentparts_pp_canceled_status_id');
                        echo 'CANCELED';
                        break;
                    case 'FAIL':
                        //$order_status_id = $this->config->get('privatbank_paymentparts_pp_failed_status_id');
                        echo ('PRIVATBANK_PAYMENTPARTS_PP :: PAYMENT FAIL!  ORDER_ID:'.$order_id .' MESSAGE:'. $requestArr['message']);
                        break;
                    case 'REJECTED':
                        // $order_status_id = $this->config->get('privatbank_paymentparts_pp_rejected_status_id');
                        echo ('PRIVATBANK_PAYMENTPARTS_PP :: PAYMENT REJECTED!  ORDER_ID:'.$order_id .' MESSAGE:'. $requestArr['message']);
                        break;
                }

                //$this->model_checkout_order->addOrderHistory($order_id, $order_status_id, $comment);

            } else {
                echo ('PRIVATBANK_PAYMENTPARTS__PP :: RECEIVED SIGNATURE MISMATCH!  ORDER_ID:'.$order_id .' RECEIVED SIGNATURE:'. $requestArr['signature']);
                // $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('config_order_status_id'));
            }
        }

    }



}
