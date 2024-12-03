<?php
namespace App\Support;

/**
 * @template T1
 * @template T2
 */
final class Pair
{
    /**
     * @param T1 $first
     * @param T2 $second
     */
    public function __construct(public readonly mixed $first, public readonly mixed $second) {
        
    }

    /**
     * @template T1
     * @template T2
     * @param Pair<T1,T2>[] $pairs
     * @return PairItems<T1,T2>
     */
    public static function pairsToItems(array $pairs): PairItems {
        $firsts = [];
        $seconds = [];
        foreach ($pairs as $pair) {
            $firsts[] = $pair->first;
            $seconds[] = $pair->second;
        }
        return new PairItems($firsts, $seconds);
    }
}

/**
 * @template T1
 * @template T2
 */
class PairItems
{
    /**
     * @param T1[] $firsts
     * @param T2[] $seconds
     */
    public function __construct(public readonly array $firsts, public readonly array $seconds) {
        
    }
}
