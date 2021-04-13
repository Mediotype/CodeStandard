<?php
/**
 * @author    Mediotype Development <diveinto@mediotype.com>
 * @copyright 2018 Mediotype. All Rights Reserved.
 */

namespace Mediotype\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Enforces a consistent corporate file header.
 */
class FileHeaderSniff implements Sniff
{
    const ISSUE_INVALID_FORMAT = 'InvalidHeaderFormat';
    const YEAR_PLACEHOLDER = 'YYYY';

    /**
     * If TRUE, the year specified in the copyright header MUST be the current year
     *
     * @var bool
     */
    public $forceCurrentYear = false;

    /**
     * The copyright owner of the code being checked.  Defaults to "Blue Acorn iCi"
     *
     * @var string
     */
    public $codeOwner = 'Blue Acorn iCi';

    private $template = <<<EOF
<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright YYYY OWNER. All Rights Reserved.
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
        $template = str_replace('OWNER', $this->codeOwner, $this->template);
        if ($this->forceCurrentYear) {
            $template = str_replace('YYYY', date('Y'), $template);
        }

        /** @var array $currentFileTokens */
        $currentFileTokens = $file->getTokens();
        /** @var File $standardFile */
        $standardFile = $this->createStandardFile($file, $template);
        /** @var array $standardFileTokens */
        $standardFileTokens = array_slice($standardFile->getTokens(), $stackPointer);

        if ($stackPointer > count($standardFileTokens)) {
            return;
        }

        /** @var array $standardToken */
        foreach ($standardFileTokens as $standardToken) {
            /** @var array $currentToken */
            $currentToken = $currentFileTokens[$stackPointer++];

            // Replace year with placeholder
            if (!$this->forceCurrentYear && \strpos($standardToken['content'], self::YEAR_PLACEHOLDER) !== false) {
                $currentToken['content'] = \preg_replace(
                    '/\d{4}/',
                    self::YEAR_PLACEHOLDER,
                    $currentToken['content']
                );
            }

            if ($currentToken
                && (
                    $currentToken['type'] !== $standardToken['type']
                    || $currentToken['content'] !== $standardToken['content']
                )
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
    private function createStandardFile(File $compareFile, $template)
    {
        $file = new File('', $compareFile->ruleset, $compareFile->config);
        $file->setContent($template);
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
            return sprintf('Invalid token, unexpected line-break.');
        }

        return sprintf('Invalid token, expected "%s".', $standardToken['content']);
    }
}
