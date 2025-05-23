<?php

declare(strict_types=1);

namespace Tests\Api;

use App\Core\Enum\UserRole;
use Tests\Support\ApiTester;
use Nette\Security\Passwords;

final class ArticleCest
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

    public function createArticleByAdmin(ApiTester $I): void
    {
        $I->haveInDatabase('user', [
            'email' => $this->admin['email'],
            'password_hash' => $this->passwords->hash($this->admin['password']),
            'name' => $this->admin['name'],
            'role' => UserRole::ADMIN->value,
        ]);

        $I->sendPost('/auth/login', ['email' => $this->admin['email'], 'password' => $this->admin['password']]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $token = $I->grabDataFromResponseByJsonPath('$.data.accessToken');

        $I->haveHttpHeader('Authorization', 'Bearer ' . $token[0]);
        $I->sendPost('/articles', [
            'title' => 'Test article',
            'content' => 'Test content',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'title' => 'Test article',
        ]);
        $articleId = $I->grabDataFromResponseByJsonPath('$.data.request.id');
        $I->seeinDatabase('article', ['id' => $articleId[0]]);

    }

    public function restrictCreationOfArticleByReader(ApiTester $I): void
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
        $I->sendPost('/articles', [
            'title' => 'Test article',
            'content' => 'Test content',
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
