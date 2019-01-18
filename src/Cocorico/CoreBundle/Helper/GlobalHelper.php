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
     * @param bool    $firstCall
     */
    public function displayExtraFieldsFormErrorMessage(Request $request, Form $form, $firstCall = true)
    {
        if ($request->getMethod() == Request::METHOD_POST) {
            $datas = $request->request->all();
        } else {
            $datas = $request->query->all();
        }

        $message = "";

        if ($firstCall) {
            $message = "<strong>REQUEST DATAS:</strong><br/>";
            foreach ($datas as $field => $data) {
                $message .= "$field: <pre>";
                $message .= print_r($data, 1);
                $message .= "</pre>";
            }

            echo $message;
        }

        $children = $form->all();
        if (count($children)) {
            $message .= "<br/><strong>FORM CHILDREN OF " . $form->getName() . ":</strong><br/>";
            /** @var Form $child */
            foreach ($children as $child) {
                $message .= $child->getName() . "<br/>";
//                $this->displayExtraFieldsFormErrorMessage($request, $child, false);
            }
        }

        $extraFields = array_diff_key($datas, $children);
        if (count($extraFields)) {
            $message .= "<br/><strong>EXTRA FIELDS</strong><br/>";
            foreach ($extraFields as $field => $data) {
                $message .= "$field: <pre>";
                $message .= print_r($data, 1);
                $message .= "</pre>";
            }
        }

        echo $message;

    }


    /**
     *
     * @param Form              $form
     * @param FlashBagInterface $flashBag
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
     * todo: move to PHP class
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
//        curl_setopt($ch, CURLOPT_POST, count($postData));
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
     * todo: move to PHP class
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


}