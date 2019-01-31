<?php

namespace Tests\Feature;

use App\Models\Details;
use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;

class DetailsRelationshipTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Read test with all base fields set.
     *
     * @return void
     */
    public function testDetailsRelationship()
    {
        $this->shiftIDawayFromDAT();

        $person = factory(Person::class)->states('with.detail')->create();

        $response = $this->be($person, 'api')->query($person->id);

        $this->compare($response, $person);
    }

    protected function shiftIDawayFromDAT()
    {
        $detail = factory(Details::class)->create();
        $detail->DETAIL_ID = $detail->DETAIL_DAT + 1000;
        $this->assertTrue(isset($detail->DETAIL_DAT));
        $detail->save();
    }

    protected function query($id)
    {
        return $this->json('POST', '/graphql', ['query' => "{
  person(id: $id) {
    details {
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
                    'details' => []
                ]
            ]
        ]);
        $details = $person->commonDetails;
        $this->assertFalse($details->isEmpty(), 'No purchase contracts found.');
        $details->each(function ($v) use ($response) {
            $response->assertJsonFragment([
                'id' => (string)$v->DETAIL_ID
            ]);
        });
    }
}