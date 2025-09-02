<?php


    namespace App\Repositories\Interfaces;

    use App\Models\WorkingDay;
    use Illuminate\Support\Collection;

    interface WorkingDayRepositoryInterface
    {
        public function all(): Collection;

        public function find(int $id): ?WorkingDay;

        public function create(array $data): WorkingDay;

        public function update(int $id, array $data): ?WorkingDay;

        public function delete(int $id): bool;

        public function getByRentalShop(int $rentalShopId): Collection;
    }
