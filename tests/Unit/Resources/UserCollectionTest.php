<?php

use App\Http\Resources\UserCollection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

beforeEach(function () {
    $this->users = collect([
        new User(['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com']),
        new User(['id' => 2, 'name' => 'Jane Doe', 'email' => 'jane@example.com']),
        new User(['id' => 3, 'name' => 'Bob Smith', 'email' => 'bob@example.com']),
    ]);
    $this->collection = new UserCollection($this->users);
});

it('transforms collection to array with basic structure', function () {
    $request = new Request();
    $result = $this->collection->toArray($request);

    expect($result)->toHaveKey('data');
    expect($result)->toHaveKey('meta');
    expect($result['meta'])->toHaveKey('count');
    expect($result['meta']['count'])->toBe(3);
});

it('includes pagination metadata when available', function () {
    $paginator = new LengthAwarePaginator(
        $this->users,
        50,
        15,
        2
    );

    $collection = new UserCollection($paginator);
    $request = new Request();
    $result = $collection->toArray($request);

    expect($result['meta'])->toHaveKey('total');
    expect($result['meta'])->toHaveKey('per_page');
    expect($result['meta'])->toHaveKey('current_page');
});

it('includes pagination links when available', function () {
    $paginator = new LengthAwarePaginator(
        $this->users,
        50,
        15,
        2,
        [
            'path' => 'http://localhost/api/users',
        ]
    );

    $collection = new UserCollection($paginator);
    $request = new Request();
    $result = $collection->toArray($request);

    expect($result)->toHaveKey('links');
});

it('includes additional metadata in with method', function () {
    $request = new Request();
    $result = $this->collection->with($request);

    expect($result)->toHaveKey('meta');
    expect($result['meta'])->toHaveKey('version');
    expect($result['meta'])->toHaveKey('timestamp');
    expect($result['meta'])->toHaveKey('api_version');
    expect($result['meta']['version'])->toBe('1.0');
    expect($result['meta']['api_version'])->toBe('v1');
});

it('handles empty collection correctly', function () {
    $emptyCollection = new UserCollection(collect());
    $request = new Request();
    $result = $emptyCollection->toArray($request);

    expect($result['data'])->toHaveCount(0);
    expect($result['meta']['count'])->toBe(0);
});
