<?php
declare(strict_types = 1);

/*
 * This file is part of the package t3g/intercept.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace App\Tests\Integration;

use App\Extractor\GithubPushEventForCore;
use App\Kernel;
use App\Service\CoreSplitService;
use App\Service\GithubService;
use PHPUnit\Framework\TestCase;

class GithubRstIssueServiceTest extends TestCase
{

    /**
     * @test
     */
    public function restTest()
    {
        $kernel = new Kernel('test', true);
        $kernel->boot();
        $container = $kernel->getContainer();
        $subject = $container->get(GithubService::class);

        // Split all patches for test-1 branch
        $message = new GithubPushEventForCore();
        $message->sourceBranch = 'test-1';
        $message->targetBranch = 'test-1';
        $message->jobUuid = 'my-uuid-1';
        $message->type = 'patch';
        $message->headCommitTitle = 'Andy this is an issue. guck.';
        $message->addedFiles = [
            'foo/bar/baz.rst'
        ];
        $message->modifiedFiles = [
            'foo/bar/BAMBAM.rst'
        ];
        $message->removedFiles = [
            'foo/bar/BAMBAM.rst'
        ];
        $subject->handleGithubIssuesForRstFiles($message);

        $kernel->shutdown();
        $this->assertTrue(true);
    }
}
