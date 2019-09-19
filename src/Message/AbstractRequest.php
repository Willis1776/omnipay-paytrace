<?php

namespace Omnipay\Paytrace\Message;

use Omnipay\Common\Exception\InvalidResponseException;

abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    protected $method;
    protected $type;
    protected $responseClass;

    public function sendData($data)
    {
        $body = $this->toJSON($data);
        $requestUrl = $this->getEndpoint();
        
        try {
            $httpResponse = $this->httpClient->request(
                $this->getHttpMethod(),
                $this->getEndpoint(),
                array(
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->getToken(),
                    'Content-type' => 'application/json',
                ),
                $body
            );

            $body = (string) $httpResponse->getBody()->getContents();
            $jsonToArrayResponse = !empty($body) ? json_decode($body, true) : array();

            return $this->response = $this->createResponse($jsonToArrayResponse, $httpResponse->getStatusCode());

        } catch (\Exception $e) {
            throw new InvalidResponseException(
                'Error communicating with payment gateway: ' . $e->getMessage(),
                $e->getCode()
            );
        }
    }

    protected function getHttpMethod()
    {
        return 'POST';
    }

    public function toJSON($data, $options = 0)
    {
        // Because of PHP Version 5.3, we cannot use JSON_UNESCAPED_SLASHES option
        // Instead we would use the str_replace command for now.
        // TODO: Replace this code with return json_encode($this->toArray(), $options | 64); once we support PHP >= 5.4
        if (version_compare(phpversion(), '5.4.0', '>=') === true) {
            return json_encode($data, $options | 64);
        }
        return str_replace('\\/', '/', json_encode($data, $options));
    }

    protected function createResponse($data)
    {
        return $this->response = new Response($this, $data);
    }

    public function getUserName()
    {
        return $this->getParameter('username');
    }

    public function setUserName($value)
    {
        return $this->setParameter('username', $value);
    }

    public function getPassword()
    {
        return $this->getParameter('password');
    }

    public function setPassword($value)
    {
        return $this->setParameter('password', $value);
    }

    public function getToken()
    {
        return $this->getParameter('token');
    }

    public function setToken($value)
    {
        return $this->setParameter('token', $value);
    }

    public function getEndpoint()
    {
        return $this->getParameter('endpoint');
    }

    public function setEndpoint($value)
    {
        return $this->setParameter('endpoint', $value);
    }

    public function getInvoiceId()
    {
        return $this->getParameter('invoiceId');
    }

    public function setInvoiceId($value)
    {
        return $this->setParameter('invoiceId', $value);
    }

    public function getCardReference()
    {
        return $this->getParameter('custid');
    }

    public function setCardReference($value)
    {
        return $this->setParameter('custid', $value);
    }

    /**
     * @return \Omnipay\Common\CreditCard|\Omnipay\Paytrace\Check
     */
    protected function getBillingSource()
    {
        return null; // @codeCoverageIgnore
    }

    protected function getBillingData()
    {
        $data = [
            'AMOUNT' => $this->getAmount(),
            'DESCRIPTION' => $this->getDescription(),
            'INVOICE' => $this->getInvoiceId(),
        ];

        $source = $this->getBillingSource();
        if (!$source) {
            return $data; // @codeCoverageIgnore
        }

        $data['BNAME'] = $source->getBillingName();
        $data['PHONE'] = $source->getPhone();
        $data['EMAIL'] = $source->getEmail();

        $data['BADDRESS'] = $source->getBillingAddress1();
        $data['BADDRESS2'] = $source->getBillingAddress2();
        $data['BCITY'] = $source->getBillingCity();
        $data['BCOUNTRY'] = $source->getBillingCountry();
        $data['BSTATE'] = $source->getBillingState();
        $data['BZIP'] = $source->getBillingPostcode();

        $data['SADDRESS'] = $source->getShippingAddress1();
        $data['SADDRESS2'] = $source->getShippingAddress2();
        $data['SCITY'] = $source->getShippingCity();
        $data['SCOUNTRY'] = $source->getShippingCountry();
        $data['SSTATE'] = $source->getShippingState();
        $data['SZIP'] = $source->getShippingPostcode();

        return $data;
    }

    protected function preparePostData($data)
    {
        $postData = '';
        foreach ($data as $key => $value) {
            if (empty($value)) {
                continue;
            }
            $postData .= urlencode("{$key}~{$value}|");
        }
        return $postData;
    }
}
