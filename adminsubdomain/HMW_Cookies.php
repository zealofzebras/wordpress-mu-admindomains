<?php

defined('ABSPATH') || die('Cheatin\' uh?');


class HMW_Models_Cookies_NONDOMAIN extends HMW_Models_Cookies {
    public function getCookieDomain() {
        $domain = COOKIE_DOMAIN;

        header("X-HIDE-CUSTOM: yes");
        

        return $domain;
    }
}
