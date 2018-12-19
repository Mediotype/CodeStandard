<?php
/**
 * @author    Mediotype Development <diveinto@mediotype.com>
 * @copyright 2018 Mediotype. All Rights Reserved.
 */

namespace Mediotype\CodeStandard\PHP;

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
            || ($fqnParts[2] !== 'Module' && $fqnParts[2] !== 'Plugin') // Is not Module\ or Plugin\
            || ($fqnParts[2] === 'Module' && $fqnParts[3] !== 'Plugin') // Is not Module\Plugin
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

    private function assembleFqn(File $file, $className)
    {
        $namespace = $file->findNext(T_NAMESPACE, 0);
        $namespaceStart = $namespace + 2; // skip over whitespace and namespace token
        $namespaceEnd = $file->findNext(T_SEMICOLON, $namespaceStart);

        $fqn = $file->getTokensAsString($namespaceStart, $namespaceEnd - $namespaceStart);

        $multipleNamespaces = $file->findNext(T_NAMESPACE, $namespace + 1);
        if ($multipleNamespaces || !$namespace) {
            return;
        }

        return $fqn . '\\' . $className;
    }

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
