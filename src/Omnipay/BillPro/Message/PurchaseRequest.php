<?php
namespace Omnipay\BillPro\Message;
use Omnipay\Common\CreditCard;
use Omnipay\Common\Message\AbstractRequest;
use Illuminate\Http\Request;
use DOMDocument;
use SimpleXMLElement;
use DOMImplementation;

/**
 * Omnipay BillPro XML Purchase Request
 */

class PurchaseRequest extends AbstractRequest {

    const EP_HOST_LIVE = 'https://gateway.billpro.com';
    const EP_HOST_TEST = 'https://gateway.billpro.com';
    const EP_PATH = '';

    public function initialize(array $parameters = []) {
        $init = parent::initialize($parameters);

        if (isset($parameters['customerId'])) {
            $this->setParameter('customerId', $parameters['customerId']);
        }

        return $init;
    }

    /**
     * Get accept header
     *
     * @access public
     * @return string
     */
    public function getAcceptHeader() {
        return $this->getParameter('acceptHeader');
    }

    /**
     * Set accept header
     *
     * @param string $value Accept header
     *
     * @access public
     * @return void
     */
    public function setAcceptHeader($value) {
        return $this->setParameter('acceptHeader', $value);
    }

    /**
     * Get merchant
     *
     * @access public
     * @return string
     */
    public function getMerchant() {
        return $this->getParameter('merchant');
    }

    /**
     * Set merchant
     *
     * @param string $value Merchant
     *
     * @access public
     * @return void
     */
    public function setMerchant($value) {
        return $this->setParameter('merchant', $value);
    }

    /**
     * Get password
     *
     * @access public
     * @return string
     */
    public function getPassword() {
        return $this->getParameter('password');
    }

    /**
     * Set password
     *
     * @param string $value Password
     *
     * @access public
     * @return void
     */
    public function setPassword($value) {
        return $this->setParameter('password', $value);
    }

    /**
     * Get user agent header
     *
     * @access public
     * @return string
     */
    public function getUserAgentHeader() {
        return $this->getParameter('userAgentHeader');
    }

    /**
     * Set user agent header
     *
     * @param string $value User agent header
     *
     * @access public
     * @return void
     */
    public function setUserAgentHeader($value) {
        return $this->setParameter('userAgentHeader', $value);
    }

    /**
     * Get data
     *
     * @access public
     * @return \SimpleXMLElement
     */
    public function getData() {
        $this->validate('amount', 'card');
        $this->getCard()->validate();
        $data = new SimpleXMLElement('<Request type="AuthorizeCapture" />');
        $merchant = $data->addChild('AccountID', $this->getMerchant());
        $password = $data->addChild('AccountAuth', $this->getPassword());
        $transaction = $data->addChild('Transaction');

        $transaction->addChild('Reference', $this->getDescription());
        $transaction->addChild('Currency', $this->getCurrency());
        $transaction->addChild('FirstName', $this->getCard()->getFirstName());
        $transaction->addChild('LastName', $this->getCard()->getLastName());
        $transaction->addChild('Amount', $this->getAmount());
        $transaction->addChild('City', $this->getCard()->getBillingCity());
        $transaction->addChild('State', $this->getCard()->getBillingState());
        $transaction->addChild('PostCode', $this->getCard()->getBillingPostcode());
        $transaction->addChild('Country', $this->getCard()->getBillingCountry());
        $transaction->addChild('IPAddress', \Request::ip());
        $transaction->addChild('Phone', $this->getCard()->getPhone());
        $transaction->addChild('Address', $this->getCard()->getBillingAddress1());
        $transaction->addChild('Email', $this->getCard()->getEmail());

        // Card Data
        $transaction->addChild('CardNumber', $this->getCard()->getNumber());
        $transaction->addChild('CardExpMonth', $this->getCard()->getExpiryMonth());
        $transaction->addChild('CardExpYear', $this->getCard()->getExpiryYear());
        $transaction->addChild('CardCVV', $this->getCard()->getCvv());

        return $data;
    }

    /**
     * Send data
     *
     * @param SimpleXMLElement $data Data
     *
     * @access public
     * @return RedirectResponse
     */
    public function sendData($data) {
        $implementation = new DOMImplementation();
        $document = $implementation->createDocument(null, '');
        $document->encoding = 'utf-8';
        $node = $document->importNode(dom_import_simplexml($data), true);

        $document->appendChild($node);
        $headers = [
            'Content-Type'  => 'text/xml; charset=utf-8'
        ];
        $xml = $document->saveXML();

        $httpResponse = $this->httpClient
            ->request('POST', $this->getEndpoint(), $headers, $xml);

        // print_r($httpResponse->getMessage()); print_r($httpResponse->xml()); die;
        return $this->response = new PurchaseResponse($this, simplexml_load_string($httpResponse->getBody()->getContents()));
    }

    /**
     * Get endpoint
     *
     * Returns endpoint depending on test mode
     *
     * @access protected
     * @return string
     */
    protected function getEndpoint() {
        return ($this->getTestMode() ? self::EP_HOST_TEST : self::EP_HOST_LIVE) . self::EP_PATH;
    }
}
