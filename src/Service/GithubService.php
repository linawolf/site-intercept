<?php
declare(strict_types = 1);

/*
 * This file is part of the package t3g/intercept.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace App\Service;

use App\Client\GeneralClient;
use App\Client\GithubClient;
use App\Creator\GithubPullRequestCloseComment;
use App\Exception\DoNotCareException;
use App\Extractor\BambooBuildTriggered;
use App\Extractor\DeploymentInformation;
use App\Extractor\GithubCorePullRequest;
use App\Extractor\GithubPullRequestIssue;
use App\Extractor\GithubPushEventForCore;
use App\Extractor\GithubUserData;
use App\Extractor\GitPatchFile;
use RuntimeException;

/**
 * Fetch various detail information from github
 */
class GithubService
{
    private GeneralClient $client;

    /**
     * @var string Absolute path pull request files are put to
     */
    private string $pullRequestPatchPath;

    /**
     * @var string Github access token
     */
    private $accessKey;
    private GithubClient $githubClient;

    /**
     * GithubService constructor.
     *
     * @param string $pullRequestPatchPath Absolute path pull request files are put to
     * @param GeneralClient $client General http client that does not need authentication
     */
    public function __construct(string $pullRequestPatchPath, GeneralClient $client, GithubClient $githubClient)
    {
        $this->pullRequestPatchPath = $pullRequestPatchPath;
        $this->client = $client;
        $this->accessKey = $_ENV['GITHUB_ACCESS_TOKEN'] ?? '';
        $this->githubClient = $githubClient;
    }

    /**
     * Get details of a new pull request issue on github.
     *
     * @param GithubCorePullRequest $pullRequest
     * @return GithubPullRequestIssue
     * @throws DoNotCareException
     */
    public function getIssueDetails(GithubCorePullRequest $pullRequest): GithubPullRequestIssue
    {
        return new GithubPullRequestIssue($this->client->get($pullRequest->issueUrl));
    }

    /**
     * Get details of a github user.
     *
     * @param GithubCorePullRequest $pullRequest
     * @return GithubUserData
     * @throws DoNotCareException
     */
    public function getUserDetails(GithubCorePullRequest $pullRequest): GithubUserData
    {
        return new GithubUserData($this->client->get($pullRequest->userUrl));
    }

    /**
     * Fetch the diff file from a github PR and store to disk
     *
     * @param GithubCorePullRequest $pullRequest
     * @return GitPatchFile
     */
    public function getLocalDiff(GithubCorePullRequest $pullRequest): GitPatchFile
    {
        $response = $this->client->get($pullRequest->diffUrl);
        $diff = (string)$response->getBody();
        if (!@mkdir($concurrentDirectory = $this->pullRequestPatchPath) && !is_dir($concurrentDirectory)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }
        $filePath = $this->pullRequestPatchPath . sha1($pullRequest->diffUrl);
        $patch = fopen($filePath, 'w+');
        fwrite($patch, $diff);
        fclose($patch);
        return new GitPatchFile($filePath);
    }

    /**
     * Close pull request, add a comment, lock pull request
     *
     * @param GithubCorePullRequest $pullRequest
     * @param GithubPullRequestCloseComment $closeComment
     */
    public function closePullRequest(
        GithubCorePullRequest $pullRequest,
        GithubPullRequestCloseComment $closeComment
    ): void {
        $client = $this->client;

        $url = $pullRequest->pullRequestUrl;
        $client->patch(
            $url,
            [
                'headers' => [
                    'Authorization' => 'token ' . $this->accessKey
                ],
                'json' => [
                    'state' => 'closed',
                ]
            ]
        );

        $url = $pullRequest->commentsUrl;
        $client->post(
            $url,
            [
                'headers' => [
                    'Authorization' => 'token ' . $this->accessKey
                ],
                'json' => [
                    'body' => $closeComment->comment,
                ],
            ]
        );

        $url = $pullRequest->issueUrl . '/lock';
        $client->put($url, [
            'headers' => [
                'Authorization' => 'token ' . $this->accessKey
            ],
        ]);
    }

    public function handleGithubIssuesForRstFiles(GithubPushEventForCore $pushEvent): void
    {
        $added = $this->filterRstChanges($pushEvent->addedFiles);
        $modified = $this->filterRstChanges($pushEvent->modifiedFiles);
        $removed = $this->filterRstChanges($pushEvent->removedFiles);
        if (count($added) + count($modified) + count($removed) === 0) {
            return;
        }
        $client = $this->client;
        $body = [];
        if ([] !== $added) {
            $body[] = 'Added:' . "\n";
            $added = array_map(function(string $file) {
                        return '[' . $file . '](https://github.com/TYPO3/typo3/tree/main/' . $file . ')';
            }, $added);
            $body[] = '* ' . implode("\n* ", $added);
            $body[] = "\n";
        }

        if ([] !== $modified) {
            $body[] = 'Modified:' . "\n";
            $modified = array_map(function (string $file) {
                return '[' . $file . '](https://github.com/TYPO3/typo3/tree/main/' . $file . ')';
            }, $modified);
            $body[] = '* ' . implode("\n* ", $modified);
            $body[] = "\n";
        }

        if ([] !== $removed) {
            $body[] = 'Removed:' . "\n";
            $removed = array_map(static function (string $file) {
                return '[' . $file . '](https://github.com/TYPO3/typo3/tree/main/' . $file . ')';
            }, $removed);
            $body[] = '* ' . implode("\n* ", $removed);
            $body[] = "\n";
        }
        $search = [
           'q' => 'label:'. $pushEvent->issueNumber .' is:issue repo:andreasfernandez/Changelog-To-Doc',
        ];
        $query = http_build_query($search);
        $response = $client->get('https://api.github.com/search/issues?' . $query, [
            'headers' => [
                'Authorization' => 'token ' . $this->accessKey
            ],
        ]);
        $contents = $response->getBody()->getContents();
        $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        $existingIssueCommentUrl = $decoded['items']['0']['comments_url'] ?? null;
        if (null !== $existingIssueCommentUrl) {
            $client->post(
                $existingIssueCommentUrl,
                [
                    'headers' => [
                        'Authorization' => 'token ' . $this->accessKey
                    ],
                    'json' => [
                        'body' => implode("\n", $body),
                    ],
                ]
            );
        } else {
            $client->post(
                'https://api.github.com/repos/andreasfernandez/Changelog-To-Doc/issues',
                [
                    'headers' => [
                        'Authorization' => 'token ' . $this->accessKey
                    ],
                    'json' => [
                        'title' => $pushEvent->headCommitTitle,
                        'body' => implode("\n", $body),
                        'labels' => [
                            (string)$pushEvent->issueNumber
                        ]
                    ]
                ]
            );
        }


    }

    /**
     * Triggers new build in project TYPO3-Documentation/t3docs-ci-deploy
     *
     * @param DeploymentInformation $deploymentInformation
     * @return BambooBuildTriggered
     */
    public function triggerDocumentationPlan(DeploymentInformation $deploymentInformation): BambooBuildTriggered
    {
        $id = sha1((string)(time()) . $deploymentInformation->packageName);
        $postBody = [
            'event_type' => 'render',
            'client_payload' => [
                'repository_url' => $deploymentInformation->repositoryUrl,
                'source_branch' => $deploymentInformation->sourceBranch,
                'target_branch_directory' => $deploymentInformation->targetBranchDirectory,
                'name' => $deploymentInformation->name,
                'vendor' => $deploymentInformation->vendor,
                'type_short' => $deploymentInformation->typeShort,
                'id' => $id
            ]
        ];
        $this->githubClient->post(
            '/repos/TYPO3-Documentation/t3docs-ci-deploy/dispatches',
            [
                'json' => $postBody
            ]
        );
        return new BambooBuildTriggered(json_encode(['buildResultKey' => $id]));
    }

    /**
     * Triggers new build in project TYPO3-Documentation/t3docs-ci-deploy for deletion
     *
     * @param DeploymentInformation $deploymentInformation
     * @return BambooBuildTriggered
     */
    public function triggerDocumentationDeletionPlan(DeploymentInformation $deploymentInformation): BambooBuildTriggered
    {
        $id = sha1((string)time() . $deploymentInformation->packageName);
        $postBody = [
            'event_type' => 'delete',
            'client_payload' => [
                'target_branch_directory' => $deploymentInformation->targetBranchDirectory,
                'name' => $deploymentInformation->name,
                'vendor' => $deploymentInformation->vendor,
                'type_short' => $deploymentInformation->typeShort,
                'id' => $id
            ]
        ];

        $this->githubClient->post(
            '/repos/TYPO3-Documentation/t3docs-ci-deploy/dispatches',
            [
                'json' => $postBody
            ]
        );
        return new BambooBuildTriggered(json_encode(['buildResultKey' => $id]));
    }

    /**
     * Trigger new build of project CORE-DRD
     *
     * @return BambooBuildTriggered
     */
    public function triggerDocumentationRedirectsPlan(): BambooBuildTriggered
    {
        $id = sha1((string)time());
        $postBody = [
            'event_type' => 'redirect',
            'client_payload' => [
                'id' => $id
            ]
        ];
        $this->githubClient->post(
            '/repos/TYPO3-Documentation/t3docs-ci-deploy/dispatches',
            [
                'json' => $postBody
            ]
        );
        return new BambooBuildTriggered(json_encode(['buildResultKey' => $id]));
    }

    /**
     * @param GithubPushEventForCore $pushEvent
     * @return void
     */
    private function filterRstChanges(array $files): array
    {
        return array_filter($files, static function (string $file) {
            return str_ends_with($file, '.rst');
        });
    }
}
