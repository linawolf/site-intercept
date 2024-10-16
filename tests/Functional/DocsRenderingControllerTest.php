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
use App\Client\GeneralClient;
use App\Client\GithubClient;
use App\Client\SlackClient;
use App\Entity\DocumentationJar;
use App\Extractor\DeploymentInformation;
use App\Kernel;
use App\Service\GithubService;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Psr7\Response;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bridge\PhpUnit\ClockMock;

class DocsRenderingControllerTest extends AbstractFunctionalWebTestCase
{
    use ProphecyTrait;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        ClockMock::register(DeploymentInformation::class);
        ClockMock::register(GithubService::class);
        ClockMock::withClockMock(155309515.6937);
    }

    /**
     * @test
     */
    public function githubBuildIsNotTriggeredWithNewRepo(): void
    {
        $this->addRabbitManagementClientProphecy();
        $this->addRabbitManagementClientProphecy();
        $slackClient = $this->addSlackClientProphecy();
        $slackClient->post(Argument::cetera())->willReturn(new Response(200, [], ''));
        $generalClientProphecy = $this->prophesize(GeneralClient::class);
        $generalClientProphecy
            ->request('GET', 'https://raw.githubusercontent.com/TYPO3-Documentation/TYPO3CMS-Reference-CoreApi/latest/composer.json')
            ->shouldBeCalled()
            ->willReturn(new Response(200, [], file_get_contents(__DIR__ . '/Fixtures/DocsToBambooGoodRequestComposer.json')));
        TestDoubleBundle::addProphecy(GeneralClient::class, $generalClientProphecy);

        $githubClientProphecy = $this->prophesize(GithubClient::class);
        $githubClientProphecy->post(Argument::cetera())->shouldNotBeCalled();
        TestDoubleBundle::addProphecy(GithubClient::class, $githubClientProphecy);

        $kernel = new Kernel('test', true);
        $kernel->boot();
        DatabasePrimer::prime($kernel);

        $request = require __DIR__ . '/Fixtures/DocsToBambooGoodRequest.php';
        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);
    }

    /**
     * @test
     */
    public function githubBuildIsTriggered(): void
    {
        $this->addRabbitManagementClientProphecy();
        $this->addRabbitManagementClientProphecy();
        $generalClientProphecy = $this->prophesize(GeneralClient::class);
        $generalClientProphecy
            ->request('GET', 'https://raw.githubusercontent.com/TYPO3-Documentation/TYPO3CMS-Reference-CoreApi/latest/composer.json')
            ->shouldBeCalled()
            ->willReturn(new Response(200, [], file_get_contents(__DIR__ . '/Fixtures/DocsToBambooGoodRequestComposer.json')));
        TestDoubleBundle::addProphecy(GeneralClient::class, $generalClientProphecy);
        $slackClientProphecy = $this->prophesize(SlackClient::class);
        $slackClientProphecy->post(Argument::cetera())->shouldBeCalled()->willReturn(new Response(200, [], ''));
        TestDoubleBundle::addProphecy(SlackClient::class, $slackClientProphecy);
        $this->addSlackClientProphecy();

        $kernel = new Kernel('test', true);
        $kernel->boot();
        DatabasePrimer::prime($kernel);

        $request = require __DIR__ . '/Fixtures/DocsToBambooGoodRequest.php';
        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $jar = $this->entityManager
            ->getRepository(DocumentationJar::class)
            ->findOneBy(['packageName' => 'johndoe/make-good']);
        $jar->setApproved(true);
        $this->entityManager->persist($jar);
        $this->entityManager->flush();

        $generalClientProphecy = $this->prophesize(GeneralClient::class);
        $generalClientProphecy
            ->request('GET', 'https://raw.githubusercontent.com/TYPO3-Documentation/TYPO3CMS-Reference-CoreApi/latest/composer.json')
            ->shouldBeCalled()
            ->willReturn(new Response(200, [], file_get_contents(__DIR__ . '/Fixtures/DocsToBambooGoodRequestComposer.json')));
        TestDoubleBundle::addProphecy(GeneralClient::class, $generalClientProphecy);
        $githubClientProphecy = $this->prophesize(GithubClient::class);
        $githubClientProphecy
            ->post(
                '/repos/TYPO3-Documentation/t3docs-ci-deploy/dispatches',
                Argument::any()
            )->shouldBeCalled()
            ->willReturn(new Response());
        TestDoubleBundle::addProphecy(GithubClient::class, $githubClientProphecy);

        $kernel = new Kernel('test', true);
        $kernel->boot();

        $request = require __DIR__ . '/Fixtures/DocsToBambooGoodRequest.php';
        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);
    }

    /**
     * @test
     */
    public function githubBuildForMultipleBranchesIsTriggered(): void
    {
        $this->addRabbitManagementClientProphecy();
        $this->addRabbitManagementClientProphecy();
        $generalClientProphecy = $this->prophesize(GeneralClient::class);
        $generalClientProphecy
            ->request('GET', 'https://bitbucket.org/pathfindermediagroup/eso-export-addon/raw/main/composer.json')
            ->shouldBeCalled()
            ->willReturn(new Response(200, [], file_get_contents(__DIR__ . '/Fixtures/DocsToBambooGoodMultiBranchRequestComposer.json')));
        TestDoubleBundle::addProphecy(GeneralClient::class, $generalClientProphecy);
        $generalClientProphecy
            ->request('GET', 'https://bitbucket.org/pathfindermediagroup/eso-export-addon/raw/v1.1/composer.json')
            ->shouldBeCalled()
            ->willReturn(new Response(200, [], file_get_contents(__DIR__ . '/Fixtures/DocsToBambooGoodMultiBranchRequestComposer.json')));
        TestDoubleBundle::addProphecy(GeneralClient::class, $generalClientProphecy);
        $slackClientProphecy = $this->prophesize(SlackClient::class);
        $slackClientProphecy->post(Argument::cetera())->shouldBeCalled()->willReturn(new Response(200, [], ''));
        TestDoubleBundle::addProphecy(SlackClient::class, $slackClientProphecy);
        $this->addSlackClientProphecy();

        $kernel = new Kernel('test', true);
        $kernel->boot();
        DatabasePrimer::prime($kernel);

        $request = require __DIR__ . '/Fixtures/DocsToBambooGoodRequestMultiBranch.php';
        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $jars = $this->entityManager
            ->getRepository(DocumentationJar::class)
            ->findBy(['packageName' => 'bla/yay']);
        foreach ($jars as $jar) {
            $jar->setApproved(true);
            $this->entityManager->persist($jar);
        }
        $this->entityManager->flush();

        $githubClientProphecy = $this->prophesize(GithubClient::class);
        $githubClientProphecy
            ->post(
                '/repos/TYPO3-Documentation/t3docs-ci-deploy/dispatches',
                Argument::any()
            )->shouldBeCalled()
            ->willReturn(new Response());
        TestDoubleBundle::addProphecy(GithubClient::class, $githubClientProphecy);
        $githubClientProphecy
            ->post(
                '/repos/TYPO3-Documentation/t3docs-ci-deploy/dispatches',
                Argument::any()
            )->shouldBeCalled()
            ->willReturn(new Response());
        TestDoubleBundle::addProphecy(GithubClient::class, $githubClientProphecy);

        $kernel = new Kernel('test', true);
        $kernel->boot();

        $request = require __DIR__ . '/Fixtures/DocsToBambooGoodRequestMultiBranch.php';
        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);
    }

    /**
     * @test
     */
    public function githubBuildIsNotTriggered(): void
    {
        $this->addGeneralClientProphecy();
        $this->addRabbitManagementClientProphecy();
        $slackClient = $this->addSlackClientProphecy();
        $slackClient->post(Argument::cetera())->willReturn(new Response(200, [], ''));
        $githubClientProphecy = $this->prophesize(GithubClient::class);
        $githubClientProphecy->post(Argument::cetera())->shouldNotBeCalled();
        TestDoubleBundle::addProphecy(GithubClient::class, $githubClientProphecy);

        $kernel = new Kernel('test', true);
        $kernel->boot();
        $request = require __DIR__ . '/Fixtures/DocsToBambooBadRequest.php';
        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);
    }

    /**
     * @test
     */
    public function githubBuildIsNotTriggeredDueToMissingDependency(): void
    {
        $this->addRabbitManagementClientProphecy();
        $slackClient = $this->addSlackClientProphecy();
        $slackClient->post(Argument::cetera())->willReturn(new Response(200, [], ''));
        $generalClientProphecy = $this->prophesize(GeneralClient::class);
        $generalClientProphecy
            ->request('GET', 'https://raw.githubusercontent.com/TYPO3-Documentation/TYPO3CMS-Reference-CoreApi/latest/composer.json')
            ->shouldBeCalled()
            ->willReturn(new Response(200, [], file_get_contents(__DIR__ . '/Fixtures/DocsToBambooBadRequestComposerWithoutDependency.json')));
        TestDoubleBundle::addProphecy(GeneralClient::class, $generalClientProphecy);

        $kernel = new Kernel('test', true);
        $kernel->boot();
        DatabasePrimer::prime($kernel);

        $request = require __DIR__ . '/Fixtures/DocsToBambooGoodRequest.php';
        $response = $kernel->handle($request);
        $this->assertSame('Dependencies are not fulfilled. See https://intercept.typo3.com for more information.', $response->getContent());
        $this->assertSame(412, $response->getStatusCode());
        $kernel->terminate($request, $response);
    }

    /**
     * cms-core can not require cms-core in its composer.json
     *
     * @test
     */
    public function githubBuildIsTriggeredForPackageThatCanNotRequireItself(): void
    {
        $this->addRabbitManagementClientProphecy();
        $this->addRabbitManagementClientProphecy();
        $generalClientProphecy = $this->prophesize(GeneralClient::class);
        $generalClientProphecy
            ->request('GET', 'https://raw.githubusercontent.com/TYPO3-Documentation/TYPO3CMS-Reference-CoreApi/latest/composer.json')
            ->shouldBeCalled()
            ->willReturn(new Response(200, [], file_get_contents(__DIR__ . '/Fixtures/DocsToBambooGoodRequestComposerWithoutDependencyForSamePackage.json')));
        TestDoubleBundle::addProphecy(GeneralClient::class, $generalClientProphecy);
        $slackClientProphecy = $this->prophesize(SlackClient::class);
        $slackClientProphecy->post(Argument::cetera())->shouldBeCalled()->willReturn(new Response(200, [], ''));
        TestDoubleBundle::addProphecy(SlackClient::class, $slackClientProphecy);
        $this->addSlackClientProphecy();
        $kernel = new Kernel('test', true);
        $kernel->boot();
        DatabasePrimer::prime($kernel);

        $request = require __DIR__ . '/Fixtures/DocsToBambooGoodRequest.php';
        $response = $kernel->handle($request);
        $this->assertSame(204, $response->getStatusCode());
        $kernel->terminate($request, $response);

        $jar = $this->entityManager
            ->getRepository(DocumentationJar::class)
            ->findOneBy(['packageName' => 'typo3/cms-core']);
        $jar->setApproved(true);
        $this->entityManager->persist($jar);
        $this->entityManager->flush();

        $generalClientProphecy = $this->prophesize(GeneralClient::class);
        $generalClientProphecy
            ->request('GET', 'https://raw.githubusercontent.com/TYPO3-Documentation/TYPO3CMS-Reference-CoreApi/latest/composer.json')
            ->shouldBeCalled()
            ->willReturn(new Response(200, [], file_get_contents(__DIR__ . '/Fixtures/DocsToBambooGoodRequestComposerWithoutDependencyForSamePackage.json')));
        TestDoubleBundle::addProphecy(GeneralClient::class, $generalClientProphecy);

        $githubClientProphecy = $this->prophesize(GithubClient::class);
        $githubClientProphecy
            ->post(
                '/repos/TYPO3-Documentation/t3docs-ci-deploy/dispatches',
                Argument::any()
            )->shouldBeCalled()
            ->willReturn(new Response());
        TestDoubleBundle::addProphecy(GithubClient::class, $githubClientProphecy);

        $kernel = new Kernel('test', true);
        $kernel->boot();

        $request = require __DIR__ . '/Fixtures/DocsToBambooGoodRequest.php';
        $response = $kernel->handle($request);
        $this->assertSame(204, $response->getStatusCode());
        $kernel->terminate($request, $response);
    }

    /**
     * @test
     */
    public function githubBuildIsNotTriggeredDueToDeletedBranch(): void
    {
        $this->addGeneralClientProphecy();
        $this->addRabbitManagementClientProphecy();
        $slackClient = $this->addSlackClientProphecy();
        $slackClient->post(Argument::cetera())->willReturn(new Response(200, [], ''));
        $kernel = new Kernel('test', true);
        $kernel->boot();
        DatabasePrimer::prime($kernel);

        $request = require __DIR__ . '/Fixtures/DocsToBamboGithubDeletedBranchRequest.php';
        $response = $kernel->handle($request);
        $this->assertSame('The branch in this push event has been deleted.', $response->getContent());
        $this->assertSame(412, $response->getStatusCode());
        $kernel->terminate($request, $response);
    }

    /**
     * @test
     */
    public function githubPingIsHandled(): void
    {
        $this->addRabbitManagementClientProphecy();
        $slackClient = $this->addSlackClientProphecy();
        $slackClient->post(Argument::cetera())->willReturn(new Response(200, [], ''));
        $this->addGeneralClientProphecy();
        $kernel = new Kernel('test', true);
        $kernel->boot();
        DatabasePrimer::prime($kernel);

        $request = require __DIR__ . '/Fixtures/DocsToBambooGithubPingRequest.php';
        $response = $kernel->handle($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('github ping', $response->getContent());
        $kernel->terminate($request, $response);
    }
}
