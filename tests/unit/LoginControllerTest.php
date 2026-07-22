<?php

/**
 * ============================================================
 *  PHPUNIT + CODEIGNITER 4 — HOW IT ALL WORKS (Read first!)
 * ============================================================
 *
 *  WHAT IS PHPUNIT?
 *  ─────────────────
 *  PHPUnit is a testing tool that automatically runs PHP functions
 *  called "tests" and checks if your real code works as expected.
 *
 *  You write tests. PHPUnit runs them. It tells you:
 *    ✔ which ones passed (green dot)
 *    ✘ which ones failed (red F) — and exactly WHY
 *
 *  ┌──────────────────────────────────────────────────────────┐
 *  │  HOW TO RUN THE TESTS                                    │
 *  │                                                          │
 *  │  ALL tests:                                              │
 *  │    vendor\bin\phpunit                                    │
 *  │                                                          │
 *  │  ONLY this file:                                         │
 *  │    vendor\bin\phpunit tests/unit/LoginControllerTest.php │
 *  │                                                          │
 *  │  With pretty output:                                     │
 *  │    vendor\bin\phpunit --testdox                          │
 *  └──────────────────────────────────────────────────────────┘
 *
 *  ANATOMY OF A TEST
 *  ──────────────────
 *  1. A class that EXTENDS CIUnitTestCase
 *  2. Methods whose name STARTS WITH "test" (PHPUnit finds them automatically)
 *  3. Each method uses "assertions" to verify behavior:
 *
 *     assertSame($expected, $actual)  → value + type must match exactly
 *     assertTrue($value)              → must be true
 *     assertFalse($value)             → must be false
 *
 *  WHAT IS A MOCK?
 *  ────────────────
 *  The Login controller needs CoreModel to talk to the database.
 *  We DON'T want a real database in tests — it's slow and fragile.
 *
 *  A "mock" is a FAKE CoreModel we fully control.
 *  We configure it: "when user_login('admin') is called → return this fake user"
 *  This lets us test EVERY scenario reliably and instantly.
 *
 *  CORRECT ORDER TO RUN THE CI4 CONTROLLER TEST TRAIT
 *  ────────────────────────────────────────────────────
 *  The ControllerTestTrait has these important chain methods:
 *
 *    withUri(url)      → REBUILDS the $this->request object from scratch.
 *                        Must be called FIRST so the controller gets the right request.
 *
 *    controller(class) → Creates new Login(); passes $this->request to it.
 *                        Must be called AFTER withUri() so the right request is wired up.
 *
 *    execute(method)   → Calls Login::authenticate() etc.
 *
 *  BETWEEN controller() and execute() we:
 *    1. injectMock()    → swap $coreModel for our fake one
 *    2. setPostData()   → populate POST fields so getPost() works
 *
 * ============================================================
 */

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use App\Controllers\Login;
use App\Models\CoreModel;

final class LoginControllerTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    /** @var CoreModel mock */
    private $mockCoreModel;

    // =========================================================================
    //  setUp() — runs BEFORE every test automatically
    // =========================================================================
    protected function setUp(): void
    {
        parent::setUp(); // Always first — boots the CI4 framework

        // createMock() → PHPUnit generates a fake CoreModel.
        // All methods exist but return null unless configured with willReturn().
        $this->mockCoreModel = $this->createMock(CoreModel::class);
    }

    // =========================================================================
    //  HELPER: injectMock()
    //  ─────────────────────
    //  Uses PHP Reflection to replace the protected $coreModel property
    //  inside the Login controller with our fake model.
    //  Must be called AFTER controller() has created the Login instance.
    // =========================================================================
    private function injectMock(): void
    {
        $ref      = new ReflectionClass($this->controller);
        $property = $ref->getProperty('coreModel');
        $property->setAccessible(true);
        $property->setValue($this->controller, $this->mockCoreModel);
    }

    // =========================================================================
    //  HELPER: setPostData(array $data)
    //  ─────────────────────────────────
    //  Populates the POST superglobal on the request object.
    //  $this->request->getPost('username') reads from globals['post'].
    //  setGlobal('post', [...]) is how we set it in tests.
    //  Must be called AFTER controller() (and after withUri() has rebuilt the request).
    // =========================================================================
    private function setPostData(array $data): void
    {
        $this->request->setGlobal('post', $data);
    }

    // =========================================================================
    //  TEST 1 — index() returns the login page (HTTP 200)
    // =========================================================================
    //  SCENARIO : User visits /login
    //  EXPECTED : HTTP 200 OK (the view loads successfully)
    //
    public function testIndexReturnsLoginView(): void
    {
        $result = $this->controller(Login::class)
            ->withUri('http://localhost/login')
            ->execute('index');

        // assertOK() checks the HTTP response status code is 200
        $result->assertOK();
    }

    // =========================================================================
    //  TEST 2 — authenticate() with CORRECT username + CORRECT password
    // =========================================================================
    //  SCENARIO : admin + secret123 (valid credentials)
    //  EXPECTED : {"status":"success","message":"Login success"}
    //
    public function testAuthenticateWithCorrectCredentialsReturnsSuccess(): void
    {
        // ── Build a fake user row from the database ───────────────────────────
        //
        //  stdClass = PHP's generic object. We make it look like a real DB row.
        //  Real DB passwords are bcrypt hashes — we use password_hash() to match.
        //
        $fakeUser           = new stdClass();
        $fakeUser->id       = 1;
        $fakeUser->username = 'admin';
        $fakeUser->role_id  = 1;
        $fakeUser->password = password_hash('secret123', PASSWORD_BCRYPT);
        //                    ↑ This creates a hash like: $2y$10$abc...
        //                      password_verify('secret123', that_hash) → true ✓

        // ── Configure the mock ────────────────────────────────────────────────
        //
        //  expects($this->once())   → user_login() MUST be called exactly once
        //  method('user_login')     → we're configuring this method
        //  willReturn([$fakeUser])  → return array with our fake user when called
        //  (we skip with() here so ANY argument triggers the return value)
        //
        $this->mockCoreModel
            ->expects($this->once())
            ->method('user_login')
            ->willReturn([$fakeUser]);

        // ── Correct order: withUri() → controller() → inject → setPost → execute ──
        //
        //  withUri()      → MUST be first — it rebuilds $this->request
        //  controller()   → MUST be after withUri() — it passes $this->request to Login
        //
        $this->withUri('http://localhost/login/authenticate');
        $this->controller(Login::class);

        $this->injectMock();        // swap real CoreModel for fake
        $this->setPostData([        // populate POST fields
            'username' => 'admin',
            'password' => 'secret123',
        ]);

        $result = $this->execute('authenticate');

        // ── Check the JSON response ───────────────────────────────────────────
        $body = json_decode($result->response()->getBody(), true);

        $this->assertSame('success', $body['status'],
            'Expected status=success when credentials are correct'
        );
        $this->assertSame('Login success', $body['message']);
    }

    // =========================================================================
    //  TEST 3 — authenticate() with CORRECT username but WRONG password
    // =========================================================================
    //  SCENARIO : admin + wrongpassword (user exists, password is wrong)
    //  EXPECTED : {"status":"error","message":"Invalid password"}
    //
    public function testAuthenticateWithWrongPasswordReturnsError(): void
    {
        $fakeUser           = new stdClass();
        $fakeUser->id       = 1;
        $fakeUser->username = 'admin';
        $fakeUser->role_id  = 1;
        $fakeUser->password = password_hash('secret123', PASSWORD_BCRYPT); // real = secret123

        $this->mockCoreModel
            ->expects($this->once())
            ->method('user_login')
            ->willReturn([$fakeUser]); // user IS found...

        $this->withUri('http://localhost/login/authenticate');
        $this->controller(Login::class);

        $this->injectMock();
        $this->setPostData([
            'username' => 'admin',
            'password' => 'wrongpassword', // ← but we send the WRONG password
        ]);

        $result = $this->execute('authenticate');

        $body = json_decode($result->response()->getBody(), true);

        // password_verify('wrongpassword', $hash_of_secret123) → false
        // So the controller returns 'Invalid password'
        $this->assertSame('error', $body['status'],
            'Expected status=error when password is wrong'
        );
        $this->assertSame('Invalid password', $body['message']);
    }

    // =========================================================================
    //  TEST 4 — authenticate() with a username that does NOT exist in DB
    // =========================================================================
    //  SCENARIO : ghost_user (this username is not in the database at all)
    //  EXPECTED : {"status":"error","message":"User not found"}
    //
    public function testAuthenticateWithUnknownUsernameReturnsError(): void
    {
        // willReturn([]) → empty array = no rows found in DB
        $this->mockCoreModel
            ->expects($this->once())
            ->method('user_login')
            ->willReturn([]); // ← empty = user not found

        $this->withUri('http://localhost/login/authenticate');
        $this->controller(Login::class);

        $this->injectMock();
        $this->setPostData([
            'username' => 'ghost_user',
            'password' => 'doesntmatter',
        ]);

        $result = $this->execute('authenticate');

        $body = json_decode($result->response()->getBody(), true);

        $this->assertSame('error', $body['status']);
        $this->assertSame('User not found', $body['message']);
    }

    // =========================================================================
    //  TEST 5 — logout() destroys the session and returns success JSON
    // =========================================================================
    //  SCENARIO : User visits /login/logout
    //  EXPECTED : {"status":"success","message":"Logged out successfully"}
    //
    public function testLogoutReturnsSuccess(): void
    {
        $result = $this->controller(Login::class)
            ->withUri('http://localhost/login/logout')
            ->execute('logout');

        $body = json_decode($result->response()->getBody(), true);

        $this->assertSame('success', $body['status']);
        $this->assertSame('Logged out successfully', $body['message']);
    }

    // =========================================================================
    //  TEST 6 — authenticate() with EMPTY username + EMPTY password
    // =========================================================================
    //  SCENARIO : Form submitted completely blank
    //  EXPECTED : {"status":"error","message":"User not found"}
    //
    public function testAuthenticateWithEmptyCredentialsReturnsError(): void
    {
        // user_login('') → no user has an empty username → return []
        $this->mockCoreModel
            ->expects($this->once())
            ->method('user_login')
            ->willReturn([]);

        $this->withUri('http://localhost/login/authenticate');
        $this->controller(Login::class);

        $this->injectMock();
        $this->setPostData([
            'username' => '',
            'password' => '',
        ]);

        $result = $this->execute('authenticate');

        $body = json_decode($result->response()->getBody(), true);

        $this->assertSame('error', $body['status']);
        $this->assertSame('User not found', $body['message']);
    }
}
