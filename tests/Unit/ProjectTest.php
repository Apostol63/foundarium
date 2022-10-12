<?php

namespace Tests\Unit;

use Tests\TestCase;

class ProjectTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testFreeAutos()
    {
        $response = $this->get('/api/free_autos');
        $response->assertOk();
    }

    public function testAssignedAutos()
    {
        $response = $this->get('/api/assigned_autos');
        $response->assertOk();
    }

    public function testAllAutos()
    {
        $response = $this->get('/api/all_autos');
        $response->assertOk();
    }

    public function testAssignUserToAuto()
    {
        $response = $this->post('/api/assign_user_to_auto',
            [
                'user_id' => 4,
                'auto_id' => 4
            ],
        );
        $response->assertOk();
    }

    public function testDeleteAuto()
    {
        $response = $this->post('/api/delete_auto/5');
        $response->assertOk();
    }
}
