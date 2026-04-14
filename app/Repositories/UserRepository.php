<?php

namespace App\Repositories;

use App\Models\Vehicle;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    protected $model;

    public function __construct(Vehicle $vehicle)
    {
        $this->model = $vehicle;
    }

    /**
     * Get all users.
     */
    public function all()
    {
        return $this->model->latest()->get();
    }

    /**
     * Find a user by their ID.
     *
     * @param int $id
     * @return mixed
     */
    public function find($id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create a new user.
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing user.
     *
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function update($id, array $data)
    {
        $user = $this->model->findOrFail($id);
        $user->update($data);
        return $user->fresh();
    }

    /**
     * Delete a user.
     *
     * @param int $id
     * @return mixed
     */
    public function delete($id)
    {
        $user = $this->model->findOrFail($id);
        return $user->delete();
    }

    /**
     * Find a user by their email.
     *
     * @param string $email
     * @return mixed
     */
    public function findByEmail($email)
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Find users by their role.
     *
     * @param string $role
     * @return mixed
     */
    public function findBySellers()
    {
        return $this->model->seller()->active()->get();
    }

    /**
     * Find users by their role.
     *
     * @param string $role
     * @return mixed
     */
    public function findByBuyers()
    {
        return $this->model->buyer()->active()->get();
    }

    /**
     * Find active users.
     *
     */
    public function findActive()
    {
        return $this->model->active()->get();
    }
}
