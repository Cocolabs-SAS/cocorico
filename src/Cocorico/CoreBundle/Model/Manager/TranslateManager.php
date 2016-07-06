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

class TranslateManager
{

    protected $clientId;
    protected $clientSecret;
    protected $scopeUrl;
    protected $tokenUrl;
    protected $grantType;
    protected $translateUrl;

    /**
     * __construct
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param string $scopeUrl
     * @param string $tokenUrl
     * @param string $grantType
     * @param string $translateUrl
     */
    public function __construct(
        $clientId,
        $clientSecret,
        $scopeUrl,
        $tokenUrl,
        $grantType,
        $translateUrl
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->scopeUrl = $scopeUrl;
        $this->tokenUrl = $tokenUrl;
        $this->grantType = $grantType;
        $this->translateUrl = $translateUrl;
    }

    /**
     * @param $url
     * @param $requestXml
     * @return mixed
     * @throws \Exception
     */
    private function getTranslateResponse($url, $requestXml)
    {
        $curlHandler = curl_init();

        curl_setopt($curlHandler, CURLOPT_URL, $url);
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
     * [getAccessToken returns the access token used to generate the translations ]
     *
     * @return mixed
     */
    private function getAccessToken()
    {
        $clientId = $this->clientId;
        $clientSecret = $this->clientSecret;

        $curlHandler = curl_init();

        $request = 'grant_type=' . urlencode($this->grantType) . '&scope=' . urlencode($this->scopeUrl) .
            '&client_id=' . urlencode($clientId) . '&client_secret=' . urlencode($clientSecret);

        curl_setopt($curlHandler, CURLOPT_URL, $this->tokenUrl);
        curl_setopt($curlHandler, CURLOPT_POST, true);
        curl_setopt($curlHandler, CURLOPT_POSTFIELDS, $request);
        curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandler, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curlHandler);

        curl_close($curlHandler);

        $responseObject = json_decode($response);

        return $responseObject->access_token;
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

        if (!$this->clientId || !$this->clientSecret) {
            return $responseArray;
        }

        //Create the XML string for passing the values.
        $requestXml = "<TranslateArrayRequest>" .
            "<AppId/>" .
            "<From>$fromLanguage</From>" .
            "<Options>" .
            "<Category xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
            "<ContentType xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\">text/plain</ContentType>" .
            "<ReservedFlags xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
            "<State xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
            "<Uri xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
            "<User xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
            "</Options>";

        $requestXml .= "<Texts>";

        foreach ($text as $inputStr) {
            $requestXml .= "<string xmlns=\"http://schemas.microsoft.com/2003/10/Serialization/Arrays\">$inputStr</string>";
        }

        $requestXml .= "</Texts>";

        $requestXml .= "<To>$toLanguage</To>";
        $requestXml .= "</TranslateArrayRequest>";

        $response = $this->getTranslateResponse($this->translateUrl, $requestXml);

        $xmlObj = new \SimpleXMLElement($response);
        foreach ($xmlObj->TranslateArrayResponse as $translatedArrObj) {
            $responseArray[] = (string)$translatedArrObj->TranslatedText;
        }

        return $responseArray;
    }

}
