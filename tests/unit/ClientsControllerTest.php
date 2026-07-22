<?php

/**
 * ============================================================
 *  PHPUNIT TEST EXAMPLE FOR CLIENTS CONTROLLER
 * ============================================================
 *
 *  In this test suite for `Clients.php`, we test features like:
 *  1. Access Control (Session roles / Redirects for unauthenticated users)
 *  2. Mocking `ClientModel` to return fake client data
 *  3. Testing JSON responses from Controller endpoints
 *
 * ============================================================
 */

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use App\Controllers\Clients;
use App\Models\ClientModel;

final class ClientsControllerTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    /** @var ClientModel|\PHPUnit\Framework\MockObject\MockObject */
    private $mockClientModel;

    // =========================================================================
    //  setUp() — runs BEFORE every test automatically
    // =========================================================================
    protected function setUp(): void
    {
        parent::setUp();

        // Create a fake version of ClientModel
        $this->mockClientModel = $this->createMock(ClientModel::class);

        // Populate superglobal $_SERVER['REQUEST_URI'] for view partials that read it directly
        $_SERVER['REQUEST_URI'] = '/clients';
    }

    // =========================================================================
    //  HELPER: injectMock()
    //  ─────────────────────
    //  Injects the mock model into $this->controller->ClientModel property
    // =========================================================================
    private function injectMock(): void
    {
        $ref      = new ReflectionClass($this->controller);
        $property = $ref->getProperty('ClientModel');
        $property->setAccessible(true);
        $property->setValue($this->controller, $this->mockClientModel);
    }

    // =========================================================================
    //  HELPER: setSession(array $sessionData)
    //  ──────────────────────────────────────
    //  Simulates a logged in user session (e.g. setting 'login' => 1, 'role' => '1')
    // =========================================================================
    private function setSession(array $sessionData): void
    {
        $session = session();
        foreach ($sessionData as $key => $value) {
            $session->set($key, $value);
        }
    }

    // =========================================================================
    //  HELPER: setPostData(array $data)
    //  ─────────────────────────────────
    //  Populates $_POST data for the request
    // =========================================================================
    private function setPostData(array $data): void
    {
        $this->request->setGlobal('post', $data);
    }

    // =========================================================================
    //  TEST 1 — index() redirects to /login if user is not logged in
    // =========================================================================
    public function testIndexRedirectsToLoginWhenNotLoggedIn(): void
    {
        // Make sure session login flag is cleared/not 1
        $this->setSession(['login' => 0]);

        $result = $this->withUri('http://localhost/clients')
            ->controller(Clients::class)
            ->execute('index');

        // assertRedirectTo checks if the controller redirects to expected URL
        $result->assertRedirectTo(base_url('login'));
    }

    // =========================================================================
    //  TEST 2 — index() redirects to /access-forbidden if user role is not allowed
    // =========================================================================
    public function testIndexRedirectsToAccessForbiddenWhenRoleNotAllowed(): void
    {
        // Logged in = 1, but role is '99' (not in ['1','2','3','4','6'])
        $this->setSession([
            'login' => 1,
            'role'  => '99'
        ]);

        $result = $this->withUri('http://localhost/clients')
            ->controller(Clients::class)
            ->execute('index');

        $result->assertRedirectTo(base_url('access-forbidden'));
    }

    // =========================================================================
    //  TEST 3 — index() loads clients view when logged in with allowed role
    // =========================================================================
    public function testIndexLoadsViewWhenAuthorized(): void
    {
        // Logged in = 1, role = '1' (Allowed role)
        $this->setSession([
            'login' => 1,
            'role'  => '1'
        ]);

        $result = $this->withUri('http://localhost/clients')
            ->controller(Clients::class)
            ->execute('index');

        $result->assertOK(); // HTTP 200
    }

    // =========================================================================
    //  TEST 4 — get_table_clients() returns JSON client array
    // =========================================================================
    public function testGetTableClientsReturnsJsonData(): void
    {
        // Set allowed role session
        $this->setSession(['role' => '1']);

        // Create fake client list
        $fakeClients = [
            ['id' => 'cl_1', 'client_name' => 'Acme Corp'],
            ['id' => 'cl_2', 'client_name' => 'Stark Industries']
        ];

        // Tell mock model to return fake clients when get_clients() is called
        $this->mockClientModel
            ->expects($this->once())
            ->method('get_clients')
            ->willReturn($fakeClients);

        $this->withUri('http://localhost/clients/get_table_clients');
        $this->controller(Clients::class);

        $this->injectMock();

        $result = $this->execute('get_table_clients');

        $body = json_decode($result->response()->getBody(), true);

        $this->assertSame($fakeClients, $body);
    }

    // =========================================================================
    //  TEST 5 — get_client_volume() returns error if invalid parameters given
    // =========================================================================
    public function testGetClientVolumeReturnsErrorOnInvalidParams(): void
    {
        $this->setSession(['role' => '1']);

        $this->withUri('http://localhost/clients/get_client_volume');
        $this->controller(Clients::class);

        $this->injectMock();
        
        // Passing incomplete / invalid POST data (missing date_from, invalid type)
        $this->setPostData([
            'client_id' => 'cl_1',
            'type'      => 'invalid_type'
        ]);

        $result = $this->execute('get_client_volume');

        $body = json_decode($result->response()->getBody(), true);

        $this->assertSame('error', $body['status']);
        $this->assertSame('Invalid parameters', $body['message']);
    }

    // =========================================================================
    //  TEST 6 — get_client_volume() returns success with total_qty
    // =========================================================================
    public function testGetClientVolumeReturnsSuccess(): void
    {
        $this->setSession(['role' => '1']);

        // Tell mock model to return 150.50 for valid parameters
        $this->mockClientModel
            ->expects($this->once())
            ->method('get_client_volume')
            ->with('cl_123', '2026-01-01', '2026-01-31', 'si', null)
            ->willReturn('150.50');

        $this->withUri('http://localhost/clients/get_client_volume');
        $this->controller(Clients::class);

        $this->injectMock();

        $this->setPostData([
            'client_id'        => 'cl_123',
            'date_from'        => '2026-01-01',
            'date_to'          => '2026-01-31',
            'type'             => 'si',
            'product_filter_id' => null
        ]);

        $result = $this->execute('get_client_volume');

        $body = json_decode($result->response()->getBody(), true);

        $this->assertSame('success', $body['status']);
        $this->assertSame('150.50', $body['total_qty']);
    }
}
