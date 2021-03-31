<?php
/**
 * @author    Mediotype Development <diveinto@mediotype.com>
 * @copyright 2018 Mediotype. All Rights Reserved.
 */

namespace Mediotype\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

class InterceptorSeeAnnotationSniff implements Sniff
{
    const ISSUE_MISSING_CLASS_ANNOTATION = 'MissingOnClass';
    const ISSUE_MISSING_METHOD_ANNOTATION = 'MissingOnMethod';

    const MESSAGE_MISSING_CLASS_ANNOTATION = 'Documentation for an interceptor\'s class must contain a @see annotation';
    const MESSAGE_MISSING_METHOD_ANNOTATION = 'Documentation for an interceptor\'s method must contain a @see annotation';

    /**
     * @inheritdoc
     */
    public function process(File $file, $stackPtr)
    {
        $name = $file->getDeclarationName($stackPtr);

        $fqn = $this->assembleFqn($file, $name);
        $fqnParts = explode('\\', $fqn);

        // Reasons we shouldn't continue
        if (count($fqnParts) < 3 // Doesn't match a Magento-style structure
            || ($fqnParts[2] !== 'Model' && $fqnParts[2] !== 'Plugin') // Is not Model\ or Plugin\
            || ($fqnParts[2] === 'Model' && $fqnParts[3] !== 'Plugin') // Is not Model\Plugin
        ) {
            return;
        }

        $this->validateInterceptorAtClassLevel($file, $stackPtr);
        $methodPtr = $stackPtr;
        while ($methodPtr = $file->findNext(T_FUNCTION, $methodPtr + 1)) {
            $this->validateInterceptorAtMethodLevel($file, $methodPtr);
        }
    }

    /**
     * @inheritdoc
     */
    public function register()
    {
        return [T_CLASS];
    }

    /**
     * Assemble the FQN from the available tokens.
     *
     * @param File $file
     * @param $className
     * @return string
     */
    private function assembleFqn(File $file, $className)
    {
        $namespace = $file->findNext(T_NAMESPACE, 0);
        $namespaceStart = $namespace + 2; // skip over whitespace and namespace token
        $namespaceEnd = $file->findNext(T_SEMICOLON, $namespaceStart);

        $fqn = $file->getTokensAsString($namespaceStart, $namespaceEnd - $namespaceStart);

        $multipleNamespaces = $file->findNext(T_NAMESPACE, $namespace + 1);
        if ($multipleNamespaces || !$namespace) {
            return '';
        }

        return $fqn . '\\' . $className;
    }

    /**
     * Validate the given file for annotations at the class level.
     *
     * @param File $file
     * @param $stackPtr
     */
    private function validateInterceptorAtClassLevel(File $file, $stackPtr)
    {
        $tokens = $file->getTokens();

        $previous = $file->findPrevious(
            array_merge(Tokens::$commentTokens, [T_WHITESPACE, T_FINAL]),
            $stackPtr-1,
            null,
            true
        );
        $openCommentPtr = $file->findPrevious(T_DOC_COMMENT_OPEN_TAG, $stackPtr, $previous);
        if ($openCommentPtr === false) {
            $file->addWarning(
                static::MESSAGE_MISSING_CLASS_ANNOTATION,
                $stackPtr,
                static::ISSUE_MISSING_CLASS_ANNOTATION
            );
            return;
        }

        $tags = $tokens[$openCommentPtr]['comment_tags'];
        $foundSee = false;
        foreach ($tags as $tagPtr) {
            if ($tokens[$tagPtr]['content'] === '@see') {
                $foundSee = true;
                break;
            }
        }

        if ($foundSee === false) {
            $file->addWarning(
                static::MESSAGE_MISSING_CLASS_ANNOTATION,
                $stackPtr,
                static::ISSUE_MISSING_CLASS_ANNOTATION
            );
            return;
        }
    }

    /**
     * Validate the given file for annotations at the method level.
     *
     * @param File $file
     * @param $stackPtr
     * @throws \PHP_CodeSniffer\Exceptions\RuntimeException
     * @throws \PHP_CodeSniffer\Exceptions\TokenizerException
     */
    private function validateInterceptorAtMethodLevel(File $file, $stackPtr)
    {
        $tokens = $file->getTokens();

        $name = $file->getDeclarationName($stackPtr);
        $properties = $file->getMethodProperties($stackPtr);

        // Only require @see annotation for intercepting methods
        if ($properties['scope'] !== 'public'
            || (strpos($name, 'before') !== 0
                && strpos($name, 'after') !== 0
                && strpos($name, 'around') !== 0
            )
        ) {
            return;
        }

        $previous = $file->findPrevious(
            array_merge(Tokens::$methodPrefixes, Tokens::$commentTokens, [T_WHITESPACE]),
            $stackPtr-1,
            null,
            true
        );
        $openCommentPtr = $file->findPrevious(T_DOC_COMMENT_OPEN_TAG, $stackPtr, $previous);
        if ($openCommentPtr === false) {
            $file->addWarning(
                static::MESSAGE_MISSING_METHOD_ANNOTATION,
                $stackPtr,
                static::ISSUE_MISSING_METHOD_ANNOTATION
            );
            return;
        }

        $tags = $tokens[$openCommentPtr]['comment_tags'];
        $foundSee = false;
        foreach ($tags as $tagPtr) {
            if ($tokens[$tagPtr]['content'] === '@see') {
                $foundSee = true;
                break;
            }
        }

        if ($foundSee === false) {
            $file->addWarning(
                static::MESSAGE_MISSING_METHOD_ANNOTATION,
                $stackPtr,
                static::ISSUE_MISSING_METHOD_ANNOTATION
            );
            return;
        }
    }
}
