<?php
/**
 * @param $template_name
 * @param array $args
 */
function wc_humm_get_template($template_name,$args) {
    return wc_get_template ( $template_name, $args,'', $_FILES. '../templates/' );
}