<?php

namespace Inna\ApiResource;

class AnonymousResourceCollection extends ResourceCollection
{
    /**
     * @param  mixed $resource
     * @param  string $collects
     * @return void
     */
    public function __construct($resource, $collects)
    {
        $this->collects = $collects;

        parent::__construct($resource);
    }
}
