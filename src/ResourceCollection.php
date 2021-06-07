<?php

namespace Inna\ApiResource;

use think\Collection;
use think\Paginator;

class ResourceCollection extends JsonResource
{
    /**
     * @var mixed
     */
    protected $resource;

    /**
     * @var string
     */
    protected $collects;

    /**
     * @var array
     */
    protected $collection;

    /**
     * @param  mixed $resource
     * @return void
     */
    public function __construct($resource)
    {
        parent::__construct($resource);

        $this->collectResource($resource);
    }

    /**
     * @param  mixed $resource
     * @return void
     */
    protected function collectResource($resource)
    {
        if ($resource instanceof MissingValue) {
            return;
        }

        if ($resource instanceof Collection) {
            $collection = $resource->all();
        } elseif ($resource instanceof Paginator) {
            $collection = $resource->getCollection()->all();
        } else {
            $collection = (array) $resource;
        }

        $this->collection = array_map(function ($resource) {
            $collects = $this->collects;

            return new $collects($resource);
        }, $collection);
    }

    /**
     * @param  \think\Request $request
     * @return array
     */
    public function resolve($request)
    {
        if ($this->resource instanceof Paginator) {
            return [
                'data' => parent::resolve($request),
                'total' => $this->resource->total(),
                'per_page' => $this->resource->listRows(),
                'current_page' => $this->resource->currentPage(),
                'last_page' => $this->resource->lastPage(),
            ];
        }

        return parent::resolve($request);
    }

    /**
     * @param  \think\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return array_map(function ($resource) use ($request) {
            return $resource->toArray($request);
        }, $this->collection);
    }
}
