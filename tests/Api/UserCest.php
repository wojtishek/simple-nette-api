<?php

declare(strict_types=1);

namespace Api;

use App\Core\Enum\UserRole;
use Tests\Support\ApiTester;
use Nette\Security\Passwords;

final class UserCest
{

    public array $admin = [
        'email' => 'admin@example.com',
        'password' => '123456789',
        'name' => 'admin',
        'role' => 'admin',
    ];

    public array $author = [
        'email' => 'author@example.com',
        'password' => '123456789',
        'name' => 'author',
        'role' => 'author',
    ];

	public array $reader = [
		'email' => 'reader@example.com',
		'password' => '123456789',
		'name' => 'reader',
		'role' => 'reader',
	];

    public Passwords $passwords;

    public function restrictDeletionOfAdminByAuthor(ApiTester $I): void
    {
        $I->haveInDatabase('user', [
            'email' => $this->admin['email'],
            'password_hash' => $this->passwords->hash($this->admin['password']),
            'name' => $this->admin['name'],
            'role' => UserRole::ADMIN->value,
        ]);

	    $I->haveInDatabase('user', [
		    'email' => $this->author['email'],
		    'password_hash' => $this->passwords->hash($this->author['password']),
		    'name' => $this->author['name'],
		    'role' => UserRole::AUTHOR->value,
	    ]);
	    $authorId = $I->grabColumnFromDatabase('user', 'id', ['email' => $this->admin['email']]);

	    $I->sendPost('/auth/login', ['email' => $this->author['email'], 'password' => $this->author['password']]);
	    $I->seeResponseCodeIs(200);
	    $I->seeResponseIsJson();

	    $token = $I->grabDataFromResponseByJsonPath('$.data.accessToken');
	    $I->haveHttpHeader('Authorization', 'Bearer ' . $token[0]);
        $I->sendDelete('/users/' . $authorId[0]);
        $I->seeResponseCodeIs(403);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson([
			'code' => 403,
		]);
    }

	public function readerCannotCreateNewUser(ApiTester $I): void
	{
		$I->haveInDatabase('user', [
			'email' => $this->reader['email'],
			'password_hash' => $this->passwords->hash($this->reader['password']),
			'name' => $this->reader['name'],
			'role' => UserRole::READER->value,
		]);

		$I->sendPost('/auth/login', ['email' => $this->reader['email'], 'password' => $this->reader['password']]);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$token = $I->grabDataFromResponseByJsonPath('$.data.accessToken');
		$I->haveHttpHeader('Authorization', 'Bearer ' . $token[0]);
		$I->sendPost('/users', [
			'email' => $this->author['email'],
			'password' => $this->author['password'],
			'name' => $this->author['name'],
			'role' => UserRole::AUTHOR->value,
		]);
		$I->seeResponseCodeIs(403);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson([
			'code' => 403,
		]);
	}

    public function _before(ApiTester $I): void
    {
        $this->passwords = new Passwords();
        $I->haveHttpHeader('accept', 'application/json');
        $I->haveHttpHeader('content-type', 'application/json');
    }

}
