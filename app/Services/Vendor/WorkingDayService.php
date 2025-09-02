<?php


    namespace App\Services\Vendor;

    use App\Models\WorkingDay;
    use Illuminate\Support\Collection;
    use App\Repositories\Interfaces\WorkingDayRepositoryInterface;

    class WorkingDayService
    {
        protected WorkingDayRepositoryInterface $repository;

        public function __construct(WorkingDayRepositoryInterface $repository)
        {
            $this->repository = $repository;
        }

        public function getAll(): Collection
        {
            return $this->repository->all();
        }

        public function getById(int $id): ?WorkingDay
        {
            return $this->repository->find($id);
        }

        public function create(array $data): WorkingDay
        {
            return $this->repository->create($data);
        }

        public function update(int $id, array $data): ?WorkingDay
        {
            return $this->repository->update($id, $data);
        }

        public function delete(int $id): bool
        {
            return $this->repository->delete($id);
        }

        public function getByRentalShop(int $rentalShopId): Collection
        {
            return $this->repository->getByRentalShop($rentalShopId);
        }
    }
