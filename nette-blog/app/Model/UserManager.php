<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Table\ActiveRow;

class UserManager extends BaseManager
{
    public function getTableName(): string
    {
        return 'user';
    }

    public function getByEmail(string $email): ?ActiveRow
    {
        return $this->getAll()
            ->where('email', $email)
            ->fetch();
    }
}