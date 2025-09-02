<?php

    namespace App\Repositories;

    use App\Models\Country;
    use App\Repositories\Interfaces\CountryRepositoryInterface;
    use Illuminate\Contracts\Pagination\LengthAwarePaginator;

    class CountryRepository implements CountryRepositoryInterface
    {
        public function all(string $search = null): LengthAwarePaginator
        {
            return Country::active()
                ->when($search, fn($query, $search) => $query->searchName($search))
                ->withTranslation() // Eager load translations
                ->latest()
                ->paginate();
        }

        public function find(int $id): ?Country
        {
            return Country::withTranslation()->find($id);
        }
    }
