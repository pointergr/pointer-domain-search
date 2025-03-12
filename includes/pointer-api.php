<?php
/**
 * Pointer API Class
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class pointer_api {

    var $login_username = '';
    var $login_password = '';
    var $key;

    function request($xml) {
        $url = "https://www.pointer.gr/api";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Content-Type:text/xml", "testserver: 0")); // testserver 0=Normal registry, 1=test registry
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        return curl_exec($curl);
    }

    function login($username = null, $password = null) {

		if( ! is_null($username)) {
            $this->login_username = $username;
        }

        if( ! is_null($password)) {
            $this->login_password = $password;
        }

        $chksum = md5($this->login_username . $this->login_password . 'login');
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"no\"?>
            <pointer>
                <login>
                    	<password>" . md5($this->login_password) . "</password>
                </login>
                <username>" . $this->login_username . "</username>
                <chksum>$chksum</chksum>
            </pointer>";

        $result = $this->request($xml);

        $xml = $this->_parseRequest($result);
        $xml_result = $xml->xpath('/pointer/login/key');

        if(count($xml_result) > 0) {
            $this->key = (string) $xml_result[0];
            return $this->key;
        } else {
            throw new Exception("Login failed. Please check your API credentials.");
        }
    }

    function logout() {
        $chksum = md5($this->login_username . $this->login_password . 'logout' . $this->key);
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <pointer>
                    <logout />
                    <username>" . $this->login_username . "</username>
                    <chksum>" . $chksum . "</chksum>
                </pointer>";

        $result = $this->request($xml);
        $xml = $this->_parseRequest($result);
        $xml_result = $xml->xpath('/pointer/logout/success');
        if(count($xml_result) > 0) {
            return (string) $xml_result[0] == 'true';
        }
        return false;
    }

    function domainCheck($domain, $tlds = NULL) {
        if (!is_array($tlds)) {
            $tlds = array();
        }

        $tld_xml = '';
        foreach ($tlds as $tld) {
            $tld_xml .= "<tld>" . $tld . "</tld>";
        }

        $chksum = md5($this->login_username . $this->login_password . 'domainCheck' . $this->key);
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
            <pointer>
                <domain-check>
                    <tlds>
                        " . $tld_xml . "
                    </tlds>
                    <domains>
                        <domain>" . $domain . "</domain>
                    </domains>
                </domain-check>
                <username>" . $this->login_username . "</username>
                <chksum>" . $chksum . "</chksum>
            </pointer>";

        $result = $this->request($xml);
        $xml = $this->_parseRequest($result);
        $xml_result = $xml->xpath('/pointer/login/key');
        $tmp = $xml->xpath('/pointer/domain-check/result/item');
        $arr = array();
        foreach($tmp as $tld_result) {
            $arr[(string) $tld_result->domain] = (string) $tld_result->available;
        }
        return $arr;
    }

    protected function _parseRequest($request_string) {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($request_string);

        if (!$xml) {
            $errors = libxml_get_errors();
            $error_msg = '';
            foreach ($errors as $error) {
                $error_msg .= $error->message . "\n";
            }
            libxml_clear_errors();
            throw new Exception("XML parsing error: " . $error_msg);
        }

        $error = $xml->xpath('/pointer/error');
        if (count($error) > 0) {
            $code = (string) $error[0]->code;
            $message = (string) $error[0]->message;
            throw new Exception("API error ($code): $message");
        }

        return $xml;
    }
}
