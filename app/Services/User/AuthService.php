<?php

namespace App\Services\User;

use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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
        $user = $this->userRepository->findBy($field, $identifier);
        // Check if user exists
        if (!$user) {
            // Log failed attempt for non-existent user (security monitoring)
            Log::info('Login attempt with non-existent identifier', [
                'identifier' => $identifier,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
            return null;
        }
        // Check if account is locked
        if ($user->locked_until && $user->locked_until->isFuture()) {
            Log::warning('Login attempt on locked account', [
                'user_id' => $user->id,
                'identifier' => $identifier,
                'locked_until' => $user->locked_until,
                'ip' => request()->ip(),
            ]);
            return null; // Account is locked
        }

        // Check if the provided password is correct
        if (!Hash::check($password, $user->password)) {
            // Increment failed attempts and potentially lock account
            $this->handleFailedLogin($user);

            Log::warning('Failed login attempt - incorrect password', [
                'user_id' => $user->id,
                'identifier' => $identifier,
                'attempts' => $user->failed_login_attempts,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
            return null;
        }

        // Successful login - reset failed attempts and unlock if necessary
        $this->handleSuccessfulLogin($user);

        Log::info('Successful login', [
            'user_id' => $user->id,
            'identifier' => $identifier,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
        return $user;
    }

    /**
     * Handle failed login attempt - increment counter and lock if threshold reached
     */
    private function handleFailedLogin($user)
    {
        $user->increment('failed_login_attempts');

        // Lock account after 5 failed attempts for 15 minutes
        if ($user->failed_login_attempts >= 5) {
            $user->locked_until = now()->addMinutes(15);
            $user->save();

            Log::warning('Account locked due to multiple failed attempts', [
                'user_id' => $user->id,
                'attempts' => $user->failed_login_attempts,
                'locked_until' => $user->locked_until,
            ]);
        }
    }

    /**
     * Handle successful login - reset failed attempts and unlock account
     */
    private function handleSuccessfulLogin($user)
    {
        $user->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);
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

    public function resetPassword($user, string $newPassword)
    {
        $user->password = Hash::make($newPassword);
        $user->save();

        Log::info('Password reset successful', [
            'user_id' => $user->id,
            'ip' => request()->ip(),
        ]);

        return $user;
    }
}
