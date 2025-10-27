<?php

namespace App\Repositories\Interfaces;

use App\Models\Car;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CarRepositoryInterface
{
    public function all(): LengthAwarePaginator;
    public function store(array $data): Car;
    public function update(array $data, Car $car): Car;
    public function destroy(Car $car): void;
    public function findById(int $id): ?Car;
    public function getByRentalShop(int $rentalShopId, array $filters = []): LengthAwarePaginator;
}
