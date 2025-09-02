<?php

    namespace App\Repositories\Interfaces;

    use App\Models\Country;
    use Illuminate\Contracts\Pagination\LengthAwarePaginator;

    interface CountryRepositoryInterface
    {
        public function all(string $search = null): LengthAwarePaginator;

        public function find(int $id): ?Country;
    }
