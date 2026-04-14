<?php

namespace App\Repositories\Contracts;

interface VehicleRepositoryInterface
{
    /**
     * Get all vehicles.
     */
    public function all();

    /**
     * Find a vehicle by its ID.
     *
     * @param int $id
     * @return mixed
     */
    public function find($id);

    /**
     * Find vehicles by their make.
     *
     * @param string $make
     * @return mixed
     */
    public function findByMake($make);

    /**
     * Create a new vehicle.
     */
    public function create(array $data);


    /**
     * Update an existing vehicle.
     *
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function update($id, array $data);

    /**
     * Delete a vehicle.
     *
     * @param int $id
     * @return mixed
     */
    public function delete($id);

    /**
     * Find listed vehicles.
     *
     */
    public function findListed();

    /**
     * Find featured vehicles.
     *
     */
    public function findFeatured();

    /**
     * Find with filters
     */
    public function findWithFilters(array $filters);

    /**
     * Get seller's vehicles
     */
    public function findBySeller($sellerId);

    /**
     * Search vehicles
     */
    public function search($query, array $filters = []);
}
