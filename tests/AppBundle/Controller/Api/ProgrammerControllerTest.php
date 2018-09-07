<?php

namespace Tests\AppBundle\Controller\Api;

use AppBundle\Test\ApiTestCase;

class ProgrammerControllerTest extends ApiTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->createUser('weaverryan');
    }

    public function testPOSTProgrammerWorks()
    {
        $data = [
            'nickname' => 'ObjectOrienter',
            'avatarNumber' => 5,
            'tagLine' => 'a test dev!'
        ];

        // 1) Create a programmer resource
        $response = $this->client->post('/api/programmers', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('weaverryan')
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertStringEndsWith('/api/programmers/ObjectOrienter', $response->getHeader('Location')[0]);
        $finishedData = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('nickname', $finishedData);
        $this->assertEquals('ObjectOrienter', $finishedData['nickname']);

    }



    public function testGETProgrammer()
    {
        $this->createProgrammer([
            'nickname' => 'UnitTester',
            'avatarNumber' => 3,
        ]);

        $response = $this->client->get('/api/programmers/UnitTester', [
            'headers' => $this->getAuthorizedHeaders('weaverryan')
        ]);
        $this->assertEquals(200, $response->getStatusCode());

        $this->asserter()->assertResponsePropertiesExist($response, [
            'nickname',
            'avatar_number',
            'tag_line',
            'power_level',
            '_links'
        ]);

        $this->asserter()->assertResponsePropertyEquals($response, 'nickname', 'UnitTester');
        $this->asserter()->assertResponsePropertyEquals($response, '_links.self', '/api/programmers/UnitTester');
    }



    public function testGETProgrammersCollection()
    {

        $this->createProgrammer(array(
            'nickname' => 'UnitTester',
            'avatarNumber' => 3,
        ));
        $this->createProgrammer(array(
            'nickname' => 'CowboyCoder',
            'avatarNumber' => 5,
        ));

        $response = $this->client->get('/api/programmers', [
            'headers' => $this->getAuthorizedHeaders('weaverryan')
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyIsArray($response, 'items');
        $this->asserter()->assertResponsePropertyCount($response, 'items', 2);
        $this->asserter()->assertResponsePropertyEquals($response, 'items[1].nickname', 'CowboyCoder');

    }

    public function testGETProgrammersCollectionPaginated()
    {

        $this->createProgrammer(array(
            'nickname' => 'willnotmatch',
            'avatarNumber' => 5,
        ));

        for ($i = 0; $i < 25; $i++) {
            $this->createProgrammer(array(
                'nickname' => 'Programmer'.$i,
                'avatarNumber' => 3,
            ));
        }


        $response = $this->client->get('/api/programmers?filter=programmer', [
            'headers' => $this->getAuthorizedHeaders('weaverryan')
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'items[5].nickname', 'Programmer5');
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 10);
        $this->asserter()->assertResponsePropertyEquals($response, 'total', 25);
        $this->asserter()->assertResponsePropertyExists($response, '_links.next');

        $nextLink = $this->asserter()->readResponseProperty($response, '_links.next');
        $response = $this->client->get($nextLink, [
            'headers' => $this->getAuthorizedHeaders('weaverryan')
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'items[5].nickname',
            'Programmer15'
        );
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 10);


        $lastLink = $this->asserter()->readResponseProperty($response, '_links.last');
        $response = $this->client->get($lastLink, [
            'headers' => $this->getAuthorizedHeaders('weaverryan')
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'items[4].nickname',
            'Programmer24'
        );
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 5);


    }



    public function testPUTProgrammer()
    {

        $this->createProgrammer(array(
            'nickname' => 'CowboyCoder',
            'avatarNumber' => 5,
            'tagLine' => 'foo',
        ));

        $data = [
            'nickname' => 'CowboyCoder',
            'avatarNumber' => 2,
            'tagLine' => 'foo',
        ];

        $response = $this->client->put('/api/programmers/CowboyCoder', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('weaverryan')
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'avatar_number', 2);
        $this->asserter()->assertResponsePropertyEquals($response, 'nickname', 'CowboyCoder');

    }


    public function testDELETEProgrammer()
    {
        $this->createProgrammer(array(
            'nickname' => 'UnitTester',
            'avatarNumber' => 3,
        ));
        $response = $this->client->delete('/api/programmers/UnitTester', [
            'headers' => $this->getAuthorizedHeaders('weaverryan')
        ]);
        $this->assertEquals(204, $response->getStatusCode());
    }


    public function testPATCHProgrammer()
    {
        $this->createProgrammer(array(
            'nickname' => 'CowboyCoder',
            'avatarNumber' => 5,
            'tagLine' => 'foo',
        ));
        $data = array(
            'tagLine' => 'bar',
        );
        $response = $this->client->patch('/api/programmers/CowboyCoder', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('weaverryan')
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'avatar_number', 5);
        $this->asserter()->assertResponsePropertyEquals($response, 'tag_line', 'bar');
    }


    public function testValidationErrors()
    {

        $data = [
            'avatarNumber' => 5,
            'tagLine' => 'a test dev!'
        ];

        $response = $this->client->post('/api/programmers', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('weaverryan')
        ]);
        $this->assertEquals(400, $response->getStatusCode());
        $this->asserter()->assertResponsePropertiesExist($response, [
            'type',
            'title',
            'errors',
        ]);
        $this->asserter()->assertResponsePropertyExists($response, 'errors.nickname');
        $this->asserter()->assertResponsePropertyEquals($response, 'errors.nickname[0]', 'Please enter a clever nickname');
        $this->asserter()->assertResponsePropertyDoesNotExist($response, 'errors.avatarNumber');
        $this->assertEquals('application/problem+json', $response->getHeader('Content-Type')[0]);
    }

    public function testInvalidJson()
    {
        $invalidBody = <<<EOF
        {
            "avatarNumber" : "2
            "tagLine": "I'm from a test!"
        }
EOF;
        
        
        $response = $this->client->post('/api/programmers', [
            'body' => $invalidBody,
            'headers' => $this->getAuthorizedHeaders('weaverryan')
        ]);
        $this->assertEquals(400, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyContains($response, 'type', 'invalid_body_format');
        $this->asserter()->assertResponsePropertyEquals($response, 'title', 'Invalid JSON format sent');

    }


    public function test404Exception()
    {
        $response = $this->client->get('/api/programmers/fake', [
            'headers' => $this->getAuthorizedHeaders('weaverryan')
        ]);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/problem+json', $response->getHeader('Content-Type')[0]);
        $this->asserter()->assertResponsePropertyEquals($response, 'type', 'about:blank');
        $this->asserter()->assertResponsePropertyEquals($response, 'title', 'Not Found');
        $this->asserter()->assertResponsePropertyEquals($response, 'detail', 'AppBundle\Entity\Programmer object not found by the @ParamConverter annotation.');
    }


    public function testGETProgrammerDeep()
    {
        $this->createProgrammer([
            'nickname' => 'UnitTester',
            'avatarNumber' => 3,
        ]);

        $response = $this->client->get('/api/programmers/UnitTester?deep=1', [
            'headers' => $this->getAuthorizedHeaders('weaverryan')
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertiesExist($response, array(
            'user.username'
        ));
        $this->asserter()->assertResponsePropertiesExist($response, array(
            'user.email'
        ));

    }


    public function testRequiresAuthentication()
    {
        $response = $this->client->post('/api/programmers', [
            'body' => '[]'
        ]);

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testBadToken()
    {
        $response = $this->client->post('/api/programmers', [
            'body' => '[]',
            'headers' => [
                'Authorization' => 'Bearer WRONG'
            ]
        ]);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('application/problem+json', $response->getHeader('Content-Type')[0]);
    }

}
