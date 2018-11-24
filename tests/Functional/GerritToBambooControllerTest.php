<?php
declare(strict_types = 1);
namespace App\Tests\Functional;

use App\Client\BambooClient;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;

class GerritToBambooControllerTest extends TestCase
{
    /**
     * @test
     */
    public function bambooBuildIsTriggered()
    {
        $kernel = new \App\Kernel('test', true);
        $kernel->boot();
        $container = $kernel->getContainer();
        $bambooClient = $this->prophesize(BambooClient::class);
        $bambooClient->post(
            file_get_contents(__DIR__ . '/Fixtures/GerritToBambooGoodBambooPostUrl.txt'),
            require(__DIR__ . '/Fixtures/GerritToBambooGoodBambooPostData.php')
        )->shouldBeCalled()
        ->willReturn(new Response());
        $container->set('App\Client\BambooClient', $bambooClient->reveal());

        $request = Request::create('/gerrit', 'POST', require(__DIR__ . '/Fixtures/GerritToBambooGoodIncoming.php'));
        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);
    }

    /**
     * @test
     */
    public function bambooBuildIsNotTriggeredWithWrongBranch()
    {
        $kernel = new \App\Kernel('test', true);
        $kernel->boot();
        $container = $kernel->getContainer();
        $bambooClient = $this->prophesize(BambooClient::class);
        $bambooClient->post(Argument::cetera())->shouldNotBeCalled();
        $container->set('App\Client\BambooClient', $bambooClient->reveal());
        $request = Request::create('/gerrit', 'POST', require(__DIR__ . '/Fixtures/GerritToBambooBadIncoming.php'));
        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);
    }
}
