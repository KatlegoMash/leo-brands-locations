<?php

namespace Tests\Unit;

use App\Seller;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FunctionalityTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     *
     * @return void
     */
    /**
     * Test if the index method returns a view with sellers collection.
     *
     * @return void
     */
    public function test_index_method_returns_view_with_sellers_collection()
    {
        Seller::create([
            'seller_id' => 1,
            'name' => 'Updated Seller',
            'domain' => 'updated.example.com',
            'seller_type' => 'updated_type',
            'is_passthrough' => 1,
            'comment' => 'Updated seller comment',
            'is_confidential' => 0,
        ]);

        $response = $this->get(route('sellerjson-maintainer'));

        $response->assertViewIs('sellerjson-maintainer');
        $response->assertViewHas('collection', Seller::get());
    }
    /**
     * Test if the store method properly stores a new seller.
     *
     * @return void
     */
    public function test_store_method_properly_stores_new_seller()
    {
        $sellerData = [
            'seller_id' => 2,
            'name' => 'New Seller',
            'domain' => 'example.com',
            'seller_type' => 'type',
            'is_passthrough' => 1,
            'comment' => 'New seller comment',
            'is_confidential' => 0,
        ];

        $response = $this->post(route('store-seller'), $sellerData);

        $response->assertRedirect(route('sellerjson-maintainer'));
        $this->assertDatabaseHas('sellers', $sellerData);
    }
    /**
     * Test if the update method properly updates a seller.
     *
     * @return void
     */
    public function test_update_method_properly_updates_seller()
    {
        $seller = Seller::create([
            'seller_id' => 3,
            'name' => 'Old Seller',
            'domain' => 'old.example.com',
            'seller_type' => 'old_type',
            'is_passthrough' => 0,
            'comment' => 'Old seller comment',
            'is_confidential' => 1,
        ]);

        $newSellerData = [
            'seller_id' => 4,
            'name' => 'Updated Seller',
            'domain' => 'updated.example.com',
            'seller_type' => 'updated_type',
            'is_passthrough' => 1,
            'comment' => 'Updated seller comment',
            'is_confidential' => 0,
        ];

        $response = $this->patch(route('seller-update', ['id' => $seller->id]), $newSellerData);

        $response->assertRedirect(route('sellerjson-maintainer'));
        $this->assertDatabaseHas('sellers', $newSellerData);
    }
    /**
     * Test if the destroy method properly deletes a seller.
     *
     * @return void
     */
    public function test_destroy_method_properly_deletes_seller()
    {
        $seller = Seller::create([
            'seller_id' => 4,
            'name' => 'Updated Seller',
            'domain' => 'updated.example.com',
            'seller_type' => 'updated_type',
            'is_passthrough' => 1,
            'comment' => 'Updated seller comment',
            'is_confidential' => 0,
        ]);

        $response = $this->delete(route('seller-destroy', ['id' => $seller->id]));

        $response->assertRedirect(route('sellerjson-maintainer'));
        $this->assertDatabaseMissing('sellers', ['id' => $seller->id]);
    }
}
