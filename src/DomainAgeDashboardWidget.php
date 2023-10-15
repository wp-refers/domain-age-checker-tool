<?php

namespace WPRefers\DomainAgeCheckerTool;

class DomainAgeDashboardWidget
{

    public function __construct() {
        add_action('wp_dashboard_setup', [$this, 'add_widget']);
    }

    public function add_widget() {
        wp_add_dashboard_widget('custom_dashboard_widget_oop', 'Domain Age Checker', [$this, 'widget_content']);
    }

    private function domain_age()
    {
        $cache_key = 'domain_age_checker_tool_response';
        $cache_time = 24 * 60 * 60;

        $domainAge = get_transient($cache_key);

        if (false === $domainAge) {
            $domainAge = (new WhoisApi())->getInfo(home_url());
            set_transient($cache_key, $domainAge, $cache_time);
        }

        return $domainAge;
    }

    private function domain_age_info($expiryDate)
    {
        $expiry_date_str = $expiryDate;

        $expiry_timestamp = strtotime($expiry_date_str);

        $current_timestamp = time();

        $time_difference = $expiry_timestamp - $current_timestamp;

        $info = [];

        if ($time_difference < 0) {
            // Expiry date has already passed
            $days_passed = floor(abs($time_difference) / (24 * 60 * 60));
            $info['message'] = "The expiry date has already passed by $days_passed days.";
            $info['status'] = false;
        } else if ($time_difference < (7 * 24 * 60 * 60)) {
            // Expiry date is coming soon (within 7 days)
            $days_remaining = ceil($time_difference / (24 * 60 * 60));
            $info['message'] = "The expiry date is coming soon, and there are $days_remaining days remaining.";
            $info['status'] = false;
        } else {
            // Expiry date is not coming soon
            $days_remaining = ceil($time_difference / (24 * 60 * 60));
            $info['message'] = "The expiry date is not coming soon, and there are $days_remaining days remaining.";
            $info['status'] = true;
        }

        return $info;
    }

    public function widget_content() {
        $domainAge = $this->domain_age();

        if (array_key_exists('registry_expiry_date', $domainAge)) {
            $expiryInfo = $this->domain_age_info($domainAge['registry_expiry_date']);
        } else {
            $expiryInfo['status'] = false;
            $expiryInfo['message'] = 'Information could not be found for the domain. Try again Later.';
        }

        if ($expiryInfo['status']) {
            $svgColor = 'green';
            $textColor = 'green';
            $statusText = 'OK';
        } else {
            $svgColor = 'red';
            $textColor = 'red';
            $statusText = 'BAD';
        }

        echo '<div style="text-align: center; display: flex; flex-direction: column; align-items: center;">
        <svg width="100" height="50" xmlns="http://www.w3.org/2000/svg" style="align-self: center;">
            <circle cx="25" cy="25" r="20" fill="transparent" stroke="' . $svgColor . '" stroke-width="2" />
            <text x="25" y="27" font-size="12" text-anchor="middle" fill="' . $textColor . '">' . $statusText . '</text>
        </svg>';

        echo $expiryInfo['message'];

        if (!empty($domainAge['age'])) {
            echo '<ul>
                <li>Age : <b>' . $domainAge['age'] . '</b></li>
                <li>Created Date : <b>' . $domainAge['creation_date'] . '</b></li>
                <li>Last Updated Date : <b>' . $domainAge['last_update_of_whois_database'] . '</b></li>
                <li>Expiry Date : <b>' . $domainAge['registry_expiry_date'] . '</b></li>
                <li>Registrar : <b>' . $domainAge['registrar'] . '</b></li>
            </ul>';
        }

        echo '</div>';
    }

}