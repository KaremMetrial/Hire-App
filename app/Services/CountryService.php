<?php

    namespace App\Services;

    use App\Models\Country;
    use App\Repositories\Interfaces\CountryRepositoryInterface;
    use Illuminate\Contracts\Pagination\LengthAwarePaginator;

    class CountryService
    {
        public function __construct(protected CountryRepositoryInterface $countryRepository)
        {
        }

        public function getAll(string $search = null): LengthAwarePaginator
        {
            return $this->countryRepository->all($search);
        }

        public function findById(int $id): ?Country
        {
            return $this->countryRepository->find($id);
        }

        public function getCities(Country $country): LengthAwarePaginator
        {
            // NOTE: This assumes your City model has an 'active' scope and is translatable.
            return $country->cities()->withTranslation()->paginate();
        }
    }
