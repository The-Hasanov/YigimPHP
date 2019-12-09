<?php

namespace Chameleon\Yigim;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

class YigimHook implements Arrayable
{
    /**
     * Types
     */
    public const TYPE = [
        'COMPANY' => 1,
        'CLIENT'  => 2,
        'INVOICE' => 3
    ];
    /**
     * Company Actions
     */
    public const COMPANY_ACTION = [
        'CREATE'  => 'C',
        'UPDATE'  => 'U',
        'ENABLE'  => 'A', //Active
        'DISABLE' => 'I', //DeActive
        'DELETE'  => 'D'
    ];
    /**
     * Invoice Actions
     */
    public const CLIENT_ACTION = [
        'CREATE'  => 'C',
        'UPDATE'  => 'U',
        'ENABLE'  => 'A', //Active
        'DISABLE' => 'I', //DeActive
        'DELETE'  => 'D'
    ];
    /**
     * Invoice Actions
     */
    public const INVOICE_ACTION = [
        'CREATE'    => 'C',
        'VIEW'      => 'R',
        'UPDATE'    => 'U',
        'CANCEL'    => 'V',
        'PAID_FULL' => 'P',
        'PAID_PART' => 'R',
        'DELETE'    => 'D'
    ];
    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @param array $params
     */
    public function __construct($params = [])
    {
        $this->attributes = $params;
    }

    /**
     * @return string
     */
    public function type()
    {
        return $this->getAttribute('type');
    }

    /**
     * @return string
     */
    public function action()
    {
        return $this->getAttribute('action');
    }

    /**
     * @return string
     */
    public function reference()
    {
        return $this->getAttribute('reference');
    }

    /**
     * @return string
     */
    public function secret()
    {
        return $this->getAttribute('secret');
    }

    /**
     * @return bool
     */
    public function isSecretValid()
    {
        return true;
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
