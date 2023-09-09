<?php

namespace App\Tests;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\ApiToken;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProductTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

  
    // private const API_TOKEN = '12f3600378cbb476613d4cec52b56730bc0bcf77d5925538eeffd8dde2d3c4d71e8bb741f0b8cd6f090c328a7535340bf0e7121786566f1f60501d29';
    // private const API_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2OTQxNzY5MzksImV4cCI6MTY5NDE4MDUzOSwicm9sZXMiOlsiUk9MRV9VU0VSIl0sInVzZXJuYW1lIjoicmF3aUBnbWFpbC5jb20ifQ.lqhyiezu8DmZ_YA_iqsNfzXzD8WMnIA0qt2Rp5JE4cEzibDF5_ETeS0byLvdx-lzwVLcltpKJsfFrRg-Q6Jl3xddaB2XlguNGZQGUXPTmnTg206KE6ppGkE0H0sV0FUT6sgE_tr2tArmuRcsFmpgpWVds502WnUvUI0z7zJuGxF0KprxIemymwjjCDUI2Ickt6y-F1KlfjuBTtk8wZiRhhEHDPDZxv7V4-W2Vjzggt3x-ipKTdxFG6RF0dBcpPWwC9-AbXCEvl1ko6_ineezHflHTxaUyUKZ9Utd7aRHmeWPIKgSoj-B_3W7H-T5lCZSCzfGmJfIM4ptXZNcYATABiPW2ONZTalzcFneDAn1shhdEyao2wWFPkz1GxulRaTrK4LmhqmyZqwe-yXaty9-LnBVzMbQPpjAee8RQD3-m-Sdl19F1T7G3JBsOE7RFEl2_4d6iWBq0DDzCAPPGVtflxOK8w3O76Wd3_XQvRzfjHhAIm_W_9A_6ACfHnSOng1NMX4bm6jZo0IeA7I_B_YHg4OgqaL4MNbdODS7FFrHPCr5ECcyZnK2DRz-fTQ-tRXaNmJTcwD3jf0CXcHLHzf_DNCsUfWpxHtVhZHjQAnJawm27gKUM1W1YwG2Zby-nOZw31JTk-O47J3xBugEuJ7x-oz5QrdUBvA2pt1KLqfGNRI';
    private  $api_token;
    private HttpClientInterface $client;
    private EntityManagerInterface $entityManager;
    private $passwordHasher;

    protected function setUp(): void
    {
        // $this->client = $this->createClient();
        // $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        // $user = new User();
        // $user->setEmail('info@garyclarke.tech');
        // $user->setPassword('garyclarketech');
        // $this->entityManager->persist($user);
        // $this->entityManager->flush();

        // $apiToken = new ApiToken();
        // $apiToken->setToken(self::API_TOKEN);api_token
        // $apiToken->setUser($user);
        // $this->entityManager->persist($apiToken);
        // $this->entityManager->flush();

        $this->client = $this->createClient();
        $container = self::getContainer();
        $this->passwordHasher = $this->getContainer()
        ->get(UserPasswordHasherInterface::class);
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, '$3CR3T')
        );
        
        $manager = $container->get('doctrine')->getManager();
        $manager->persist($user);
        $manager->flush();
        // retrieve a token
        $response = $this->client->request('POST', '/auth', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => 'test@example.com',
                'password' => '$3CR3T',
            ],
        ]);
        $json = $response->toArray();
        $this->assertResponseIsSuccessful();
        $this->assertArrayHasKey('token', $json);

        $this->api_token = $json['token'];
    }

    public function testGetCollection(): void
    {
        $response = $this->client->request('GET', '/api/products',  [
            'headers' => ['auth_bearer' => $this->api_token]
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/api/contexts/Product',
            '@id' => '/api/products',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 100,
            'hydra:view' => [
                '@id' => '/api/products?page=1',
                '@type' => 'hydra:PartialCollectionView',
                'hydra:first' => '/api/products?page=1',
                'hydra:last' => '/api/products?page=50',
                'hydra:next' => '/api/products?page=2',
            ],
        ]);

        $this->assertCount(2, $response->toArray()['hydra:member']);
    }

    public function testPagination(): void
    {
        $response = $this->client->request('GET', '/api/products?page=2', [
            'headers' => ['x-api-token' => self::API_TOKEN]
        ]);

        $this->assertJsonContains([
            'hydra:view' => [
                '@id' => '/api/products?page=2',
                '@type' => 'hydra:PartialCollectionView',
                'hydra:first' => '/api/products?page=1',
                'hydra:last' => '/api/products?page=50',
                'hydra:previous' => '/api/products?page=1',
                'hydra:next' => '/api/products?page=3',
            ],
        ]);
    }

    public function testCreatProduct(): void
    {
        $this->client->request('POST', '/api/products', [
            'headers' => ['x-api-token' => self::API_TOKEN],
            'json' => [
                "mpn" => "1234",
                "name" => "A test product",
                "description" => "A test product description",
                "issueDate" => "2023-09-07T12:23:00.872Z",
                "manufacturer" => "/api/manufacturers/1"
            ]
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            "@context" => "/api/contexts/Product",
            "@type" => "Product",
            "mpn" => "1234",
            "name" => "A test product",
            "description" => "A test product description",
            "issueDate" => "2023-09-07T00:00:00+00:00",
            "manufacturer" => "/api/manufacturers/1",
        ]);
    }

    public function testUpdateProduct(): void
    {
        $this->client->request('PUT', '/api/products/1', [
            'headers' => ['x-api-token' => self::API_TOKEN],
            'json' => [
                'description' => 'An updated description'
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => '/api/products/1',
            'description' => 'An updated description'
        ]);
    }

    public function testCreateInvalidProduct(): void
    {

        $this->client->request('POST', '/api/products', [
            'headers' => ['x-api-token' => self::API_TOKEN],
            'json' => [
                "mpn" => "1234",
                "description" => "A test product description",
                "issueDate" => "2023-09-07T12:23:00.872Z",
                "manufacturer" => '/api/manufacturers/1'
            ]
        ]);

        $this->assertResponseStatusCodeSame(422);
        // $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            "@context" => "/api/contexts/ConstraintViolationList",
            "@type" => "ConstraintViolationList",
            "hydra:title" => "An error occurred",
            "hydra:description" => "name: This value should not be blank.",
        ]);
    }

    public function testInvalidToken(): void
    {
        $this->client->request('PUT', '/api/products/1', [
            'headers' => ['x-api-token' => 'fake-token'],
            'json'    => [
                'description' => 'An updated description',
            ]
        ]);

        $this->assertResponseStatusCodeSame(401);
        $this->assertJsonContains([
            'message'         => 'Invalid credentials.',
        ]);
    }
}
