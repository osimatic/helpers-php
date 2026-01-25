<?php

declare(strict_types=1);

namespace Tests\Network;

use Osimatic\Network\HTTPMethod;
use Osimatic\Network\HTTPRequest;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class HTTPRequestTest extends TestCase
{
	/* ===================== execute() - Tests basiques ===================== */

	/**
	 * Note: La méthode execute() utilise cURL directement et fait de vraies requêtes HTTP.
	 * Les tests d'intégration sont marqués @group integration et skippés par défaut.
	 *
	 * Pour des tests complets sans connexion réseau, il faudrait :
	 * - Refactorer la classe pour injecter un wrapper cURL mockable
	 * - Ou utiliser des extensions PHP comme php-vcr pour enregistrer/rejouer les requêtes
	 * - Ou utiliser un serveur HTTP local de test
	 */

	#[Group("integration")]
	public function testExecuteGetRequestWithRealHttpCall(): void
	{
		$this->markTestSkipped('Ce test nécessite une connexion Internet et appelle un service externe');

		$result = HTTPRequest::execute(
			HTTPMethod::GET,
			'https://httpbin.org/get'
		);

		$this->assertIsString($result);
		$this->assertNotEmpty($result);

		$json = json_decode($result, true);
		$this->assertIsArray($json);
		$this->assertArrayHasKey('url', $json);
	}

	#[Group("integration")]
	public function testExecutePostRequestWithRealHttpCall(): void
	{
		$this->markTestSkipped('Ce test nécessite une connexion Internet et appelle un service externe');

		$result = HTTPRequest::execute(
			HTTPMethod::POST,
			'https://httpbin.org/post',
			['key' => 'value', 'foo' => 'bar']
		);

		$this->assertIsString($result);
		$this->assertNotEmpty($result);

		$json = json_decode($result, true);
		$this->assertIsArray($json);
		$this->assertArrayHasKey('form', $json);
		$this->assertEquals('value', $json['form']['key']);
		$this->assertEquals('bar', $json['form']['foo']);
	}

	#[Group("integration")]
	public function testExecuteGetRequestWithQueryParameters(): void
	{
		$this->markTestSkipped('Ce test nécessite une connexion Internet et appelle un service externe');

		$result = HTTPRequest::execute(
			HTTPMethod::GET,
			'https://httpbin.org/get',
			['param1' => 'value1', 'param2' => 'value2']
		);

		$this->assertIsString($result);

		$json = json_decode($result, true);
		$this->assertArrayHasKey('args', $json);
		$this->assertEquals('value1', $json['args']['param1']);
		$this->assertEquals('value2', $json['args']['param2']);
	}

	#[Group("integration")]
	public function testExecuteWithHeaders(): void
	{
		$this->markTestSkipped('Ce test nécessite une connexion Internet et appelle un service externe');

		$result = HTTPRequest::execute(
			HTTPMethod::GET,
			'https://httpbin.org/headers',
			[],
			['X-Custom-Header: test-value', 'X-Another-Header: another-value']
		);

		$this->assertIsString($result);

		$json = json_decode($result, true);
		$this->assertArrayHasKey('headers', $json);
		$this->assertArrayHasKey('X-Custom-Header', $json['headers']);
		$this->assertEquals('test-value', $json['headers']['X-Custom-Header']);
	}

	/* ===================== execute() - Options tests ===================== */

	#[Group("integration")]
	public function testExecuteWithUserAgent(): void
	{
		$this->markTestSkipped('Ce test nécessite une connexion Internet et appelle un service externe');

		$customUserAgent = 'MyCustomUserAgent/1.0';
		$result = HTTPRequest::execute(
			HTTPMethod::GET,
			'https://httpbin.org/user-agent',
			[],
			[],
			['user_agent' => $customUserAgent]
		);

		$this->assertIsString($result);

		$json = json_decode($result, true);
		$this->assertArrayHasKey('user-agent', $json);
		$this->assertEquals($customUserAgent, $json['user-agent']);
	}

	#[Group("integration")]
	public function testExecuteWithTimeout(): void
	{
		$this->markTestSkipped('Ce test nécessite une connexion Internet et appelle un service externe');

		// Test avec un timeout très court sur une URL qui prend du temps
		$result = HTTPRequest::execute(
			HTTPMethod::GET,
			'https://httpbin.org/delay/5',
			[],
			[],
			['time_out' => 1]
		);

		// La requête devrait échouer à cause du timeout
		$this->assertFalse($result);
	}

	#[Group("integration")]
	public function testExecuteWithHttpAuthentication(): void
	{
		$this->markTestSkipped('Ce test nécessite une connexion Internet et appelle un service externe');

		$result = HTTPRequest::execute(
			HTTPMethod::GET,
			'https://httpbin.org/basic-auth/user/passwd',
			[],
			[],
			['user_password' => 'user:passwd']
		);

		$this->assertIsString($result);

		$json = json_decode($result, true);
		$this->assertTrue($json['authenticated']);
		$this->assertEquals('user', $json['user']);
	}

	#[Group("integration")]
	public function testExecuteWithWrongHttpAuthentication(): void
	{
		$this->markTestSkipped('Ce test nécessite une connexion Internet et appelle un service externe');

		$result = HTTPRequest::execute(
			HTTPMethod::GET,
			'https://httpbin.org/basic-auth/user/passwd',
			[],
			[],
			['user_password' => 'wrong:credentials']
		);

		$this->assertIsString($result);
		// HTTP 401 devrait retourner quand même la réponse (cURL ne considère pas ça comme une erreur)
	}

	/* ===================== execute() - SSL tests ===================== */

	#[Group("integration")]
	public function testExecuteWithHttpsUrl(): void
	{
		$this->markTestSkipped('Ce test nécessite une connexion Internet et appelle un service externe');

		$result = HTTPRequest::execute(
			HTTPMethod::GET,
			'https://httpbin.org/get'
		);

		$this->assertIsString($result);
		$this->assertNotEmpty($result);
	}

	#[Group("integration")]
	public function testExecuteWithHttpUrl(): void
	{
		$this->markTestSkipped('Ce test nécessite une connexion Internet et appelle un service externe');

		$result = HTTPRequest::execute(
			HTTPMethod::GET,
			'http://httpbin.org/get'
		);

		$this->assertIsString($result);
		$this->assertNotEmpty($result);
	}

	/* ===================== execute() - Response file tests ===================== */

	#[Group("integration")]
	public function testExecuteWithResponseFile(): void
	{
		$this->markTestSkipped('Ce test nécessite une connexion Internet et appelle un service externe');

		$tempFile = tmpfile();
		$this->assertNotFalse($tempFile);

		$result = HTTPRequest::execute(
			HTTPMethod::GET,
			'https://httpbin.org/get',
			[],
			[],
			['response_file' => $tempFile]
		);

		// Quand response_file est utilisé, la méthode retourne true au lieu du contenu
		$this->assertTrue($result);

		// Vérifie que le fichier contient les données
		rewind($tempFile);
		$content = stream_get_contents($tempFile);
		$this->assertNotEmpty($content);

		$json = json_decode($content, true);
		$this->assertIsArray($json);
		$this->assertArrayHasKey('url', $json);

		fclose($tempFile);
	}

	/* ===================== execute() - Error handling ===================== */

	public function testExecuteWithInvalidUrlReturnsFalse(): void
	{
		$result = HTTPRequest::execute(
			HTTPMethod::GET,
			'invalid-url-format'
		);

		$this->assertFalse($result);
	}

	public function testExecuteWithNonExistentDomainReturnsFalse(): void
	{
		$result = HTTPRequest::execute(
			HTTPMethod::GET,
			'https://this-domain-absolutely-does-not-exist-12345678.com'
		);

		$this->assertFalse($result);
	}

	/* ===================== execute() - Logger tests ===================== */

	#[Group("integration")]
	public function testExecuteLogsSuccessfulRequest(): void
	{
		$this->markTestSkipped('Ce test nécessite une connexion Internet et appelle un service externe');

		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects($this->once())
			->method('info')
			->with($this->stringContains('HTTP response code: 200'));

		$result = HTTPRequest::execute(
			HTTPMethod::GET,
			'https://httpbin.org/get',
			[],
			[],
			[],
			$logger
		);

		$this->assertIsString($result);
	}

	public function testExecuteLogsErrorOnFailure(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects($this->once())
			->method('error')
			->with($this->stringContains('cURL request error'));

		$result = HTTPRequest::execute(
			HTTPMethod::GET,
			'invalid-url',
			[],
			[],
			[],
			$logger
		);

		$this->assertFalse($result);
	}

	/* ===================== execute() - Different HTTP methods ===================== */

	#[Group("integration")]
	public function testExecuteWithDifferentHttpMethods(): void
	{
		$this->markTestSkipped('Ce test nécessite une connexion Internet et appelle un service externe');

		// GET
		$result = HTTPRequest::execute(HTTPMethod::GET, 'https://httpbin.org/get');
		$this->assertIsString($result);

		// POST
		$result = HTTPRequest::execute(HTTPMethod::POST, 'https://httpbin.org/post');
		$this->assertIsString($result);

		// PATCH
		$result = HTTPRequest::execute(HTTPMethod::PATCH, 'https://httpbin.org/patch');
		$this->assertIsString($result);

		// DELETE
		$result = HTTPRequest::execute(HTTPMethod::DELETE, 'https://httpbin.org/delete');
		$this->assertIsString($result);
	}

	#[Group("integration")]
	public function testExecutePostWithSslAndQueryParameters(): void
	{
		$this->markTestSkipped('Ce test nécessite une connexion Internet et appelle un service externe');

		$result = HTTPRequest::execute(
			HTTPMethod::POST,
			'https://httpbin.org/post',
			['key1' => 'value1', 'key2' => 'value2']
		);

		$this->assertIsString($result);

		$json = json_decode($result, true);
		// Avec SSL, les paramètres POST sont encodés avec http_build_query
		$this->assertArrayHasKey('form', $json);
	}

	/* ===================== execute() - URL building tests ===================== */

	#[Group("integration")]
	public function testExecuteGetAppendsQueryParamsToUrlWithoutExistingQuery(): void
	{
		$this->markTestSkipped('Ce test nécessite une connexion Internet et appelle un service externe');

		$result = HTTPRequest::execute(
			HTTPMethod::GET,
			'https://httpbin.org/get',
			['foo' => 'bar', 'baz' => 'qux']
		);

		$json = json_decode($result, true);
		$this->assertEquals('bar', $json['args']['foo']);
		$this->assertEquals('qux', $json['args']['baz']);
	}

	#[Group("integration")]
	public function testExecuteGetAppendsQueryParamsToUrlWithExistingQuery(): void
	{
		$this->markTestSkipped('Ce test nécessite une connexion Internet et appelle un service externe');

		$result = HTTPRequest::execute(
			HTTPMethod::GET,
			'https://httpbin.org/get?existing=param',
			['new' => 'value']
		);

		$json = json_decode($result, true);
		$this->assertEquals('param', $json['args']['existing']);
		$this->assertEquals('value', $json['args']['new']);
	}

	/* ===================== parseRawHttpRequestData() ===================== */

	/**
	 * Note: parseRawHttpRequestData() est très difficile à tester car elle dépend de :
	 * - php://input qui n'est pas réentrant et ne peut être lu qu'une seule fois
	 * - $_SERVER['CONTENT_TYPE'] qui est une variable globale
	 *
	 * Pour tester cette méthode correctement, il faudrait :
	 * - Utiliser des tests fonctionnels avec de vraies requêtes HTTP
	 * - Ou refactorer la classe pour injecter ces dépendances
	 * - Ou utiliser des outils comme php-vcr ou des mocks de stream wrappers
	 */

	public function testParseRawHttpRequestDataWithEmptyInput(): void
	{
		// Test basique qui vérifie que la méthode peut être appelée
		// Note: Sans vraie requête HTTP, php://input sera vide
		$result = HTTPRequest::parseRawHttpRequestData();

		$this->assertIsArray($result);
	}

	public function testParseRawHttpRequestDataWithInitialData(): void
	{
		$initialData = ['existing' => 'value'];
		$result = HTTPRequest::parseRawHttpRequestData($initialData);

		$this->assertIsArray($result);
		// Les données initiales devraient être préservées ou mergées
		// mais le comportement exact dépend du contenu de php://input
	}

	public function testParseRawHttpRequestDataReturnFormat(): void
	{
		$result = HTTPRequest::parseRawHttpRequestData();

		$this->assertIsArray($result);
		// Le résultat est toujours un tableau associatif
	}

	/**
	 * Test qui documente l'utilisation attendue avec multipart/form-data
	 */
	#[Group("integration")]
	public function testParseRawHttpRequestDataWithMultipartFormData(): void
	{
		$this->markTestSkipped(
			'Test nécessitant une vraie requête HTTP multipart/form-data. ' .
			'Exemple d\'utilisation : ' .
			'POST /endpoint HTTP/1.1' . "\n" .
			'Content-Type: multipart/form-data; boundary=----WebKitFormBoundary' . "\n" .
			'Corps avec fichiers et champs de formulaire'
		);

		// Comportement attendu :
		// - Les champs de formulaire devraient être dans le tableau résultat
		// - Les fichiers devraient être dans $result['files']
	}

	/**
	 * Test documenté pour expliquer comment cette méthode devrait être testée
	 */
	public function testParseRawHttpRequestDataIntegrationTest(): void
	{
		$this->markTestSkipped(
			'Cette méthode nécessite un test fonctionnel avec de vraies requêtes HTTP. ' .
			'Pour tester correctement : ' .
			'1. Créer un script PHP qui appelle parseRawHttpRequestData() ' .
			'2. Envoyer des requêtes multipart/form-data avec des fichiers ' .
			'3. Vérifier que les données et fichiers sont correctement parsés'
		);
	}

	/**
	 * Test qui documente l'utilisation avec form-urlencoded
	 */
	#[Group("integration")]
	public function testParseRawHttpRequestDataWithFormUrlEncoded(): void
	{
		$this->markTestSkipped(
			'Test nécessitant une vraie requête HTTP avec Content-Type: application/x-www-form-urlencoded'
		);

		// Comportement attendu :
		// - Les données devraient être parsées avec parse_str()
		// - Le résultat devrait contenir les paires clé-valeur du formulaire
	}
}
