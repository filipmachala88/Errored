<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\SmartObject;
use Nette\Utils\DateTime;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\TemplateFactory;

class PostManager extends BaseManager
{
    use SmartObject;
    
    public function __construct(
        Explorer $db,
        private TemplateFactory $templateFactory,
        private LinkGenerator $linkGenerator,
    ) { 
        parent::__construct($db);
    }
    public function getTableName(): string
    {
        return 'post';
    }

    public function insert(array $values): ActiveRow
    {
        $retVal = parent::insert($values);

        if (Debugger::$productionMode){
            $latte = $thiss->templateFactory->createTemplate();
            $latte->getLatte()->addProvider('uiControl', $this->linkGenerator);
            // Mail send START
            $message = new Message();
            $message->setFrom('odesilatel@gmail.com');
            $message->addTo('prijemce1@gmail.com');
            $message->addTo('prijemce2@gmail.com');

            $message->setHtmlBody($latte->renderToString(__DIR__ . '/addPostMail.latte', $retVal->toArray()));

            $sender = new SendmailMailer();
            $sender->send($message);
        }

        // Mail send END
        return $retVal;
    }

    public function getPublicPosts(int $limit = null): Selection
    {
        $retVal = $this->getAll()
            ->where('created_at < ', new DateTime)
            ->order('created_at DESC');
        
        if ($limit){
            $retVal->limit($limit);
        }

        return $retVal;
    }
}
