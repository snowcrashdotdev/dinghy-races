<?php

namespace App\DQL;

use Doctrine\ORM\Query\Lexer;

class StandardDeviationFunction  extends \Doctrine\ORM\Query\AST\Functions\FunctionNode
{
    public $valueExpression = null;

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER); 
        $parser->match(Lexer::T_OPEN_PARENTHESIS); 
        $this->valueExpression = $parser->StringExpression(); 
        $parser->match(Lexer::T_CLOSE_PARENTHESIS); 
    }

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return '(STDDEV(' . $this->valueExpression->dispatch($sqlWalker) . '))'; 
    }
}