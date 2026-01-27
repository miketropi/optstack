<?php

declare(strict_types=1);

namespace OptStack\Core\Condition;

/**
 * Condition Evaluator
 *
 * Evaluates a set of conditions against given data.
 */
class ConditionEvaluator
{
    /**
     * Evaluate a set of conditions.
     *
     * @param array<Condition> $conditions Conditions to evaluate
     * @param array<string, mixed> $data Data to evaluate against
     * @return bool True if all conditions pass
     */
    public function evaluate(array $conditions, array $data): bool
    {
        if (empty($conditions)) {
            return true;
        }

        $results = [];
        $currentRelation = 'AND';

        foreach ($conditions as $condition) {
            $result = $condition->evaluate($data);
            $results[] = [
                'result' => $result,
                'relation' => $currentRelation,
            ];
            $currentRelation = $condition->getRelation();
        }

        return $this->resolveResults($results);
    }

    /**
     * Resolve grouped results with AND/OR logic.
     *
     * @param array<array{result: bool, relation: string}> $results
     */
    protected function resolveResults(array $results): bool
    {
        if (empty($results)) {
            return true;
        }

        // Start with the first result
        $final = $results[0]['result'];

        for ($i = 1; $i < count($results); $i++) {
            $result = $results[$i];

            if ($result['relation'] === 'OR') {
                $final = $final || $result['result'];
            } else {
                $final = $final && $result['result'];
            }
        }

        return $final;
    }

    /**
     * Check if any condition passes.
     *
     * @param array<Condition> $conditions
     * @param array<string, mixed> $data
     */
    public function any(array $conditions, array $data): bool
    {
        foreach ($conditions as $condition) {
            if ($condition->evaluate($data)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if all conditions pass.
     *
     * @param array<Condition> $conditions
     * @param array<string, mixed> $data
     */
    public function all(array $conditions, array $data): bool
    {
        foreach ($conditions as $condition) {
            if (!$condition->evaluate($data)) {
                return false;
            }
        }

        return true;
    }
}
