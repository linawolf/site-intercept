<?php
declare(strict_types = 1);

/*
 * This file is part of the package t3g/intercept.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace App\Controller;

use App\Exception\DoNotCareException;
use App\Extractor\GithubPushEventForCore;
use App\Service\GithubService;
use App\Service\RabbitPublisherService;
use Exception;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for github rst files issue creation
 * https://github.com/typo3. Triggered by github hook on
 * https://github.com/TYPO3/typo3.
 */
class GithubRstIssueController extends AbstractController
{
    /**
     * Called by github post merge, this calls a script to update
     * the git sub tree repositories
     * @Route("/rstissuecreate", name="docs_github_rst_issue_create")
     *
     * @param Request $request
     * @param GithubService $githubService
     * @throws \JsonException
     * @return Response
     */
    public function index(Request $request, GithubService $githubService): Response
    {
        try {
            $pushEventInformation = new GithubPushEventForCore(json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR));
            $githubService->handleGithubIssuesForRstFiles($pushEventInformation);
        } catch (DoNotCareException $e) {
            // Hook payload could not be identified as hook that should trigger git split
        }

        return new Response();
    }
}
