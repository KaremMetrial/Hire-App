<?php

    namespace App\Services\Vendor;

    use App\Repositories\Interfaces\VendorRepositoryInterface;

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
    }
