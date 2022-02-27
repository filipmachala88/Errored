<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Explorer;
use Nette\Database\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\SmartObject;

class CommentManager extends BaseManager
{
    public function getTableName(): string
    {
        return 'comment';
    }
    public function getCommentsByPostId(int $postId, int $limit = null): Selection
    {
        return $this->getAll()
            ->where('post_id', $postId)
            ->order('created_at')
            ->limit($limit);
    }
}
