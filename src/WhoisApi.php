<?php

namespace WPRefers\DomainAgeCheckerTool;

use Phois\Whois\Whois;

class WhoisApi
{

    public function getInfo( $domain )
    {
        $response = array();

        $arrKeys = array(
            'domain_name',
            'creation_date',
            'last_update_of_whois_database',
            'registry_expiry_date',
            'registrar'
        );
        
        $domain = $this->parseDomain($domain);

        if (!$this->isValidDomain($domain)) {
            return $response;
        }

        $data = $this->crawl($domain);

        foreach ($data as $key => $value) {
            if (in_array($key, $arrKeys)) {

                $value = trim(strip_tags(html_entity_decode($value)));
                switch ($key) {
                    case 'registrar':
                    case 'domain_name':
                        $formattedVal = strtolower($value);
                        break;
                    default:
                        $formattedVal = date_format(date_create($value),"Y/m/d H:i:s");
                        break;
                }
                $response[$key] = $formattedVal;

                if ($key === 'creation_date') {
                    $response['age'] = $this->getAge($value);
                }
            }
        }

        return $response;
    }

    public function crawl($domain)
    {
        $response =  explode(PHP_EOL, (new Whois($domain))->info());

        $result = array('info'=>"");
        foreach($response as $row) {
            $posOfFirstColon = strpos($row, ":");
            if($posOfFirstColon === FALSE) {
                $result['info'] = $row;
                $result['domain_name'] = $domain;
            } else {
                // Replace whitespaces with underscore from key
                $key = str_replace(' ', '_',  strtolower(trim(substr($row, 0, $posOfFirstColon))));
                $key = strip_tags(html_entity_decode($key));
                // remove >>>_
                $key = str_replace('>>>_', '',  $key);
                $result[$key] = trim(substr($row, $posOfFirstColon+1));
            }
        }

        return $result;
    }

    public function getAge($date)
    {
        $time = time() - strtotime($date);

        $years = floor($time / 31556926);

        $days = floor(($time % 31556926) / 86400);

        if($years == "1") {
            $y= "1 year";
        }
        else
        {
            $y = $years . " years";
        }
        if($days == "1") {
            $d = "1 day";
        }
        else
        {
            $d = $days . " days";
        }
        return "$y, $d";
    }

    private function parseDomain($domain)
    {
        $domain = rtrim($domain, '/');

        $parsed_url = parse_url($domain);

        if (isset($parsed_url['host'])) {
            $main_domain = $parsed_url['host'];
        } else {
            // If the host is not set, try extracting the domain from the path
            $path_parts = explode('/', $parsed_url['path']);
            $main_domain = isset($path_parts[0]) ? $path_parts[0] : null;
        }
        return $main_domain;
    }

    private function isValidDomain($domain)
    {
        // Remove leading and trailing spaces
        $domain = trim($domain);

        // Define a regular expression pattern for a valid domain
        $pattern = '/^(?:(?![_.-])[a-zA-Z0-9_.-]{1,63}(?<![_.-])\.)+[a-zA-Z.]{2,10}$/';

        // Use preg_match to check if the string matches the pattern
        return preg_match($pattern, $domain);
    }

}