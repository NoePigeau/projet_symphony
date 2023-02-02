<?php
	namespace App\Doctrine\Extension;
	
	use Doctrine\ORM\Query\AST\Functions\FunctionNode;
	use Doctrine\ORM\Query\Lexer;
	use Doctrine\ORM\Query\QueryException;
	use Doctrine\ORM\Query\SqlWalker;
	
	class JSONGetTextFunction extends FunctionNode
	{
		public $jsonExpression = null;
		public $pathExpression = null;
		
		/**
		 * @throws QueryException
		 */
		public function parse(\Doctrine\ORM\Query\Parser $parser)
		{
			$parser->match(Lexer::T_IDENTIFIER);
			$parser->match(Lexer::T_OPEN_PARENTHESIS);
			$this->jsonExpression = $parser->ArithmeticPrimary();
			$parser->match(Lexer::T_COMMA);
			$this->pathExpression = $parser->ArithmeticPrimary();
			$parser->match(Lexer::T_CLOSE_PARENTHESIS);
		}
		
		public function getSql(SqlWalker $sqlWalker): string
		{
			return 'JSON_GET_TEXT(' .
				$this->jsonExpression->dispatch($sqlWalker) . ', ' .
				$this->pathExpression->dispatch($sqlWalker) .
				')';
		}
	}