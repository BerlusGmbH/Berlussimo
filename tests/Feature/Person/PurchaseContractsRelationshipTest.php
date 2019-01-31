<?php

namespace Tests\Feature;

use App\Models\Kaufvertraege;
use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;

class PurchaseContractsRelationshipTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Read test with all base fields set.
     *
     * @return void
     */
    public function testPurchaseContractRelationship()
    {
        $this->shiftIDawayFromDAT();

        $person = factory(Person::class)->states('with.purchaseContract')->create();

        $response = $this->be($person, 'api')->query($person->id);

        $this->compare($response, $person);
    }

    protected function shiftIDawayFromDAT()
    {
        $purchaseContract = factory(Kaufvertraege::class)->create();
        $purchaseContract->ID = $purchaseContract->DAT + 1000;
        $this->assertTrue(isset($purchaseContract->DAT));
        $purchaseContract->save();
    }

    protected function query($id)
    {
        return $this->json('POST', '/graphql', ['query' => "{
  person(id: $id) {
    purchaseContracts {
      id
    }
  }
}"]);
    }

    protected function compare(TestResponse $response, Person $person)
    {
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'person' => [
                    'purchaseContracts' => []
                ]
            ]
        ]);
        $purchaseContracts = $person->kaufvertraege;
        $this->assertFalse($purchaseContracts->isEmpty(), 'No purchase contracts found.');
        $purchaseContracts->each(function ($v) use ($response) {
            $response->assertJsonFragment([
                'id' => (string)$v->ID
            ]);
        });
    }
}