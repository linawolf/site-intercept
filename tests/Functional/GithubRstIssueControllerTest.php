<?php
declare(strict_types = 1);

/*
 * This file is part of the package t3g/intercept.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace App\Tests\Functional;

use App\Bundle\TestDoubleBundle;
use App\Kernel;
use App\Service\CoreSplitService;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class GithubRstIssueControllerTest extends AbstractFunctionalWebTestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function githubIssueIsCreatedForRstChanges()
    {
        $kernel = new Kernel('test', true);
        $kernel->boot();
        $request = require __DIR__ . '/Fixtures/GithubRstIssuePatchRequest.php';
        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);
    }
}
