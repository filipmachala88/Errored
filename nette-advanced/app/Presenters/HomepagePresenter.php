<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use App\Components\carousel\CarouselControl;

final class HomepagePresenter extends Nette\Application\UI\Presenter
{

    protected function createComponentCarousel()
    {
        $control = new CarouselControl();

        return $control;
    }

    public function renderDefault()
    {
        $this->getComponent("carousel");
    }
}
