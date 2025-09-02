<?php


    namespace App\Repositories;

    use App\Models\WorkingDay;
    use Illuminate\Support\Collection;
    use App\Repositories\Interfaces\WorkingDayRepositoryInterface;

    class WorkingDayRepository implements WorkingDayRepositoryInterface
    {
        public function all(): Collection
        {
            return WorkingDay::all();
        }

        public function find(int $id): ?WorkingDay
        {
            return WorkingDay::find($id);
        }

        public function create(array $data): WorkingDay
        {
            return WorkingDay::create($data);
        }

        public function update(int $id, array $data): ?WorkingDay
        {
            $workingDay = $this->find($id);
            if ($workingDay) {
                $workingDay->update($data);
            }

            return $workingDay;
        }

        public function delete(int $id): bool
        {
            $workingDay = $this->find($id);
            return $workingDay ? (bool)$workingDay->delete() : false;
        }

        public function getByRentalShop(int $rentalShopId): Collection
        {
            return WorkingDay::where('rental_shop_id', $rentalShopId)->get();
        }
    }
