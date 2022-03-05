<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
Use Nette\Database\Explorer;

class PostPresenter extends \Nette\Application\UI\Presenter
{
    public function __construct(
        private Explorer $db,
    ) { }

    public function renderShow(int $postId): void
    {
        $this->template->post = $this->db
            ->table('post')
            ->get($postId);
    }
}