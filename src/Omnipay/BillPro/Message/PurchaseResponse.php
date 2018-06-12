<?php
namespace Omnipay\BillPro\Message;
use DOMDocument;
use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;
/**
 * Billpro XML Response
 */
class PurchaseResponse extends AbstractResponse {

    /**
     * Constructor
     *
     * @param RequestInterface $request Request
     * @param string           $data    Data
     *
     * @access public
     */
    public function __construct(RequestInterface $request, $data) 
    {
        $this->request = $request;
        $this->data = $data;

        // print_r('getMessage(): ' . $this->getMessage()); die;
    }

    /**
     * Get message
     *
     * @access public
     * @return string
     */
    public function getMessage() 
    {
        $message = 'UNKNOWN ERROR';
        if (isset($this->data->Description)) {
            $message = $this->data->Description->__toString();
        }
        return $message;
    }

    /**
     * Get transaction reference
     *
     * @access public
     * @return string
     */
    public function getTransactionReference() 
    {
        if (isset($this->data->Reference)) 
        {
            return $this->data->Reference;
        }
    }

    /**
     * Get is redirect
     *
     * @access public
     * @return boolean
     */
    public function isRedirect() 
    {
        return false;
    }

    /**
     * Get is successful
     *
     * @access public
     * @return boolean
     */
    public function isSuccessful() 
    {
        if (isset($this->data->ResponseCode))
        {
            $responseCode = $this->data->ResponseCode;

            if ($responseCode == '100')
            {
                return true;
            }

            return false;
        }
        
        return false;
    }
}