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
use Doctrine\ORM\Query\AST\Literal;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Class CastFunction
 *
 * Adds the ability to use the MySQL CAST function inside Doctrine
 *
 * @package Cocorico\CoreBundle\DQL
 */
class CastFunction extends FunctionNode
{
    /**
     * Holds the field of the CAST DQL statement
     *
     * @var $castExpression
     */
    protected $castExpression;

    /**
     * Holds the type of the CAST DQL statement
     *
     * @var $type
     */
    protected $type;


    protected $supportedTypes = array(
        'bool',
        'boolean',
        'char',
        'date',
        'datetime',
        'decimal',
        'int',
        'integer',
        'json',
        'string',
        'text',
        'time',
    );

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
        $this->castExpression = $parser->ArithmeticExpression();
        $parser->match(Lexer::T_AS);
        $parser->match(Lexer::T_IDENTIFIER);
        $lexer = $parser->getLexer();
        $type = $lexer->token['value'];

        if ($lexer->isNextToken(Lexer::T_OPEN_PARENTHESIS)) {
            $parser->match(Lexer::T_OPEN_PARENTHESIS);
            /** @var Literal $parameter */
            $parameter = $parser->Literal();
            $parameters = array(
                $parameter->value
            );
            if ($lexer->isNextToken(Lexer::T_COMMA)) {
                while ($lexer->isNextToken(Lexer::T_COMMA)) {
                    $parser->match(Lexer::T_COMMA);
                    $parameter = $parser->Literal();
                    $parameters[] = $parameter->value;
                }
            }
            $parser->match(Lexer::T_CLOSE_PARENTHESIS);
            $type .= '(' . implode(', ', $parameters) . ')';
        }

        if (!$this->checkType($type)) {
            $parser->syntaxError(
                sprintf(
                    'Wrong type value. Valid types are: "%s"',
                    implode(', ', $this->supportedTypes)
                ),
                $lexer->token
            );
        }

        $this->type = $type;
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * Check type
     *
     * @param string $type
     * @return bool
     */
    protected function checkType($type)
    {
        $type = strtolower(trim($type));
        foreach ($this->supportedTypes as $supportedType) {
            if (strpos($type, $supportedType) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * getSql returns the sql
     *
     * @param  SqlWalker $sqlWalker
     * @return string
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        /** @var Node $value */
        $value = $this->castExpression;
        $type = $this->type;
        $type = strtolower($type);

        $isBoolean = $type === 'bool' || $type === 'boolean';

        if ($type === 'char') {
            $type = 'char(1)';
        } elseif ($type === 'string' || $type === 'text' || $type === 'json') {
            $type = 'char';
        } elseif ($type === 'int' || $type === 'integer' || $isBoolean) {
            $type = 'signed';
        }
        $expression = 'CAST(' . $this->getExpressionValue($value, $sqlWalker) . ' AS ' . $type . ')';

        if ($isBoolean) {
            $expression .= ' <> 0';
        }

        return $expression;
    }

    /**
     * Get expression value string.
     *
     * @param string|Node $expression
     * @param SqlWalker   $sqlWalker
     * @return string
     */
    protected function getExpressionValue($expression, SqlWalker $sqlWalker)
    {
        if ($expression instanceof Node) {
            $expression = $expression->dispatch($sqlWalker);
        }

        return $expression;
    }
}