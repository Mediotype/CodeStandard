<?php
declare(strict_types=1);

namespace Mediotype\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class MethodOrderSniff implements Sniff
{
    public const METHOD_OUT_OF_ORDER_ANNOTATION = 'MethodOutOfOrder';

    public const METHOD_OUT_OF_ORDER_MESSAGE_END = 'Method %s is out of order, it should be after %s (the last method)';
    public const METHOD_OUT_OF_ORDER_MESSAGE_START = 'Method %s is out of order, it should be before %s (the first method)';
    public const METHOD_OUT_OF_ORDER_MESSAGE = 'Method %s is out of order, it should be after %s and before %s';

    private const INITIALIZER_ORDER = ['__construct', '_construct', '_init'];
    private const SCOPE_ORDER = ['public', 'protected', 'private'];

    public function register()
    {
        return [T_CLASS];
    }

    public function process(File $phpcsFile, $stackPtr)
    {
        // Scopes are grouped, and alphabetized within those groups
        // Method names of _init, _construct, and __constructor are their own group, alphabatized and first at the top
        // Otherwise, public, protected, private is the order

        $methods = [];
        $methodPointer = $stackPtr;
        while ($methodPointer = $phpcsFile->findNext(T_FUNCTION, $methodPointer + 1)) {
            $methods[] = $methodPointer;
        }

        $sortedMethods = $methods;
        usort(
            $sortedMethods,
            static function ($a, $b) use ($phpcsFile) {
                return self::sortMethods($phpcsFile, $a, $b);
            }
        );

        // psuedo code
        // get array of all methods
        // get sorted array of all methods
        // calculate the index offset
        // whatever has the biggest offset is out of order
        // it needs to be marked as out of order, removed from the arrays, and then loop at recalculating offsets

        // Things that do not work:
        // - everything with an incorrect offset is wrong (one wrong item has a cascade effect on all items after it)

        while (true) { // Once we no longer have anything out of order, we'll break out of the array
            // Reset the indices so we can easily grab before and after methods
            $methods = array_values($methods);
            $sortedMethods = array_values($sortedMethods);

            $deltaCount = [];
            foreach ($methods as $index => $methodPointer) {
                $correctIndex = array_search($methodPointer, $sortedMethods);
                $delta = $index - $correctIndex;
                if ($delta === 0) {
                    // Necessary so that the
                    continue;
                }

                if (!isset($deltaCount[$delta])) {
                    $deltaCount[$delta] = ['count' => 0];
                }
                $deltaCount[$delta]['delta'] = $delta;
                // The pointer can be overwritten.. we're doing it again and again so that's fine
                $deltaCount[$delta]['pointer'] = $methodPointer;
                $deltaCount[$delta]['index'] = $correctIndex;
                $deltaCount[$delta]['badIndex'] = $index;
            }
            if (empty($deltaCount)) {
                break;
            }

            // This makes the first thing we process the _most wrong_
            // We have to do it that way, b/c everything that comes after the wrong item in the sorted array
            // is also, currently, wrong.
            uasort(
                $deltaCount,
                static function ($a, $b) {
                    // b first, for reverse sort (higher first)
                    return $b['delta'] <=> $a['delta'];
                }
            );
            $data = reset($deltaCount);

            $name = $phpcsFile->getDeclarationName($data['pointer']);
            $correctIndex = $data['index'];

            $stringParams = [$name];
            $message = static::METHOD_OUT_OF_ORDER_MESSAGE;
            if ($correctIndex > 0) {
                $stringParams[] = $phpcsFile->getDeclarationName($sortedMethods[$correctIndex - 1]);
            }
            if ($correctIndex === 0) {
                $message = static::METHOD_OUT_OF_ORDER_MESSAGE_START;
            }
            if ($correctIndex < count($sortedMethods) - 1) {
                $stringParams[] = $phpcsFile->getDeclarationName($sortedMethods[$correctIndex + 1]);
            }
            if ($correctIndex === count($sortedMethods) - 1) {
                $message = static::METHOD_OUT_OF_ORDER_MESSAGE_END;
            }

            $phpcsFile->addWarning(
                sprintf($message, ...$stringParams),
                $data['pointer'],
                static::METHOD_OUT_OF_ORDER_ANNOTATION
            );

            unset($sortedMethods[$correctIndex], $methods[$data['badIndex']]);
        }
    }

    private static function sortMethods(File $file, $methodAPointer, $methodBPointer)
    {
        $nameA = strtolower($file->getDeclarationName($methodAPointer));
        $nameB = strtolower($file->getDeclarationName($methodBPointer));

        // We use these as number so we can spaceship operator it, lowest number goes first
        $nameAIsInit = in_array($nameA, self::INITIALIZER_ORDER) ? 0 : 1;
        $nameBIsInit = in_array($nameB, self::INITIALIZER_ORDER) ? 0 : 1;

        if ($nameAIsInit !== $nameBIsInit) {
            // We only do this if they're different, otherwise they might be in the same group!
            return $nameAIsInit <=> $nameBIsInit;
        }

        if ($nameAIsInit === 0 && $nameBIsInit === 0) {
            return array_search($nameA, self::INITIALIZER_ORDER) <=> array_search($nameB, self::INITIALIZER_ORDER);
        }

        $propertiesA = $file->getMethodProperties($methodAPointer);
        $propertiesB = $file->getMethodProperties($methodBPointer);

        $scopeA = $propertiesA['scope'];
        $scopeB = $propertiesB['scope'];

        $scopeASortIndex = array_search($scopeA, self::SCOPE_ORDER);
        $scopeBSortIndex = array_search($scopeB, self::SCOPE_ORDER);
        $sameScope = $scopeASortIndex === $scopeBSortIndex;

        return $sameScope ? $nameA <=> $nameB : $scopeASortIndex <=> $scopeBSortIndex;
    }
}
