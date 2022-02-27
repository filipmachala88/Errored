<?php

declare(strict_types=1);

namespace App\Model;


use Nette\Database\Table\Selection;

class RoleManager extends BaseManager
{

    public function getTableName(): string
    {
        return 'role';
    }

    public function findByUserId(int $id): Selection
    {
        return $this->getAll()
            ->where(':user_x_role.user_id', $id);
    }

    public function findByUserIdToSelect(int $id): array
    {
        return $this->findByUserId($id)->fetchPairs('id', 'name');
    }
}