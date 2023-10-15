<?php

namespace WPRefers\DomainAgeCheckerTool;

class DomainAgeCheckerTool
{

    public function __construct()
    {
        add_action('wp_enqueue_scripts', array( $this, 'enqueue' ));

        add_action( 'init', array( $this, 'add_shortcode' ) );
        add_action( 'wp_ajax_domain_age_checker_xhr_action', array( $this, 'domain_age_checker_xhr_action') );
        add_action( 'wp_ajax_nopriv_domain_age_checker_xhr_action', array( $this, 'domain_age_checker_xhr_action') );

        add_filter('widget_text', 'do_shortcode');

        $DashboardWidget = new DomainAgeDashboardWidget();
    }

    public function enqueue()
    {
        wp_enqueue_style('domain-age-checker-tool-style', plugins_url('css/domain-age-checker-tool.css', __DIR__));
        wp_enqueue_script('domain-age-checker-tool-script', plugins_url('js/domain-age-checker-tool.js', __DIR__), array('jquery'), '1.0', true);
        wp_localize_script('domain-age-checker-tool-script', "domain_age_checker_tool_data", array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce("_wpnonce")
        ));
    }

    public function domain_age_checker_xhr_action()
    {
        check_ajax_referer( '_wpnonce', 'security');

        $domains = sanitize_text_field($_POST['domains']);

        wp_send_json(
            $this->domain_age_checker_resolve_date(
                $domains
            )
        );
        wp_die();
    }

    public function domain_age_checker_resolve_date( $domains )
    {
        $domains = array_filter(array_unique(explode(' ', $domains)));

        $response = [];

        $count = 0;

        foreach (array_filter($domains) as $domain) {

            // only 5 domains
            if ($count < 5) {
                $response[] = (new WhoisApi())->getInfo(
                    str_replace('www.', '', $domain)
                );
            }

            $count++;
        }

        return array(
            'data'  => $response,
            'html'  => $this->domain_age_checker_resolve_html_data( $response ),
            'total' => count($response)
        );
    }

    public function domain_age_checker_resolve_html_data( $data )
    {
        $html = '';
        foreach ($data as $info) :

            if (count($info) > 1) {
                $html .=  '<tr><td>'.$info['domain_name'].'</td>' .
                    '<td>'.$info['age'].'</td>' .
                    '<td>'.$info['creation_date'].'</td>' .
                    '<td>'.$info['last_update_of_whois_database'].'</td>' .
                    '<td>'.$info['registry_expiry_date'].'</td></tr>';
            } else {
                $html .= '<tr><td>'.$info['domain_name'].'</td>' .
                    '<td colspan="4">Information could not be found. Try again Later.</td></tr>';
            }

        endforeach;

        return $html;
    }

    public function add_shortcode()
    {
        add_shortcode('domain-age-checker-tool', array( $this, 'shortcode' ));
    }

    public function shortcode( $atts )
    {
        // extract the attributes into variables
        extract(shortcode_atts(array(
            'title' => 'Domain Age Checker'
        ), $atts));

        $file_path = dirname(__FILE__) . '/templates/domain-age-checker-tool.php';

        ob_start();

        include($file_path);

        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

}