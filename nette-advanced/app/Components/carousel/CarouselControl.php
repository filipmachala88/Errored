<?php


namespace App\Components\carousel;

use Nette\Application\UI;

class CarouselControl extends UI\Control
{
    public function render()
    {
        $this->template->setFile(__DIR__ . "/default.latte");
        $this->template->carousels = $this->db->table("carousel");
        $this->template->render();
    }
}