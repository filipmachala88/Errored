<?php


namespace App\Components\carousel;

use Nette\Application\UI;
use Nette\Database\Explorer;

class CarouselControl extends UI\Control
{
    public function __construct(
        private Explorer $db,
    ) { }

    public function render()
    {
        $this->template->setFile(__DIR__ . "/default.latte");
        $this->template->carousels = $this->db->table("carousel");
        $this->template->render();
    }
}