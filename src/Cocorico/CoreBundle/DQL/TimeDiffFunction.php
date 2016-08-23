<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cocorico\CoreBundle\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Class TimeDiffFunction
 *
 * Adds the ability to use the MySQL TIMEDIFF function inside Doctrine
 *
 * @package Cocorico\CoreBundle\DQL
 */
class TimeDiffFunction extends FunctionNode
{

    /**
     * @var \Doctrine\ORM\Query\AST\SimpleArithmeticExpression
     */
    protected $firstDate = null;

    /**
     * @var \Doctrine\ORM\Query\AST\SimpleArithmeticExpression
     */
    protected $secondDate = null;

    /**
     * getSql returns the sql
     *
     * @param  SqlWalker $sqlWalker
     * @return String
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return 'TIMEDIFF('
        . $this->firstDate->dispatch($sqlWalker)
        . ', '
        . $this->secondDate->dispatch($sqlWalker)
        . ')';
    }

    /**
     * parse the provided parser and creates dql
     *
     * @param  Parser $parser
     * @return void
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->firstDate = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->secondDate = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}