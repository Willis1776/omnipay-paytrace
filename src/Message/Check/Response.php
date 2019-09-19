<?php

namespace Omnipay\Paytrace\Message\Check;

use Omnipay\Paytrace\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;

class Response extends AbstractResponse
{
    const TRANSACTION_KEY = 'CHECKIDENTIFIER';

    protected $statusCode;

    public function __construct(RequestInterface $request, $data, $statusCode = 200)
    {
        parent::__construct($request, $data);
        $this->statusCode = $statusCode;
    }

    public function isSuccessful()
    {
        return empty($this->data['error']) && $this->getCode() < 400;
    }

    public function getTransactionReference()
    {
        // This is usually correct for payments, authorizations, etc
        if (!empty($this->data['transactions']) && !empty($this->data['transactions'][0]['related_resources'])) {
            foreach (array('sale', 'authorization') as $type) {
                if (!empty($this->data['transactions'][0]['related_resources'][0][$type])) {
                    return $this->data['transactions'][0]['related_resources'][0][$type]['id'];
                }
            }
        }

        // This is a fallback, but is correct for fetch transaction and possibly others
        if (!empty($this->data['id'])) {
            return $this->data['id'];
        }

        return null;
    }

    public function getMessage()
    {
        if (isset($this->data['error_description'])) {
            return $this->data['error_description'];
        }

        if (isset($this->data['message'])) {
            return $this->data['message'];
        }
        
        return null;
    }

    public function getCode()
    {
        return $this->statusCode;
    }

    public function getCardReference()
    {
        if (isset($this->data['id'])) {
            return $this->data['id'];
        }
    }

    // public function isSuccessful()
    // {
    //     return isset($this->data[static::TRANSACTION_KEY]) && !empty($this->data[static::TRANSACTION_KEY])
    //     && (!isset($this->data['ERROR']) || empty($this->data['ERROR']));
    // }
}
