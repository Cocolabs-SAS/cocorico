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

class GeoDistanceFunction extends FunctionNode
{
    /**
     * @var \Doctrine\ORM\Query\AST\ComparisonExpression
     */
    private $latitude;
    /**
     * @var \Doctrine\ORM\Query\AST\ComparisonExpression
     */
    private $longitude;

    /**
     * Parse DQL Function
     *
     * @param Parser $parser
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->latitude = $parser->ComparisonExpression();
        $parser->match(Lexer::T_COMMA);
        $this->longitude = $parser->ComparisonExpression();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * Get SQL to compute distance between two points in km
     *
     * @param SqlWalker $sqlWalker
     * @return string
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        $connection = $sqlWalker->getConnection();

        $_piBy180 = 0.0174532925; //PI() / 180
        $_180byPi = 57.2957795131; //PI() / 180

        if ($connection->getDriver()->getName() != 'pdo_sqlite') {
            return sprintf(
                '((ACOS(SIN(%s * ' . $_piBy180 . ') * SIN(%s * ' . $_piBy180 . ' ) + COS(%s * ' . $_piBy180 . ') * COS(%s * ' . $_piBy180 . ') * COS((%s - %s) * ' . $_piBy180 . ')) * ' . $_180byPi . ') * %s)',
                $this->latitude->rightExpression->dispatch($sqlWalker),
                $this->latitude->leftExpression->dispatch($sqlWalker),
                $this->latitude->rightExpression->dispatch($sqlWalker),
                $this->latitude->leftExpression->dispatch($sqlWalker),
                $this->longitude->rightExpression->dispatch($sqlWalker),
                $this->longitude->leftExpression->dispatch($sqlWalker),
                '111.18957696' //60 * 1.1515 * 1.609344
            );
        } else {//Disable distance for sqlite because missing trigonometric functions. todo: Add trigonometric functions to sqlite
            return sprintf(
                '(%s - %s)',
                $this->latitude->rightExpression->dispatch($sqlWalker),
                $this->longitude->rightExpression->dispatch($sqlWalker)
            );
        }

    }
}