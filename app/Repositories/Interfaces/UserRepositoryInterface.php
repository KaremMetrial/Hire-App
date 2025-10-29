<?php

namespace App\Repositories\Interfaces;

use App\Models\User;

interface UserRepositoryInterface
{
    public function create($data);

    public function findBy(string $field, string $value);

    public function update(User $user, array $data);
}
