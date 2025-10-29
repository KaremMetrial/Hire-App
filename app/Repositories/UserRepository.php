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

    public function update(User $user, array $data)
    {
        $user->update($data);
        return $user;
    }
}
