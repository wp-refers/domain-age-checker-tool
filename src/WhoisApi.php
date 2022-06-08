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
            'registry_expiry_date'
        );

        $data = $this->crawl($domain);

        foreach ($data as $key => $value) {
            if (in_array($key, $arrKeys)) {

                $value = trim(strip_tags(html_entity_decode($value)));
                switch ($key) {
                    case 'domain_name':
                        $formattedVal = strtolower($value);
                        break;
                    default:
                        $formattedVal = date_format(date_create($value),"Y/m/d H:i:s");
                        break;
                }
                $response[$key] = $formattedVal;

                if ($key === 'creation_date') {
                    $response['age'] = $this->getDate($value);
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

    public function getDate($date)
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

}