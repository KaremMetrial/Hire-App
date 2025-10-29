<?php

namespace App\Services\User;

use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function register($data)
    {
        return $this->userRepository->create($data);
    }

    public function login(array $credentials)
    {
        $identifier = $credentials['identifier'];
        $password = $credentials['password'];

        // Determine if the identifier is an email or a phone number
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        // Find the user by the identifier
        // Note: This assumes your UserRepository has a `findBy` method.
        $user = $this->userRepository->findBy($field, $identifier);

        // Check if user exists and if the provided password is correct
        if (! $user || ! Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }

    public function updateProfile($user, array $data)
    {
        DB::beginTransaction();
        try {
            $updatedUser = $this->userRepository->update($user, $data);
            DB::commit();
            return $updatedUser;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
