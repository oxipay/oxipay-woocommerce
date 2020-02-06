<?php
/**
 * @param $template_name
 * @param $args
 * @author roger.bi@flexigroup.com.au
 * @copyright flexigroup
 */
function wc_humm_get_template($template_name,$args) {
    return wc_get_template ( $template_name, $args,'', $_FILES. '../templates/' );
}