<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Model\Manager;

//todo: create a bundle instead

use Cocorico\CoreBundle\Model\Manager\Exception\TranslationKeyIsInvalid;
use Cocorico\CoreBundle\Model\Manager\Exception\TranslationQuotaExceeded;

class TranslateManager
{

    protected $clientSecret;
    protected $tokenUrl;
    protected $translateUrl;

    /**
     * __construct
     *
     * @param string $clientSecret
     * @param string $tokenUrl
     * @param string $translateUrl
     */
    public function __construct(
        $clientSecret,
        $tokenUrl,
        $translateUrl
    ) {
        $this->clientSecret = $clientSecret;
        $this->tokenUrl = $tokenUrl;
        $this->translateUrl = $translateUrl;

        if(empty($this->clientSecret)) {
            throw new \UnexpectedValueException('Token for translator is missing');
        }
    }

    /**
     * [getAccessToken returns the access token used to generate the translations ]
     *
     * @return mixed
     */
    private function getAccessToken()
    {
        $curlHandler = curl_init();
        $dataString = json_encode('{body}');
        curl_setopt($curlHandler, CURLOPT_POSTFIELDS, $dataString);
        curl_setopt(
            $curlHandler,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($dataString),
                'Ocp-Apim-Subscription-Key: ' . $this->clientSecret
            )
        );
        curl_setopt($curlHandler, CURLOPT_URL, $this->tokenUrl);
        curl_setopt($curlHandler, CURLOPT_HEADER, false);
        curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, true);

        $strResponse = curl_exec($curlHandler);
        curl_close($curlHandler);

        if(preg_match('/Out of call volume quota/i', $strResponse)) {
            throw new TranslationQuotaExceeded('quota exceeded for translation');
        }

        if(preg_match('/invalid subscription key/i', $strResponse)) {
            throw new TranslationKeyIsInvalid('your key is invalid');
        }

        return $strResponse;
    }

    /**
     * @param $requestXml
     * @return mixed
     * @throws \Exception
     */
    private function getTranslateResponse($requestXml)
    {
        $curlHandler = curl_init();

        curl_setopt($curlHandler, CURLOPT_URL, $this->translateUrl);
        curl_setopt(
            $curlHandler,
            CURLOPT_HTTPHEADER,
            array('Authorization: Bearer ' . $this->getAccessToken(), 'Content-Type: text/xml')
        );

        curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandler, CURLOPT_SSL_VERIFYPEER, false);

        if ($requestXml) {
            //Set HTTP POST Request.
            curl_setopt($curlHandler, CURLOPT_POST, true);
            //Set data to POST in HTTP "POST" Operation.
            curl_setopt($curlHandler, CURLOPT_POSTFIELDS, $requestXml);
        }

        $response = curl_exec($curlHandler);
        $curlErrNo = curl_errno($curlHandler);
        if ($curlErrNo) {
            $curlError = curl_error($curlHandler);
            throw new \Exception($curlError);
        }
        curl_close($curlHandler);

        return $response;
    }

    /**
     * getTranslation returns translated string from the server replacing tags
     *
     * @param  string $fromLanguage
     * @param  string $toLanguage
     * @param  array  $text
     * @return string
     */
    public function getTranslation($fromLanguage, $toLanguage, $text = array())
    {
        $responseArray = array();

        if (!$this->clientSecret) {
            return $responseArray;
        }

        $xml = <<<XML
            <TranslateArrayRequest>
                <AppId/>
                <From>{$fromLanguage}</From>
                <Options>
                    <Category xmlns="http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2" />
                    <ContentType xmlns="http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2">text/plain</ContentType>
                    <ReservedFlags xmlns="http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2" />
                    <State xmlns="http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2" />
                    <Uri xmlns="http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2" />
                    <User xmlns="http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2" />
                </Options>
                <Texts>
XML;
        foreach ($text as $inputStr) {
            $inputStr = str_ireplace('<![CDATA', '', $inputStr);
            $xml .= <<<XML
                    <string xmlns="http://schemas.microsoft.com/2003/10/Serialization/Arrays"><![CDATA[{$inputStr}]]></string>
XML;
        }
        $xml .= <<<XML
                </Texts>
                <To>{$toLanguage}</To>
            </TranslateArrayRequest>
XML;

        $response = $this->getTranslateResponse($xml);

        $xmlObj = new \SimpleXMLElement($response);

        if(!isset($xmlObj->TranslateArrayResponse)) {
            throw new \LogicException('Response from translator is incomplete');
        }

        foreach ($xmlObj->TranslateArrayResponse as $translatedArrObj) {
            $responseArray[] = (string)$translatedArrObj->TranslatedText;
        }

        return $responseArray;
    }

}
