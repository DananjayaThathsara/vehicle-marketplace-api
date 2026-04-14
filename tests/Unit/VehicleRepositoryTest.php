<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Vehicle;
use App\Models\User;
use App\Repositories\VehicleRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VehicleRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private VehicleRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new VehicleRepository();
    }

    /** @test */
    public function it_can_get_all_vehicles()
    {
        // Arrange
        Vehicle::factory()->count(5)->create();

        // Act
        $vehicles = $this->repository->all();

        // Assert
        $this->assertCount(5, $vehicles);
    }

    /** @test */
    public function it_can_find_vehicle_by_id()
    {
        // Arrange
        $vehicle = Vehicle::factory()->create(['make' => 'Toyota']);

        // Act
        $found = $this->repository->find($vehicle->id);

        // Assert
        $this->assertEquals('Toyota', $found->make);
        $this->assertEquals($vehicle->id, $found->id);
    }

    /** @test */
    public function it_can_create_vehicle()
    {
        // Arrange
        $seller = User::factory()->seller()->create();
        $data = [
            'vin' => 'TEST1234567890ABC',
            'make' => 'Toyota',
            'model' => 'Camry',
            'year' => 2020,
            'color' => 'Blue',
            'mileage' => 30000,
            'price' => 25000,
            'description' => 'Test vehicle',
            'seller_id' => $seller->id,
            'status' => 'draft'
        ];

        // Act
        $vehicle = $this->repository->create($data);

        // Assert
        $this->assertDatabaseHas('vehicles', [
            'vin' => 'TEST1234567890ABC',
            'make' => 'Toyota'
        ]);
        $this->assertEquals('Toyota', $vehicle->make);
    }

    /** @test */
    public function it_can_update_vehicle()
    {
        // Arrange
        $vehicle = Vehicle::factory()->create(['price' => 25000]);

        // Act
        $updated = $this->repository->update($vehicle->id, ['price' => 30000]);

        // Assert
        $this->assertEquals(30000, $updated->price);
        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'price' => 30000
        ]);
    }

    /** @test */
    public function it_can_delete_vehicle()
    {
        // Arrange
        $vehicle = Vehicle::factory()->create();

        // Act
        $this->repository->delete($vehicle->id);

        // Assert
        $this->assertSoftDeleted('vehicles', ['id' => $vehicle->id]);
    }

    /** @test */
    public function it_can_find_listed_vehicles()
    {
        // Arrange
        Vehicle::factory()->count(3)->create(['status' => 'listed']);
        Vehicle::factory()->count(2)->create(['status' => 'draft']);

        // Act
        $listed = $this->repository->findListed();

        // Assert
        $this->assertEquals(3, $listed->total());
    }

    /** @test */
    public function it_can_find_vehicles_by_make()
    {
        // Arrange
        Vehicle::factory()->count(2)->create(['make' => 'Toyota', 'status' => 'listed']);
        Vehicle::factory()->count(3)->create(['make' => 'Honda', 'status' => 'listed']);

        // Act
        $toyotas = $this->repository->findByMake('Toyota');

        // Assert
        $this->assertEquals(2, $toyotas->total());
    }

    /** @test */
    public function it_can_find_featured_vehicles()
    {
        // Arrange
        Vehicle::factory()->count(5)->create([
            'is_featured' => true,
            'status' => 'listed'
        ]);
        Vehicle::factory()->count(3)->create([
            'is_featured' => false,
            'status' => 'listed'
        ]);

        // Act
        $featured = $this->repository->findFeatured(10);

        // Assert
        $this->assertCount(5, $featured);
    }

    /** @test */
    public function it_can_find_vehicles_with_filters()
    {
        // Arrange
        Vehicle::factory()->create([
            'make' => 'Toyota',
            'price' => 25000,
            'year' => 2020,
            'status' => 'listed'
        ]);
        Vehicle::factory()->create([
            'make' => 'Honda',
            'price' => 35000,
            'year' => 2021,
            'status' => 'listed'
        ]);

        // Act
        $results = $this->repository->findWithFilters([
            'make' => 'Toyota',
            'price_max' => 30000
        ]);

        // Assert
        $this->assertEquals(1, $results->total());
        $this->assertEquals('Toyota', $results->first()->make);
    }
}
