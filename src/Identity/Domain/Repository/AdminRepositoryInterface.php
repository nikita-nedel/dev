<?php

declare(strict_types=1);

namespace App\Identity\Domain\Repository;

use App\Identity\Domain\Model\Admin;

interface AdminRepositoryInterface
{
    public function add(Admin $admin): void;
}
