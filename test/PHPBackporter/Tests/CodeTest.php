<?php

class PHPBackporter_Tests_CodeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideTestConversion
     */
    public function testConversion($originalCode, $expectedCode, $expectedOutput) {
        $factory = new PHPBackporter_Factory;

        $parser        = new PHPParser_Parser;
        $traverser     = $factory->getTraverser();
        $prettyPrinter = new PHPParser_PrettyPrinter_Zend;

        $stmts = $parser->parse(new PHPParser_Lexer('<?php ' . $originalCode));

        $traverser->traverse($stmts);

        $code = $prettyPrinter->prettyPrint($stmts);

        if (false === strpos($expectedCode, '%')) {
            $this->assertEquals($expectedCode, $code);
        } else {
            $this->assertStringMatchesFormat($expectedCode, $code);
        }

        ob_start();
        eval($code);
        $output = trim(ob_get_clean());

        if (false === strpos($expectedOutput, '%')) {
            $this->assertEquals($expectedOutput, $output);
        } else {
            $this->assertStringMatchesFormat($expectedOutput, $output);
        }
    }

    public function provideTestConversion() {
        $tests = array();

        foreach (
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(dirname(__FILE__) . '/../../code'),
                RecursiveIteratorIterator::LEAVES_ONLY
            ) as $file
        ) {
            foreach (explode('-----', file_get_contents($file)) as $test) {
                $tests[] = array_map('trim', explode('---', $test));
            }
        }

        return $tests;
    }
}