<?php

namespace App\Repositories;

use App\Models\Car;
use App\Repositories\Interfaces\CarRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CarRepository implements CarRepositoryInterface
{
    public function all(): LengthAwarePaginator
    {
        return Car::with('model', 'fuel', 'transmission', 'category', 'rentalShop', 'city')
            ->latest()
            ->paginate();
    }

    public function store(array $data): Car
    {
        return Car::create($data);
    }

    public function update(array $data, Car $car): Car
    {
        $car->update($data);
        return $car;
    }

    public function destroy(Car $car): void
    {
        $car->delete();
    }
}
