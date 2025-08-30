<?php

    namespace App\Services\Vendor;

    use App\Repositories\Interfaces\VendorRepositoryInterface;
    use Illuminate\Support\Facades\Hash;


    class AuthService
    {
        protected VendorRepositoryInterface $vendorRepository;

        public function __construct(VendorRepositoryInterface $vendorRepository)
        {
            $this->vendorRepository = $vendorRepository;
        }

        public function register($data)
        {
            return $this->vendorRepository->create($data);
        }

        public function login(array $credentials)
        {
            $identifier = $credentials['identifier'];
            $password = $credentials['password'];

            // Determine if the identifier is an email or a phone number
            $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

            // Find the vendor by the identifier
            // Note: This assumes your VendorRepository has a `findBy` method.
            $vendor = $this->vendorRepository->findBy($field, $identifier);

            // Check if vendor exists and if the provided password is correct
            if (!$vendor || !Hash::check($password, $vendor->password)) {
                return null;
            }

            return $vendor;
        }
    }
