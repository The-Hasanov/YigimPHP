<?php

namespace Chameleon\Yigim;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Support\Facades\Event;

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
            'verify'   => false
        ]));
    }

    /**
     * @param $type
     * @param $action
     * @param $listener
     */
    public static function listen($type, $action, $listener)
    {
        Event::listen(implode('.', [self::class, $type, $action]), $listener);
    }

    /**
     * @param string $hookKey
     * @param array  $params
     * @return YigimHook
     */
    public function handleHook($hookKey, $params = [])
    {
        $yigimHook = new YigimHook($params);
        $yigimHook->setSecretKey($hookKey);
        if ($yigimHook->isSecretValid()) {
            Event::push(implode('.', [self::class, $yigimHook->type(), $yigimHook->action()]), [
                'yigim' => $this,
                'hook'  => $yigimHook
            ]);
        }
        return $yigimHook;
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
     * @param $refCompany
     * @return YigimResponse
     */
    public function setDefaultCompany($refCompany)
    {
        return $this->request('PUT', 'biller/companies/default', [
            'query' => [
                'reference' => $refCompany
            ]
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
        ]);
    }

    /**
     * @param string $refClient
     * @param array  $param
     * @return YigimResponse
     */
    public function updateClient($refClient, array $param)
    {
        return $this->request('PUT', 'biller/clients/' . $refClient, [
            'query' => $param
        ]);
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return YigimResponse
     */
    public function getClients($limit = 10, $offset = 0)
    {
        return $this->request('GET', 'biller/clients', [
            'query' => [
                'offset' => $offset,
                'limit'  => $limit
            ]
        ]);
    }

    /**
     * @param string $msisdn
     * @return YigimResponse
     */
    public function getClient($msisdn)
    {
        return $this->request('GET', 'biller/clients/' . $msisdn);
    }

    /**
     * @param $refClient
     * @return YigimResponse
     */
    public function deleteClient($refClient)
    {
        return $this->request('DELETE', 'biller/clients/' . $refClient);
    }

    /**
     * @param array $params
     * @return YigimResponse
     */
    public function createInvoice(array $params)
    {
        return $this->request('POST', 'biller/invoices', $this->preparePostBody($params));
    }

    /**
     * @param int   $numInvoice
     * @param array $params
     * @return YigimResponse
     */
    public function updateInvoice($numInvoice, array $params)
    {
        return $this->request('PUT', 'biller/invoices/' . $numInvoice, [
            'query' => $params
        ]);
    }

    /**
     * @param string $msisdn
     * @param int    $limit
     * @param int    $offset
     * @return YigimResponse
     */
    public function getInvoices($msisdn = null, $limit = 100, $offset = 0)
    {
        return $this->request('GET', 'biller/invoices', [
            'query' => array_filter([
                'offset' => $offset,
                'limit'  => $limit,
                'msisdn' => $msisdn
            ])
        ]);
    }

    /**
     * @param int $numInvoice
     * @return YigimResponse
     */
    public function getInvoice($numInvoice)
    {
        return $this->request('GET', 'biller/invoices/' . $numInvoice);
    }

    /**
     * @param int $numInvoice
     * @return YigimResponse
     */
    public function deleteInvoice($numInvoice)
    {
        return $this->request('DELETE', 'biller/invoices/' . $numInvoice);
    }

    /**
     * @param string|int $numInvoice
     * @param int        $limit
     * @param int        $offset
     * @return YigimResponse
     */
    public function getPayments($numInvoice = null, $limit = 100, $offset = 0)
    {
        return $this->request('GET', 'biller/payments', [
            'query' => array_filter([
                'invoice' => $numInvoice,
                'offset'  => $offset,
                'limit'   => $limit
            ])
        ]);
    }


    /**
     * @param string $refPayment
     * @return YigimResponse
     */
    public function getPayment($refPayment)
    {
        return $this->request('GET', 'biller/payments/' . $refPayment);
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
     * @param array $params
     * @return array
     */
    private function preparePostBody($params)
    {
        $tempOptions = '';
        if (isset($params['option']) && is_array($params['option'])) {
            foreach ($params['option'] as $index => $param) {
                $tempOptions .= '&' . http_build_query(['option' => $param]);
            }
            unset($params['option']);
        }
        return [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'body'    => http_build_query($params, '', '&') . $tempOptions
        ];
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
    private function timeSpan()
    {
        return hex2bin(str_pad(base_convert(time() * 5, 10, 16), 16, '0', STR_PAD_LEFT));
    }
}
