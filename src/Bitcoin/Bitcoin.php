<?php

namespace Websto\Payments\Bitcoin;


class Bitcoin
{
    private $_public_key;
    private $_private_key;

    /**
     * Constructor.
     *
     * @param string $public_key
     * @param string $private_key
     *
     * @throws InvalidArgumentException
     */
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


    public function cnb_form($data)
    {

            $callback_url = url('/') . "/payment_callback/?invoice_id=" . rand(0,8) . "&secret=" . $data['secret'] . '&gap_limit=2';

            $get_bit_wallet = "https://api.blockchain.info/v2/receive?key=" . $this->_public_key . "&xpub=" . $this->_private_key . "&callback=" . urlencode($callback_url);

//            print $get_bit_wallet;

            $resp = file_get_contents($get_bit_wallet);
            //print $resp;
            $response = json_decode($resp);
            //print json_encode(array('input_address' => $response->address ));
            print 'GAP =' . $response->gap;
            exit;


    }






}