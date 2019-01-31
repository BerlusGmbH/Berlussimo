<?php

namespace Tests\Feature;

use App\Models\Details;
use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotesRelationshipTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Read test with all base fields set.
     *
     * @return void
     */
    public function testWithMinimalFieldsSet()
    {
        $this->shiftIDawayFromDAT();

        $person = factory(Person::class)->states('with.note')->create();

        $response = $this->be($person, 'api')->query($person->id);

        $this->compare($response, $person);
    }

    protected function shiftIDawayFromDAT()
    {
        $note = factory(Details::class)->create();
        $note->DETAIL_ID = $note->DETAIL_DAT + 1000;
        $this->assertTrue(isset($note->DETAIL_DAT));
        $note->save();
    }

    protected function query($id)
    {
        return $this->json('POST', '/graphql', ['query' => "{
  person(id: $id) {
    notes {
      id
      value
      comment
    }
  }
}"]);
    }

    protected function compare($response, $person)
    {
        $note = $person->hinweise->first();
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'person' => [
                        'notes' => [
                            [
                                'id' => $note->DETAIL_ID,
                                'value' => $note->DETAIL_INHALT,
                                'comment' => $note->DETAIL_BEMERKUNG,
                            ]
                        ]
                    ]
                ]
            ]);
    }
}