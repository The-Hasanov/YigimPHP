<?php

namespace Chameleon\Yigim;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class Yigim
{
    /**
     * @var string
     */
    private $alias;
    /**
     * @var string
     */
    private $key;
    /**
     * @var ClientInterface
     */
    private $client;
    /**
     * Api
     */
    public const API_URI = 'https://api.yigim.az/';

    /**
     * Yigim constructor.
     * @param                 $alias
     * @param                 $key
     * @param ClientInterface $client
     */
    public function __construct($alias, $key, ClientInterface $client)
    {
        $this->alias = $alias;
        $this->key = $key;
        $this->client = $client;
    }

    /**
     * @param $config
     * @return Yigim
     */
    public static function create($config): Yigim
    {
        return new self($config['alias'], $config['key'], new Client([
            'base_uri' => static::API_URI,
            'timeout'  => 30,
        ]));
    }

    /**
     * @return YigimResponse
     */
    public function getCompanies()
    {
        return $this->request('GET', 'biller/companies/');
    }

    /**
     * @return YigimResponse
     */
    public function getDefaultCompany()
    {
        return $this->request('GET', 'biller/companies/default');
    }

    /**
     * @param $reference
     * @return YigimResponse
     */
    public function setDefaultCompany($reference)
    {
        return $this->request('PUT', 'biller/companies/default', [
            'query' => [
                'reference' => $reference
            ]
        ]);
    }

    /**
     * @param array $param
     * @return YigimResponse
     */
    public function createCompany(array $param)
    {
        return $this->request('POST', 'biller/companies', [
            'form_params' => $param
        ]);
    }


    /**
     * @param array $param
     * @return YigimResponse
     */
    public function createClient(array $param)
    {
        return $this->request('POST', 'biller/clients', [
            'form_params' => $param
        ], ['Content-Type' => 'application/x-www-form-urlencoded']);
    }

    /**
     * @param string $ref
     * @param array  $param
     * @return YigimResponse
     */
    public function updateClient($ref, array $param)
    {
        return $this->request('PUT', 'biller/clients/' . $ref, [
            'query' => $param
        ]);
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return YigimResponse
     */
    public function getClients($limit = 100, $offset = 0)
    {
        return $this->request('GET', 'biller/clients', [
            'query' => [
                'offset' => $offset,
                'limit'  => $limit
            ]
        ]);
    }

    /**
     * @param string $ref
     * @return YigimResponse
     */
    public function getClient($ref)
    {
        return $this->request('GET', 'biller/clients/' . $ref);
    }

    /**
     * @param $ref
     * @return YigimResponse
     */
    public function deleteClient($ref)
    {
        return $this->request('DELETE', 'biller/clients/' . $ref);
    }

    /**
     * @param array $params
     * @return YigimResponse
     */
    public function createInvoice(array $params)
    {
        return $this->request('POST', 'biller/invoices', [], [
            'form_params' => $params
        ]);
    }

    /**
     * @param       $number
     * @param array $params
     * @return YigimResponse
     */
    public function updateInvoice($number, array $params)
    {
        return $this->request('PUT', 'biller/invoices/' . $number, [
            'query' => $params
        ]);
    }

    /**
     * @param int  $limit
     * @param int  $offset
     * @param null $client
     * @return YigimResponse
     */
    public function getInvoices($limit = 100, $offset = 0, $client = null)
    {
        return $this->request('GET', 'biller/invoices', [
            'query' => array_filter([
                'offset' => $offset,
                'limit'  => $limit,
                'msisdn' => $client
            ])
        ]);
    }

    /**
     * @param string $ref
     * @return YigimResponse
     */
    public function getInvoice($ref)
    {
        return $this->request('GET', 'biller/invoices/' . $ref);
    }

    /**
     * @param $ref
     * @return YigimResponse
     */
    public function deleteInvoice($ref)
    {
        return $this->request('DELETE', 'biller/invoices/' . $ref);
    }

    /**
     * @param int  $limit
     * @param int  $offset
     * @param null $payment
     * @return YigimResponse
     */
    public function getPayments($limit = 100, $offset = 0, $payment = null)
    {
        return $this->request('GET', 'biller/payments', [
            'query' => array_filter([
                'offset'  => $offset,
                'limit'   => $limit,
                'invoice' => $payment
            ])
        ]);
    }


    /**
     * @param string $ref
     * @return YigimResponse
     */
    public function getPayment($ref)
    {
        return $this->request('GET', 'biller/payments/' . $ref);
    }


    /**
     * @param       $method
     * @param       $uri
     * @param array $options
     * @param array $headers
     * @return YigimResponse
     * @throws
     */
    protected function request($method, $uri, array $options = [], array $headers = []): YigimResponse
    {
        return new YigimResponse(
            $this->client->request($method, ltrim($uri, '/'), $this->prepareOptions($options, $headers))
        );
    }

    /**
     * @param array $options
     * @param array $headers
     * @return array
     */
    protected function prepareOptions(array $options = [], array $headers = []): array
    {
        $options = array_merge([
            'strict'          => false,
            'referer'         => false,
            'track_redirects' => false,
            'http_errors'     => false,
            'protocols'       => ['http', 'https']
        ], $options);

        $options['headers'] = array_merge([
            'User-Agent' => 'YigimPHP',
            'X-Alias'    => $this->alias,
            'X-Token'    => $this->secret(),
            'X-Type'     => 'JSON'
        ], $options['headers'] ?? [], $headers);

        return $options;
    }


    /**
     * @return string
     */
    protected function secret(): string
    {
        return base64_encode(
            md5(hash_hmac('sha1', $this->alias . $this->timeSpan(), $this->key, true), true)
        );
    }

    /**
     * @return bool|string
     */
    public function timeSpan()
    {
        return hex2bin(str_pad(base_convert(time() * 5, 10, 16), 16, '0', STR_PAD_LEFT));
    }
}
