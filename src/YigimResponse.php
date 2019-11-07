<?php

namespace Chameleon\Yigim;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Macroable;
use Psr\Http\Message\ResponseInterface;

class YigimResponse implements Arrayable
{
    use Macroable;
    /**
     * Status Codes
     */
    public const STATUS = [
        'OK'            => 0,
        'SYSTEM_ERROR'  => 1,
        'AUTH_ERROR'    => 2,
        'ACCESS_DENIED' => 3,
        'INVALID_PARAM' => 4,
        'NOT_PERMITTED' => 5
    ];
    /**
     * Types
     */
    public const TYPE = [
        'VOID'       => 'void',
        'COLLECTION' => 'collection',
        'OBJECT'     => 'object',
        'ERROR'      => 'error'
    ];
    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @param array|ResponseInterface $response
     */
    public function __construct($response)
    {
        $this->attributes = $response instanceof ResponseInterface
            ? json_decode($response->getBody(), true)
            : (array)$response;
        $this->attributes = $this->attributes['response'] ?? $this->attributes;
    }

    /**
     * @return int
     */
    public function code()
    {
        return (int)$this->getAttribute('code', self::STATUS['AUTH_ERROR']);
    }

    /**
     * @return string
     */
    public function type()
    {
        return $this->getAttribute('type', self::TYPE['ERROR']);
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->code() === self::STATUS['OK'] && $this->type() !== self::TYPE['ERROR'];
    }

    /**
     * @return string|null
     */
    public function message()
    {
        return $this->getAttribute('message');
    }

    /**
     * @param null $param
     * @param null $default
     * @return mixed
     */
    public function payload($param = null, $default = null)
    {
        return array_get($this->getAttribute('payload'), $param, $default);
    }


    /**
     * @param      $name
     * @param null $default
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        return Arr::get($this->attributes, $name, $default);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }
}
