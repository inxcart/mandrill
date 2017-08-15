<?php

namespace MandrillModule;

use MandrillModule\Exception\Error;
use MandrillModule\Exception\HttpError;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * Class Mandrill
 *
 * @package MandrillModule
 */
class Mandrill
{
    public $templates;
    public $exports;
    public $users;
    public $rejects;
    public $inbound;
    public $tags;
    public $messages;
    public $whitelists;
    public $ips;
    public $internal;
    public $subaccounts;
    public $urls;
    public $webhooks;
    public $senders;
    public $metadata;

    public static $errorMap = array(
        'ValidationError'            => 'MandrillModule\\Exception\\ValidationError',
        'Invalid_Key'                => 'MandrillModule\\Exception\\Invalid_Key',
        'PaymentRequired'            => 'MandrillModule\\Exception\\PaymentRequired',
        'Unknown_Subaccount'         => 'MandrillModule\\Exception\\UnknownSubaccount',
        'Unknown_Template'           => 'MandrillModule\\Exception\\UnknownTemplate',
        'ServiceUnavailable'         => 'MandrillModule\\Exception\\ServiceUnavailable',
        'Unknown_Message'            => 'MandrillModule\\Exception\\UnknownMessage',
        'Invalid_Tag_Name'           => 'MandrillModule\\Exception\\InvalidTagName',
        'Invalid_Reject'             => 'MandrillModule\\Exception\\InvalidReject',
        'Unknown_Sender'             => 'MandrillModule\\Exception\\UnknownSender',
        'Unknown_Url'                => 'MandrillModule\\Exception\\UnknownUrl',
        'Unknown_TrackingDomain'     => 'MandrillModule\\Exception\\UnknownTrackingDomain',
        'Invalid_Template'           => 'MandrillModule\\Exception\\InvalidTemplate',
        'Unknown_Webhook'            => 'MandrillModule\\Exception\\UnknownWebhook',
        'Unknown_InboundDomain'      => 'MandrillModule\\Exception\\UnknownInboundDomain',
        'Unknown_InboundRoute'       => 'MandrillModule\\Exception\\UnknownInboundRoute',
        'Unknown_Export'             => 'MandrillModule\\Exception\\UnknownExport',
        'IP_ProvisionLimit'          => 'MandrillModule\\Exception\\IPProvisionLimit',
        'Unknown_Pool'               => 'MandrillModule\\Exception\\UnknownPool',
        'NoSendingHistory'           => 'MandrillModule\\Exception\\NoSendingHistory',
        'PoorReputation'             => 'MandrillModule\\Exception\\PoorReputation',
        'Unknown_IP'                 => 'MandrillModule\\Exception\\UnknownIP',
        'Invalid_EmptyDefaultPool'   => 'MandrillModule\\Exception\\InvalidEmptyDefaultPool',
        'Invalid_DeleteDefaultPool'  => 'MandrillModule\\Exception\\InvalidDeleteDefaultPool',
        'Invalid_DeleteNonEmptyPool' => 'MandrillModule\\Exception\\InvalidDeleteNonEmptyPool',
        'Invalid_CustomDNS'          => 'MandrillModule\\Exception\\InvalidCustomDNS',
        'Invalid_CustomDNSPending'   => 'MandrillModule\\Exception\\InvalidCustomDNSPending',
        'Metadata_FieldLimit'        => 'MandrillModule\\Exception\\MetadataFieldLimit',
        'Unknown_MetadataField'      => 'MandrillModule\\Exception\\UnknownMetadataField',
    );
    public $apikey;
    public $ch;
    public $root = 'https://mandrillapp.com/api/1.0';
    public $debug = false;

    /**
     * Mandrill constructor.
     *
     * @param null $apikey
     *
     * @throws Error
     */
    public function __construct($apikey = null)
    {
        if (!$apikey) {
            $apikey = getenv('MANDRILL_APIKEY');
        }
        if (!$apikey) {
            $apikey = $this->readConfigs();
        }
        if (!$apikey) {
            throw new Error('You must provide a Mandrill API key');
        }
        $this->apikey = $apikey;

        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_USERAGENT, 'Mandrill-PHP/1.0.55');
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->ch, CURLOPT_HEADER, false);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 600);

        $this->root = rtrim($this->root, '/').'/';

        $this->templates = new Templates($this);
        $this->exports = new Exports($this);
        $this->users = new Users($this);
        $this->rejects = new Rejects($this);
        $this->inbound = new Inbound($this);
        $this->tags = new Tags($this);
        $this->messages = new Messages($this);
        $this->whitelists = new Whitelists($this);
        $this->ips = new Ips($this);
        $this->internal = new Internal($this);
        $this->subaccounts = new Subaccounts($this);
        $this->urls = new Urls($this);
        $this->webhooks = new Webhooks($this);
        $this->senders = new Senders($this);
        $this->metadata = new Metadata($this);
    }

    /**
     * @return bool|string
     */
    public function readConfigs()
    {
        $paths = array('~/.mandrill.key', '/etc/mandrill.key');
        foreach ($paths as $path) {
            if (file_exists($path)) {
                $apikey = trim(file_get_contents($path));
                if ($apikey) {
                    return $apikey;
                }
            }
        }

        return false;
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        curl_close($this->ch);
    }

    /**
     * @param string $url
     * @param array  $params
     *
     * @return mixed
     * @throws Error
     * @throws HttpError
     */
    public function call($url, $params)
    {
        $params['key'] = $this->apikey;
        $params = json_encode($params);
        $ch = $this->ch;

        curl_setopt($ch, CURLOPT_URL, $this->root.$url.'.json');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_VERBOSE, $this->debug);

        $start = microtime(true);
        $this->log('Call to '.$this->root.$url.'.json: '.$params);
        if ($this->debug) {
            $curlBuffer = fopen('php://memory', 'w+');
            curl_setopt($ch, CURLOPT_STDERR, $curlBuffer);
        }

        $responseBody = curl_exec($ch);
        $info = curl_getinfo($ch);
        $time = microtime(true) - $start;
        if ($this->debug && isset($curlBuffer)) {
            rewind($curlBuffer);
            $this->log(stream_get_contents($curlBuffer));
            fclose($curlBuffer);
        }
        $this->log('Completed in '.number_format($time * 1000, 2).'ms');
        $this->log('Got response: '.$responseBody);

        if (curl_error($ch)) {
            throw new HttpError("API call to $url failed: ".curl_error($ch));
        }
        $result = json_decode($responseBody, true);
        if ($result === null) {
            throw new Error('We were unable to decode the JSON response from the Mandrill API: '.$responseBody);
        }

        if (floor($info['http_code'] / 100) >= 4) {
            throw $this->castError($result);
        }

        return $result;
    }

    /**
     * @param string $msg
     */
    public function log($msg)
    {
        if ($this->debug) {
            error_log($msg);
        }
    }

    /**
     * @param mixed $result
     *
     * @return mixed
     * @throws Error
     */
    public function castError($result)
    {
        if ($result['status'] !== 'error' || !$result['name']) {
            throw new Error('We received an unexpected error: '.json_encode($result));
        }

        $class = (isset(self::$errorMap[$result['name']])) ? self::$errorMap[$result['name']] : 'MandrillModule\\Exception\\Error';

        return new $class($result['message'], $result['code']);
    }
}
