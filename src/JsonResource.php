<?php

namespace Inna\ApiResource;

use JsonSerializable;
use think\facade\Request;
use think\Response;

class JsonResource implements JsonSerializable
{
    /**
     * @var mixed
     */
    protected $resource;

    /**
     * @var array
     */
    protected $additional = [];

    /**
     * @var mixed
     */
    public static $wrap;

    /**
     * @param  mixed $resource
     * @return void
     */
    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    /**
     * 创建资源
     *
     * @param  mixed $resource
     * @return static
     */
    public static function make($resource)
    {
        return new static($resource);
    }

    /**
     * 创建集合资源
     *
     * @param  mixed $resource
     * @return \Inna\ApiResource\AnonymousResourceCollection
     */
    public static function collection($resource)
    {
        return new AnonymousResourceCollection($resource, static::class);
    }

    /**
     * 转换资源为数组
     *
     * @param  \think\Request $request
     * @return array
     */
    public function toArray($request)
    {
        if (is_null($this->resource)) {
            return [];
        }

        return is_array($this->resource)
            ? $this->resource
            : $this->resource->toArray();
    }

    /**
     * @param  \think\Request $request
     * @return array
     */
    public function resolve($request)
    {
        return  $this->filter($this->toArray($request));
    }

    /**
     * @param  array $data
     * @return array
     */
    protected function filter($data)
    {
        $mergeData = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->filter($value);

                continue;
            }

            if ($value instanceof MissingValue ||
                ($value instanceof self &&
                $value->resource instanceof MissingValue)) {
                unset($data[$key]);
            }

            if ($value instanceof MergeValue) {
                unset($data[$key]);

                $mergeData = array_merge($mergeData, $this->filter($value->data));
            }
        }

        return array_merge($data, $mergeData);
    }

    /**
     * 设置资源附加数据
     *
     * @param  array $data
     * @return static
     */
    public function additional(array $data)
    {
        $this->additional = $data;

        return $this;
    }

    /**
     * 设置资源最外层包裹名称
     *
     * @param  string $warp
     * @return static
     */
    public function wrap($warp)
    {
        static::$wrap = $warp;

        return $this;
    }

    /**
     * 转换为 HTTP 响应
     *
     * @return \think\response\Json
     */
    public function toResponse()
    {
        $data = $this->resolve(Request::instance());

        return Response::create(
            array_merge(
                static::$wrap ? [static::$wrap => $data] : $data,
                $this->additional
            ),
            'json'
        );
    }

    /**
     * @param  string $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->resource->{$key});
    }

    /**
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->resource->{$key};
    }

    /**
     * JSON 序列化
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->resolve(Request::instance());
    }

    /**
     * @param  bool $condition
     * @param  mixed $value
     * @return mixed
     */
    protected function when($condition, $value)
    {
        return $condition ? (is_callable($value) ? $value() : $value) : new MissingValue;
    }

    /**
     * @param  bool $condition
     * @param  mixed $value
     * @return mixed
     */
    protected function mergeWhen($condition, $value)
    {
        return $condition ? new MergeValue(is_callable($value) ? $value() : $value) : new MissingValue;
    }

    /**
     * @param  string $relation
     * @return bool
     */
    protected function relationLoaded($relation)
    {
        return array_key_exists($relation, $this->resource->getRelation());
    }

    /**
     * @param  string $relation
     * @return mixed
     */
    protected function whenLoad($relation)
    {
        $relations = $this->resource->getRelation();

        return array_key_exists($relation, $relations) ? $relations[$relation] : new MissingValue;
    }
}
