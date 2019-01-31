<?php

namespace Tests\Feature;

use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaseFieldsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Read test with all base fields set.
     *
     * @return void
     */
    public function testWithMaximumFieldsSet()
    {
        $person = factory(Person::class)->states('maximum')->create();

        $response = $this->be($person, 'api')->query($person->id);

        $this->compare($response, $person);
    }

    protected function query($id)
    {
        return $this->json('POST', '/graphql', ['query' => "query Person {
  person(id: $id) {
    id
    firstName
    lastName
    fullName
    canonicalName
    name
    gender
    birthday
    createdAt
    updatedAt
  }
}"]);
    }

    protected function compare($response, $person)
    {
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'person' => [
                        'id' => (string)$person->id,
                        'lastName' => $person->name,
                        'firstName' => $person->first_name,
                        'fullName' => $person->address_name,
                        'canonicalName' => $person->pretty_name,
                        'name' => $person->full_name,
                        'gender' => $this->dbGenderToGraphQLGender($person->sex),
                        'birthday' => $person->birthday !== null ? $person->birthday->toDateString() : null,
                        'createdAt' => $person->created_at !== null ? $person->created_at->toDateTimeString() : null,
                        'updatedAt' => $person->updated_at !== null ? $person->updated_at->toDateTimeString() : null
                    ]
                ]
            ]);
    }

    protected function dbGenderToGraphQLGender($value)
    {
        if ($value) {
            return $value === 'männlich' ? 'MALE' : 'FEMALE';
        }
        return null;
    }

    /**
     * Read test with all base fields set.
     *
     * @return void
     */
    public function testWithMinimalFieldsSet()
    {
        $person = factory(Person::class)->create();

        $response = $this->be($person, 'api')->query($person->id);

        $this->compare($response, $person);
    }
}