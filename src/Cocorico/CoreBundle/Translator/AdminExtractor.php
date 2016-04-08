<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cocorico\CoreBundle\Translator;

use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Extractor\FileVisitorInterface;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;

class AdminExtractor implements FileVisitorInterface, NodeVisitor
{
    private $traverser;
    /** @var  MessageCatalogue */
    private $catalogue;
    private $file;

    public function __construct()
    {
        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor($this);
    }

    public function enterNode(Node $node)
    {
//        if (preg_match('/entity.*\./', $node->getDocComment())) {
//            return;
//        }
        if (!$node instanceof Node\Scalar\String_) {
            return;
        } else {
            $id = $node->value;
        }

        // ignore multiple dot without any string between them
        if (preg_match('/(\.\.|\.\.\.)/', $id)) {
            return;
        }

        // only extract dot-delimited string such as "admin.user.label"
        if (preg_match('/admin.*\./', $id)) { // || preg_match('/entity.*\./', $id)
            $domain = 'SonataAdminBundle';

            $message = new Message($id, $domain);
            $message->addSource(new FileSource((string)$this->file, $node->getLine()));

            $this->catalogue->add($message);
        }
    }

    public function visitPhpFile(\SplFileInfo $file, MessageCatalogue $catalogue, array $ast)
    {
        $this->file = $file;
        $this->catalogue = $catalogue;

        // only extract from path/directory 'Entity'
        if ($this->file->getPathInfo()->getFilename() == 'Admin') {
            $this->traverser->traverse($ast);
        }
    }

    public function beforeTraverse(array $nodes)
    {
    }

    public function leaveNode(Node $node)
    {
    }

    public function afterTraverse(array $nodes)
    {
    }

    public function visitFile(\SplFileInfo $file, MessageCatalogue $catalogue)
    {
    }

    public function visitTwigFile(\SplFileInfo $file, MessageCatalogue $catalogue, \Twig_Node $ast)
    {
    }
}
