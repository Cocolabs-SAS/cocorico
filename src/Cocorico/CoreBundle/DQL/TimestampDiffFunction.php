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
 * Class TimestampDiff function
 *
 * Adds the ability to use the MySQL TIMESTAMPDIFF function inside Doctrine
 *
 * @package Cocorico\CoreBundle\DQL
 */
class TimestampDiffFunction extends FunctionNode
{
    /**
     * @var \Doctrine\ORM\Query\AST\SimpleArithmeticExpression
     */
    protected $unit = null;

    /**
     * @var \Doctrine\ORM\Query\AST\SimpleArithmeticExpression
     */
    protected $firstDate = null;
    /**
     * @var \Doctrine\ORM\Query\AST\SimpleArithmeticExpression
     */
    protected $secondDate = null;


    /**
     * List of supported units.
     *
     * @var array
     */
    protected $supportedUnits = array(
        'MICROSECOND',
        'SECOND',
        'MINUTE',
        'HOUR',
        'DAY',
        'WEEK',
        'MONTH',
        'QUARTER',
        'YEAR'
    );


    /**
     * {@inheritdoc}
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return 'TIMESTAMPDIFF('
        . $this->unit
        . ', '
        . $this->firstDate->dispatch($sqlWalker)
        . ', '
        . $this->secondDate->dispatch($sqlWalker)
        . ')';
    }


    /**
     * {@inheritdoc}
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $parser->match(Lexer::T_IDENTIFIER);

        $lexer = $parser->getLexer();
        $unit = strtoupper(trim($lexer->token['value']));
        if (!$this->checkUnit($unit)) {
            $parser->syntaxError(
                sprintf(
                    'Unit is not valid for TIMESTAMPDIFF function. Supported units are: "%s"',
                    implode(', ', $this->supportedUnits)
                ),
                $lexer->token
            );
        }

        $this->unit = $unit;
        $parser->match(Lexer::T_COMMA);
        $this->firstDate = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->secondDate = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * Check that unit is supported.
     *
     * @param string $unit
     * @return bool
     */
    protected function checkUnit($unit)
    {
        return in_array($unit, $this->supportedUnits);
    }
}