<?php

namespace AppBundle\Translation;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\ExtractorInterface;

class ExtraTranslationExtractor implements ExtractorInterface
{
	public function extract()
    {
    	$catalogue = new MessageCatalogue();

    	foreach(\AppBundle\Helper\Constants::KEYWORDS as $keyword)
    	{
    		$message = new Message("keyword.$keyword.name", "messages");
    		$message->setDesc("Name of the keyword '$keyword'");
    		$catalogue->add($message);

    		$message = new Message("keyword.$keyword.title", "messages");
    		$message->setDesc("Description of keyword '$keyword' to be shown when mouse is over it");
    		$catalogue->add($message);
    	}

    	foreach(\AppBundle\Helper\Constants::ICONS as $icon)
    	{
    		$message = new Message("icon.$icon", "messages");
    		$message->setDesc("Text to be shown when mouse is over the icon [$icon]");
    		$catalogue->add($message);
    	}

        foreach(\AppBundle\Helper\Constants::PROBLEMS as $problem)
        {
            $message = new Message("decks.problems.$problem", "messages");
            $message->setDesc("Description of this deck problem");
            $catalogue->add($message);
        }

    	return $catalogue;
    }
}