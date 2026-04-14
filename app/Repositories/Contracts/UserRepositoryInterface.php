<?php

namespace App\Repositories\Contracts;

interface UserRepositoryInterface
{
    /**
     * Get all users.
     */
    public function all();

    /**
     * Find a user by their ID.
     *
     * @param int $id
     * @return mixed
     */
    public function find($id);

    /**
     * Create a new user.
     */
    public function create(array $data);

    /**
     * Update an existing user.
     *
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function update($id, array $data);

    /**
     * Delete a user.
     *
     * @param int $id
     * @return mixed
     */
    public function delete($id);

    /**
     * Find a user by their email.
     *
     * @param string $email
     * @return mixed
     */
    public function findByEmail($email);

    /**
     * Find users by their role.
     *
     * @param string $role
     * @return mixed
     */
    public function findBySellers();

    /**
     * Find users by their role.
     *
     * @param string $role
     * @return mixed
     */
    public function findByBuyers();

    /**
     * Find active users.
     *
     */
    public function findActive();
}
