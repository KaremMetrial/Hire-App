<?php

namespace App\Services;

use App\Models\Car;
use App\Repositories\Interfaces\CarRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CarService
{
    public function __construct(protected CarRepositoryInterface $carRepository)
    {
    }

    public function getAll(): LengthAwarePaginator
    {
        return $this->carRepository->all();
    }

    public function store(array $data): Car
    {
        return $this->carRepository->store($data);
    }

    public function update(array $data, Car $car): Car
    {
        return $this->carRepository->update($data, $car);
    }

    public function destroy(Car $car): void
    {
        $this->carRepository->destroy($car);
    }
}
