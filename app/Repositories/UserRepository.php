<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function create($data)
    {
        return User::create($data);
    }

    public function findBy(string $field, string $value)
    {
        return User::where($field, $value)->first();
    }

    public function update(User $vendor, array $data)
    {
        return $vendor->update($data);
    }
}
