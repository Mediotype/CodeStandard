<?php

/**
 * @author    Mediotype Development <diveinto@mediotype.com>
 * @copyright 2018 Mediotype. All Rights Reserved.
 */

namespace Mediotype\CodeStandard\PHP;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Enforces a consistent corporate file header.
 */
class FileHeaderSniff implements Sniff
{
    const ISSUE_INVALID_FORMAT = 'InvalidHeaderFormat';

    private $template = <<<EOF
<?php

/**
 * @author    Mediotype Development <diveinto@mediotype.com>
 * @copyright %d Mediotype. All Rights Reserved.
 */

EOF;

    /**
     * Register listener tokens.
     *
     * @return array
     */
    public function register()
    {
        return [T_DOC_COMMENT_OPEN_TAG];
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File
     * @param integer $stackPointer
     * @return integer|void
     */
    public function process(File $file, $stackPointer)
    {
        /** @var array $currentFileTokens */
        $currentFileTokens = $file->getTokens();
        /** @var File $standardFile */
        $standardFile = $this->createStandardFile($file);
        /** @var array $standardFileTokens */
        $standardFileTokens = array_slice($standardFile->getTokens(), $stackPointer);

        if ($stackPointer > count($standardFileTokens)) {
            return;
        }

        /** @var array $standardToken */
        foreach ($standardFileTokens as $standardToken) {
            /** @var array $currentToken */
            $currentToken = $currentFileTokens[$stackPointer++];

            if ($currentToken
                && $currentToken['type'] !== $standardToken['type']
                || $currentToken['content'] !== $standardToken['content']
            ) {
                $file->addErrorOnLine(
                    $this->getErrorMessage($currentToken, $standardToken),
                    $currentToken['line'],
                    self::ISSUE_INVALID_FORMAT
                );

                return;
            }
        }
    }

    /**
     * Generate the header standard as a file object.
     *
     * @param File $compareFile
     * @return File
     */
    private function createStandardFile(File $compareFile)
    {
        $file = new File('', $compareFile->ruleset, $compareFile->config);

        $file->setContent(
            sprintf($this->template, date('Y'))
        );

        $file->parse();

        return $file;
    }

    /**
     * Generate an error message from the given tokens.
     *
     * @param array $currentToken
     * @param array $standardToken
     * @return string
     */
    private function getErrorMessage(array $currentToken, array $standardToken)
    {
        if ($standardToken['content'] === PHP_EOL) {
            return sprintf('Invalid token, expected line-break.');
        }

        return sprintf('Invalid token, expected "%s".', $standardToken['content']);
    }
}
