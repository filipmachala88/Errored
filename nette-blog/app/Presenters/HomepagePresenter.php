<?php

declare(strict_types=1);

namespace App\Presenters;

Use App\Model\PostManager;
use App\Model\RoleManager;
use Nette\Application\UI\Presenter;

final class HomepagePresenter extends Presenter
{
    public function __construct(
        private PostManager $postManager,
        private roleManager $roleManager,
    ) {
        bdump($roleManager->findByUserId(2)->fetchPairs('id', 'name'));
    }

    public function renderDefault()
    {
        $this->template->posts = $this->postManager->getPublicPosts(5);
    }
}
