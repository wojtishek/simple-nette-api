<?php

declare(strict_types=1);

namespace Tests\Support;

use Codeception\Actor;
use Tests\Support\_generated\ApiTesterActions;

/**
 * Inherited Methods
 * @method void wantTo($text)
 * @method void wantToTest($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause($vars = [])
 *
 * @SuppressWarnings(PHPMD)
*/
class ApiTester extends Actor
{
    use ApiTesterActions;

    /**
     * Define custom actions here
     */
}
