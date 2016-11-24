<?php
namespace AppBundle\Translation;

use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Extractor\FileVisitorInterface;

class BazingaJavascriptTranslationExtractor implements FileVisitorInterface
{
	public function visitFile(\SplFileInfo $file, MessageCatalogue $catalogue)
    {
    	if ('.js' !== substr($file, -3)) {
            return;
        }

        $content = file_get_contents($file);

        preg_match_all('/Translator\.trans(Choice)?\((["\'])([^+]+?)\2[,)]/', $content, $matches);
        foreach($matches[3] as $key) {
       		$message = new Message($key);
        	$message->addSource(new FileSource((string) $file));
        	$catalogue->add($message);
        }
    }

    public function visitPhpFile(\SplFileInfo $file, MessageCatalogue $catalogue, array $ast) { }
    public function visitTwigFile(\SplFileInfo $file, MessageCatalogue $catalogue, \Twig_Node $node) { }
}