<?php

/*
 * This file is part of the package t3g/intercept.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Symfony\Component\HttpFoundation\Request;

return Request::create(
    '/rstissuecreate',
    'POST',
    [],
    [],
    [],
    [],
    '{
      "ref": "refs/heads/main",
      "before": "253c42fe1e2e050539a21973b819216d7260b1a2",
      "after": "1b93464c68d398351410d871826e30066bfdbb2f",
      "created": false,
      "deleted": false,
      "forced": false,
      "base_ref": null,
      "compare": "https://github.com/TYPO3/TYPO3.CMS/compare/253c42fe1e2e...1b93464c68d3",
      "commits": [
        {
          "id": "1b93464c68d398351410d871826e30066bfdbb2f",
          "tree_id": "614938af52c154d0f76f964d797b8da7c6f9d3a0",
          "distinct": true,
          "message": "[BUGFIX] Load AdditionalFactoryConfiguration.php again\n\nThis file is placed in \"typo3conf\" just like the other configuration\nfiles and must be loaded accordingly.\n\nResolves: #87035\nRelates: #85560\nReleases: main\nChange-Id: I7db72a3c1b29f79fb242f1e5da21ec7d77614bfe\nReviewed-on: https://review.typo3.org/58977\nTested-by: TYPO3com <no-reply@typo3.com>\nReviewed-by: Andreas Wolf <andreas.wolf@typo3.org>\nReviewed-by: Benni Mack <benni@typo3.org>\nTested-by: Benni Mack <benni@typo3.org>\nReviewed-by: Anja Leichsenring <aleichsenring@ab-softlab.de>\nTested-by: Anja Leichsenring <aleichsenring@ab-softlab.de>",
          "timestamp": "2018-11-29T22:59:07+01:00",
          "url": "https://github.com/TYPO3/TYPO3.CMS/commit/1b93464c68d398351410d871826e30066bfdbb2f",
          "author": {
            "name": "Mathias Brodala",
            "email": "mbrodala@pagemachine.de",
            "username": "mbrodala"
          },
          "committer": {
            "name": "Anja Leichsenring",
            "email": "aleichsenring@ab-softlab.de",
            "username": "maddy2101"
          },
          "added": [
            "typo3/sysext/core/Documentation/Changelog/12.0/Feature-97326-OpenBackendPageFromAdminPanel.rst"
          ],
          "removed": [
            "typo3/sysext/core/Documentation/Changelog/12.0/Feature-97326-OpenBackendPageFromAdminPanel.rst"
          ],
          "modified": [
            "typo3/sysext/core/Classes/Configuration/ConfigurationManager.php",
            "typo3/sysext/core/Documentation/Changelog/12.0/Feature-97326-OpenBackendPageFromAdminPanel.rst"
          ]
        }
      ],
      "head_commit": {
        "id": "1b93464c68d398351410d871826e30066bfdbb2f",
        "tree_id": "614938af52c154d0f76f964d797b8da7c6f9d3a0",
        "distinct": true,
        "message": "[BUGFIX] Load AdditionalFactoryConfiguration.php again\n\nThis file is placed in \"typo3conf\" just like the other configuration\nfiles and must be loaded accordingly.\n\nResolves: #87035\nRelates: #85560\nReleases: main\nChange-Id: I7db72a3c1b29f79fb242f1e5da21ec7d77614bfe\nReviewed-on: https://review.typo3.org/58977\nTested-by: TYPO3com <no-reply@typo3.com>\nReviewed-by: Andreas Wolf <andreas.wolf@typo3.org>\nReviewed-by: Benni Mack <benni@typo3.org>\nTested-by: Benni Mack <benni@typo3.org>\nReviewed-by: Anja Leichsenring <aleichsenring@ab-softlab.de>\nTested-by: Anja Leichsenring <aleichsenring@ab-softlab.de>",
        "timestamp": "2018-11-29T22:59:07+01:00",
        "url": "https://github.com/TYPO3/TYPO3.CMS/commit/1b93464c68d398351410d871826e30066bfdbb2f",
        "author": {
          "name": "Mathias Brodala",
          "email": "mbrodala@pagemachine.de",
          "username": "mbrodala"
        },
        "committer": {
          "name": "Andreas Leichsenring",
          "email": "aleichsenring@ab-softlab.de",
          "username": "maddy2101"
        },
       "added": [
            "typo3/sysext/core/Documentation/Changelog/12.0/Feature-97326-OpenBackendPageFromAdminPanel.rst"
      ],
          "removed": [
            "typo3/sysext/core/Documentation/Changelog/12.0/Feature-97326-OpenBackendPageFromAdminPanel.rst"
      ],
        "modified": [
          "typo3/sysext/core/Classes/Configuration/ConfigurationManager.php",
          "typo3/sysext/core/Documentation/Changelog/12.0/Feature-97326-OpenBackendPageFromAdminPanel.rst"
        ]
      },
      "repository": {
        "id": 1430051,
        "node_id": "MDEwOlJlcG9zaXRvcnkxNDMwMDUx",
        "name": "TYPO3.CMS",
        "full_name": "TYPO3/TYPO3.CMS",
        "private": false,
        "owner": {
          "name": "TYPO3",
          "email": "",
          "login": "TYPO3",
          "id": 88698,
          "node_id": "MDEyOk9yZ2FuaXphdGlvbjg4Njk4",
          "avatar_url": "https://avatars0.githubusercontent.com/u/88698?v=4",
          "gravatar_id": "",
          "url": "https://api.github.com/users/TYPO3",
          "html_url": "https://github.com/TYPO3",
          "followers_url": "https://api.github.com/users/TYPO3/followers",
          "following_url": "https://api.github.com/users/TYPO3/following{/other_user}",
          "gists_url": "https://api.github.com/users/TYPO3/gists{/gist_id}",
          "starred_url": "https://api.github.com/users/TYPO3/starred{/owner}{/repo}",
          "subscriptions_url": "https://api.github.com/users/TYPO3/subscriptions",
          "organizations_url": "https://api.github.com/users/TYPO3/orgs",
          "repos_url": "https://api.github.com/users/TYPO3/repos",
          "events_url": "https://api.github.com/users/TYPO3/events{/privacy}",
          "received_events_url": "https://api.github.com/users/TYPO3/received_events",
          "type": "Organization",
          "site_admin": false
        },
        "html_url": "https://github.com/TYPO3/TYPO3.CMS",
        "description": "The TYPO3 Core - Enterprise Content Management System. Synchronized read-only mirror of http://git.typo3.org/Packages/TYPO3.CMS.git",
        "fork": false,
        "url": "https://github.com/TYPO3/TYPO3.CMS",
        "forks_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/forks",
        "keys_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/keys{/key_id}",
        "collaborators_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/collaborators{/collaborator}",
        "teams_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/teams",
        "hooks_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/hooks",
        "issue_events_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/issues/events{/number}",
        "events_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/events",
        "assignees_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/assignees{/user}",
        "branches_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/branches{/branch}",
        "tags_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/tags",
        "blobs_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/git/blobs{/sha}",
        "git_tags_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/git/tags{/sha}",
        "git_refs_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/git/refs{/sha}",
        "trees_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/git/trees{/sha}",
        "statuses_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/statuses/{sha}",
        "languages_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/languages",
        "stargazers_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/stargazers",
        "contributors_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/contributors",
        "subscribers_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/subscribers",
        "subscription_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/subscription",
        "commits_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/commits{/sha}",
        "git_commits_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/git/commits{/sha}",
        "comments_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/comments{/number}",
        "issue_comment_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/issues/comments{/number}",
        "contents_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/contents/{+path}",
        "compare_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/compare/{base}...{head}",
        "merges_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/merges",
        "archive_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/{archive_format}{/ref}",
        "downloads_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/downloads",
        "issues_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/issues{/number}",
        "pulls_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/pulls{/number}",
        "milestones_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/milestones{/number}",
        "notifications_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/notifications{?since,all,participating}",
        "labels_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/labels{/name}",
        "releases_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/releases{/id}",
        "deployments_url": "https://api.github.com/repos/TYPO3/TYPO3.CMS/deployments",
        "created_at": 1299060143,
        "updated_at": "2018-11-29T21:43:35Z",
        "pushed_at": 1543528771,
        "git_url": "git://github.com/TYPO3/TYPO3.CMS.git",
        "ssh_url": "git@github.com:TYPO3/TYPO3.CMS.git",
        "clone_url": "https://github.com/TYPO3/TYPO3.CMS.git",
        "svn_url": "https://github.com/TYPO3/TYPO3.CMS",
        "homepage": "https://typo3.org",
        "size": 377747,
        "stargazers_count": 586,
        "watchers_count": 586,
        "language": "PHP",
        "has_issues": false,
        "has_projects": true,
        "has_downloads": true,
        "has_wiki": false,
        "has_pages": false,
        "forks_count": 347,
        "mirror_url": null,
        "archived": false,
        "open_issues_count": 5,
        "license": {
          "key": "other",
          "name": "Other",
          "spdx_id": "NOASSERTION",
          "url": null,
          "node_id": "MDc6TGljZW5zZTA="
        },
        "forks": 347,
        "open_issues": 5,
        "watchers": 586,
        "default_branch": "main",
        "stargazers": 586,
        "master_branch": "main",
        "organization": "TYPO3"
      },
      "pusher": {
        "name": "reviewtypo3org",
        "email": "steffen.gebert+github-reviewtypo3org@typo3.org"
      },
      "organization": {
        "login": "TYPO3",
        "id": 88698,
        "node_id": "MDEyOk9yZ2FuaXphdGlvbjg4Njk4",
        "url": "https://api.github.com/orgs/TYPO3",
        "repos_url": "https://api.github.com/orgs/TYPO3/repos",
        "events_url": "https://api.github.com/orgs/TYPO3/events",
        "hooks_url": "https://api.github.com/orgs/TYPO3/hooks",
        "issues_url": "https://api.github.com/orgs/TYPO3/issues",
        "members_url": "https://api.github.com/orgs/TYPO3/members{/member}",
        "public_members_url": "https://api.github.com/orgs/TYPO3/public_members{/member}",
        "avatar_url": "https://avatars0.githubusercontent.com/u/88698?v=4",
        "description": "https://github.com/typo3-documentation  https://github.com/FriendsOfTYPO3"
      },
      "sender": {
        "login": "reviewtypo3org",
        "id": 5028725,
        "node_id": "MDQ6VXNlcjUwMjg3MjU=",
        "avatar_url": "https://avatars3.githubusercontent.com/u/5028725?v=4",
        "gravatar_id": "",
        "url": "https://api.github.com/users/reviewtypo3org",
        "html_url": "https://github.com/reviewtypo3org",
        "followers_url": "https://api.github.com/users/reviewtypo3org/followers",
        "following_url": "https://api.github.com/users/reviewtypo3org/following{/other_user}",
        "gists_url": "https://api.github.com/users/reviewtypo3org/gists{/gist_id}",
        "starred_url": "https://api.github.com/users/reviewtypo3org/starred{/owner}{/repo}",
        "subscriptions_url": "https://api.github.com/users/reviewtypo3org/subscriptions",
        "organizations_url": "https://api.github.com/users/reviewtypo3org/orgs",
        "repos_url": "https://api.github.com/users/reviewtypo3org/repos",
        "events_url": "https://api.github.com/users/reviewtypo3org/events{/privacy}",
        "received_events_url": "https://api.github.com/users/reviewtypo3org/received_events",
        "type": "User",
        "site_admin": false
      }
    }'
);
