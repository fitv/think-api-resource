<?php

namespace Inna\ApiResource;

class MergeValue
{
    /**
     * @var array
     */
    public $data;

    /**
     * @param  array $data
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
