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
 * Class DateFormatFunction
 *
 * Adds the ability to use the MySQL DATE_FORMAT function inside Doctrine
 *
 * @package Cocorico\CoreBundle\DQL
 */
class DateFormatFunction extends FunctionNode
{

    /**
     * Holds the timestamp of the DATE_FORMAT DQL statement
     *
     * @var $dateExpression
     */
    protected $dateExpression;

    /**
     * Holds the '% format' parameter of the DATE_FORMAT DQL statement
     * var String
     */
    protected $formatChar;

    /**
     * getSql returns the sql
     *
     * @param  SqlWalker $sqlWalker
     * @return String
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return 'DATE_FORMAT (' . $sqlWalker->walkArithmeticExpression(
            $this->dateExpression
        ) . ',' . $sqlWalker->walkStringPrimary($this->formatChar) . ')';
    }

    /**
     * parse the provided parser and creates dql
     *
     * @param  Parser $parser
     * @return void
     */
    public function parse(Parser $parser)
    {
        $parser->Match(Lexer::T_IDENTIFIER);
        $parser->Match(Lexer::T_OPEN_PARENTHESIS);

        $this->dateExpression = $parser->ArithmeticExpression();
        $parser->Match(Lexer::T_COMMA);

        $this->formatChar = $parser->ArithmeticExpression();

        $parser->Match(Lexer::T_CLOSE_PARENTHESIS);
    }
}