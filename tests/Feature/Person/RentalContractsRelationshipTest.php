<?php

namespace Tests\Feature;

use App\Models\Mietvertraege;
use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;

class RentalContractsRelationshipTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Read test with all base fields set.
     *
     * @return void
     */
    public function testRentalContractRelationship()
    {
        $this->shiftIDawayFromDAT();

        $person = factory(Person::class)->states('with.rentalContract')->create();

        $response = $this->be($person, 'api')->query($person->id);

        $this->compare($response, $person);
    }

    protected function shiftIDawayFromDAT()
    {
        $rentalContract = factory(Mietvertraege::class)->create();
        $rentalContract->MIETVERTRAG_ID = $rentalContract->MIETVERTRAG_DAT + 1000;
        $this->assertTrue(isset($rentalContract->MIETVERTRAG_DAT));
        $rentalContract->save();
    }

    protected function query($id)
    {
        return $this->json('POST', '/graphql', ['query' => "{
  person(id: $id) {
    rentalContracts {
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
                    'rentalContracts' => []
                ]
            ]
        ]);
        $rentalContracts = $person->mietvertraege;
        $this->assertFalse($rentalContracts->isEmpty(), 'No rental contracts found.');
        $rentalContracts->each(function ($v) use ($response) {
            $response->assertJsonFragment([
                'id' => (string)$v->MIETVERTRAG_ID
            ]);
        });
    }
}