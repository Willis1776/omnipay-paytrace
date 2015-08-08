<?php

namespace Omnipay\Paytrace\Message;

class RefundRequest extends AbstractRequest
{
    protected $type = 'Refund';
    protected $responseClass = 'CaptureResponse';

    public function getData()
    {
        $this->validate('amount', 'card');
        $this->getCard()->validate();
        $data = $this->getBaseData();
        $card = $this->getCard();
        $data['AMOUNT'] = $this->getAmount();
        $data['CC'] = $card->getNumber();
        $data['EXPMNTH'] = substr($card->getExpiryYear(), -2);
        $data['EXPYR'] = $card->getExpiryMonth();
        if ($this->getTestMode()) {
            $data['TEST'] = 'Y';
        }
        return $data;
    }
}