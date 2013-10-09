<?php
namespace ARP\SolrClient2;

class CurlBrowser {
    private $timeout = 13;
    private $header = array();
    private $proxy_host = null;
    private $proxy_port = null;
    private $proxy_exclude = null;
    private $curl_init = null;

    public function __construct() {
        $this->header[] = "Connection: close";
        $this->curl_init = curl_init();
    }

    public function httpGet($url) {
        return $this->doRequest('GET', $url, array(), null);
    }

    public function httpPost($url, array $header, $data) {
        return $this->doRequest('POST', $url, $header, $data);
    }

    public function timeout($timeout) {
        $this->timeout = (int)$timeout;
    }

    public function proxy($host, $port) {
        if(trim($host) === "" || trim($port) === '')
            return false;

        $this->proxy_host = trim($host);
        $this->proxy_port = trim($port);
        return true;
    }

    public function excludeHost($host) {
        if(trim($host) === "")
            return false;

        $this->proxy_exclude[] = trim($host);
        return true;
    }

    private function doRequest($method, $url, array $header, $data) {
        $parsed_url = parse_url($url);
        $this->header = array_merge($this->header, $header);

        if(isset($parsed_url['scheme']) 
            && $parsed_url['scheme'] !== 'http' 
            && $parsed_url['scheme'] !== 'https') {
            return false;
        }
        
        if($method === 'GET') {
            curl_setopt($this->curl_init, CURLOPT_POST, 0);
        } else if($method === 'POST') {
            curl_setopt($this->curl_init, CURLOPT_POST, 1);
            curl_setopt($this->curl_init, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($this->curl_init, CURLOPT_URL, $url);
        curl_setopt($this->curl_init, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->curl_init, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl_init, CURLINFO_HEADER_OUT, true);
        curl_setopt($this->curl_init, CURLOPT_TIMEOUT, $this->timeout);

        if(!empty($this->header))
            curl_setopt($this->curl_init, CURLOPT_HTTPHEADER, $this->header);
        
        if(!empty($this->proxy_host) && !in_array($parsed_url["host"], $this->proxy_exclude)) {
            curl_setopt($this->curl_init, CURLOPT_PROXY, $this->proxy_host);
            curl_setopt($this->curl_init, CURLOPT_PROXYPORT, $this->proxy_port);
        }
        
        $response = new \stdClass();
        $response->content = curl_exec($this->curl_init);
        $response->header = curl_getInfo($this->curl_init, CURLINFO_HEADER_OUT);
        $response->status = (int)curl_getInfo($this->curl_init, CURLINFO_HTTP_CODE);  
        $response->contentType = curl_getInfo($this->curl_init, CURLINFO_CONTENT_TYPE);  
        $response->error = curl_error($this->curl_init);

        return $response;
    }
}