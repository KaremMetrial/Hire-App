<?php

    namespace App\Repositories\Interfaces;

    interface VendorRepositoryInterface
    {
        public function create($data);
        public function findBy(string $field, string $value);
    }
