<?php
declare(strict_types = 1);

/*
 * This file is part of the package t3g/intercept.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace App\Extractor;

use App\Exception\DoNotCareException;
use App\Utility\BranchUtility;
use Ramsey\Uuid\Uuid;

/**
 * Extract information from a github push event hook
 * needed to trigger the core git split to single repos.
 *
 * Throws exceptions if not responsible.
 */
class GithubPushEventForCore
{
    private const TYPE_PATCH = 'patch';
    private const TYPE_TAG = 'tag';

    /**
     * @var string|null Used especially in logging as context.
     */
    public ?string $jobUuid = null;

    /**
     * @var string|null Either 'patch' if a merged patch should be split, or 'tag' to apply tags to sub reps
     */
    public ?string $type = null;

    /**
     * @var string|null The source branch to split FROM, eg. 'TYPO3_8-7', '9.2', 'main'
     */
    public ?string $sourceBranch = null;

    /**
     * @var string|null The target branch to split TO, eg. '8.7', '9.2', 'main'
     */
    public ?string $targetBranch = null;

    /**
     * @var string|null Set to the tag that has been pushed for type=tag
     */
    public ?string $tag = null;

    /**
     * @var string|null the full name of the repository
     */
    public ?string $repositoryFullName = null;

    /**
     * @var string[]
     */
    public array $addedFiles = [];

    /**
     * @var string[]
     */
    public array $modifiedFiles = [];

    /**
     * @var string[]
     */
    public array $removedFiles = [];

    public string $headCommitTitle = '';

    public int $issueNumber = 0;


    /**
     * Extract information.
     *
     * @param array $fullPullRequestInformation Optional, this object is used in consumer, via json serializer, too.
     * @throws DoNotCareException
     * @throws \Exception
     */
    public function __construct(array $fullPullRequestInformation = [])
    {
        if (!empty($fullPullRequestInformation)) {
            if (empty($fullPullRequestInformation['ref'])) {
                throw new DoNotCareException('ref is empty, it\'s not my job');
            }
            $this->repositoryFullName = $fullPullRequestInformation['repository']['full_name'] ?? '';
            if ($this->isPushedPatch($fullPullRequestInformation)) {
                $this->type = self::TYPE_PATCH;
                $this->sourceBranch = $this->getSourceBranch($fullPullRequestInformation['ref']);
                $this->targetBranch = BranchUtility::resolveCoreSplitBranch($this->sourceBranch);
                $this->addedFiles = $fullPullRequestInformation['head_commit']['added'] ?? [];
                $this->modifiedFiles = $fullPullRequestInformation['head_commit']['modified'] ?? [];
                $this->removedFiles = $fullPullRequestInformation['head_commit']['removed'] ?? [];
                $matches = [];
                preg_match('/Resolves:\s+?#(\d+)/', $fullPullRequestInformation['head_commit']['message'], $matches);
                $this->issueNumber = (int)($matches[1] ?? 0);
                $this->headCommitTitle = explode("\n", $fullPullRequestInformation['head_commit']['message'] ?? '', 2)[0] ?? '';
            } elseif ($this->isPushedTag($fullPullRequestInformation)) {
                $this->type = self::TYPE_TAG;
                $this->sourceBranch = $fullPullRequestInformation['repository']['master_branch'] ?? 'main';
                $this->tag = $this->getTag($fullPullRequestInformation['ref']);
            } else {
                throw new DoNotCareException('no pushed patch, no pushed tag, it\'s not my job');
            }
            $this->jobUuid = Uuid::uuid4()->toString();
        }
    }

    /**
     * If the 'ref' part of a github pull request starts with 'refs/heads/',
     * then this has been a merged patch that should be split.
     *
     * @param array $requestInformation
     * @return bool
     */
    private function isPushedPatch(array $requestInformation): bool
    {
        return str_starts_with($requestInformation['ref'], 'refs/heads/');
    }

    /**
     * If 'ref' starts with 'refs/tags/, and created is set and base_ref is not
     * empty, then this push event is an event for a new tag on git core main.
     *
     * @param array $requestInformation
     * @return bool
     */
    private function isPushedTag(array $requestInformation): bool
    {
        return str_starts_with($requestInformation['ref'], 'refs/tags/')
               && $requestInformation['created'] === true;
    }

    /**
     * Extract tag from a 'refs/tags/v9.5.1' a-like string
     *
     * @param string $ref
     * @return string
     * @throws DoNotCareException
     */
    private function getTag(string $ref): string
    {
        $tagFromRef = str_replace('refs/tags/', '', $ref);
        if (empty($tagFromRef)) {
            throw new DoNotCareException('no tag found in ref');
        }
        return $tagFromRef;
    }

    /**
     * Determine source branch from 'ref', eg. 'refs/heads/main' becomes 'main'
     *
     * @param string $ref
     * @return string
     * @throws DoNotCareException
     */
    private function getSourceBranch(string $ref): string
    {
        $sourceBranchFromRef = str_replace('refs/heads/', '', $ref);
        if (empty($sourceBranchFromRef)) {
            throw new DoNotCareException('not source branch found');
        }
        return BranchUtility::resolveCoreMonoRepoBranch($sourceBranchFromRef);
    }
}
