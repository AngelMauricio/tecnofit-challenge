<?php
declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Controllers\MovementController;
use App\Repositories\MovementRepository;
use stdClass;
use Exception;

class MovementIntegrationTest extends TestCase
{
    private $repositoryMock;
    private $controller;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(MovementRepository::class);
        $this->controller = new MovementController($this->repositoryMock);
        
        // Reset HTTP response code before each test
        http_response_code(200);
    }

    private function mockFoundData(): array
    {
        // Simulate PDO::FETCH_OBJ return structure
        $obj = new stdClass();
        $obj->ranking_position = 1;
        $obj->user_name = 'Test User';
        $obj->personal_record = 100;
        
        return [$obj];
    }

    private function mockMovementObject(): object
    {
        // Simulate a found movement record
        $obj = new stdClass();
        $obj->id = 1;
        $obj->name = 'Mocked Movement';
        
        return $obj;
    }

    // 1. Pesquisar com "1" -> Lista
    public function testSearchWithIdOneReturnsList(): void
    {
        $this->repositoryMock->method('get')->with('1')->willReturn($this->mockMovementObject());
        $this->repositoryMock->method('getRanking')->with('1')->willReturn($this->mockFoundData());

        $result = $this->controller->get('1');
        
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    // 2. Pesquisar com "Deadlift" -> Lista
    public function testSearchWithDeadliftNameReturnsList(): void
    {
        $this->repositoryMock->method('get')->with('Deadlift')->willReturn($this->mockMovementObject());
        $this->repositoryMock->method('getRanking')->with('Deadlift')->willReturn($this->mockFoundData());

        $result = $this->controller->get('Deadlift');
        
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    // 3. Pesquisar por "bla" -> 404
    public function testSearchForNonExistentMovementReturns404(): void
    {
        // Simulate Repository throwing an exception for not found
        $this->repositoryMock->method('get')
            ->with('bla')
            ->willThrowException(new Exception("Movement not found", 404));

        $result = $this->controller->get('bla');
        
        $this->assertEquals(404, http_response_code());
        $this->assertArrayHasKey('error', $result);
    }

    // 4. Pesquisar por "\";" -> 404
    public function testSearchWithInjectionCharsReturns404(): void
    {
        $this->repositoryMock->method('get')
            ->with("\";")
            ->willThrowException(new Exception("Invalid input", 404));

        $result = $this->controller->get("\";");
        
        $this->assertEquals(404, http_response_code());
    }

    // 5. Pesquisar por "Back Squat" -> Lista
    public function testSearchWithBackSquatSpaceReturnsList(): void
    {
        $this->repositoryMock->method('get')->with('Back Squat')->willReturn($this->mockMovementObject());
        $this->repositoryMock->method('getRanking')->with('Back Squat')->willReturn($this->mockFoundData());

        $result = $this->controller->get('Back Squat');
        
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    // 6. Pesquisar por 3 -> Lista Vazia (existe, mas sem records)
    public function testSearchWithIdThreeReturnsEmptyList(): void
    {
        // Movement exists
        $this->repositoryMock->method('get')->with('3')->willReturn($this->mockMovementObject());
        // But ranking is empty
        $this->repositoryMock->method('getRanking')->with('3')->willReturn([]);

        $result = $this->controller->get('3');
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    // 7. Pesquisar por 4 -> 404
    public function testSearchWithIdFourReturns404(): void
    {
        $this->repositoryMock->method('get')
            ->with('4')
            ->willThrowException(new Exception("Movement not found", 404));

        $result = $this->controller->get('4');
        
        $this->assertEquals(404, http_response_code());
    }
}