<?php

declare(strict_types = 1);

/*
 * This file is part of the package t3g/intercept.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace App\EventListener;

use App\Repository\HistoryEntryRepository;

class KernelTerminateListener
{
    private HistoryEntryRepository $repository;

    public function __construct(HistoryEntryRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(): void
    {
//        $this->repository->deleteOldEntries();
    }
}
