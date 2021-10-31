# Think API Resource

API Resources Converter for ThinkPHP 5.

## Installation

```shell
$ composer require inna/think-api-resource:^1.0
```

## Usage
```php
<?php

use Inna\ApiResource\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'posts' => PostResource::collection($this->whenLoad('posts')),
        ];
    }
}
```

```php
<?php

use Inna\ApiResource\JsonResource;

class PostResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
        ];
    }
}

```

```php
<?php

class UserController 
{
    public function index()
    {
        $users = User::with('posts')->paginate();
      
        return UserResource::collection($users);
    }
    
    public function show()
    {
        $user = User::find(1);
      
        return UserResource::make($user)->wrap('user')->additional([
            'foo' => 'bar',
        ]);
    }
}

```
