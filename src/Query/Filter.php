<?php

namespace App\Query;

use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\Query\ParameterTypeInferer;
use Doctrine\ORM\QueryBuilder;

/**
 * Фильтр по колонкам.
 */
class Filter
{
    /**
     * @param QueryBuilder $qb
     * @param array $filter [ ['column' => "...", 'op' => "...", 'value' => "..."], ]
     * @return QueryBuilder
     */
    public function apply(QueryBuilder $qb, array $filter): QueryBuilder
    {
        $colIdx = 0;
        foreach ($filter as $filterRow) {
            $colIdx++;
            $filterParamName = 'filter_p' . $colIdx;
            $filterColumn = $filterRow['column'];
            $op = $filterRow['op'];

            if (!in_array($op, ['in', '<>', '=', '<', '<=', '>', '>=', 'icmp', 'isnullor'], true)) {
                throw new \Exception("Неподдерживамая операция для фильтра '{$op}'");
            }
            $name = $filterColumn;

            if ($op === 'in') {
                $value = $filterRow['value']; //TODO: проверить что это массив
                $comparison = new Func($name . ' IN', [':' . $filterParamName]);
            } elseif ($op === 'isnullor') {
                $value = $filterRow['value'];
                $comparison = new \Doctrine\ORM\Query\Expr\Orx([
                    new Comparison($name, 'IS', 'NULL'),
                    new Comparison($name, '=', ':' . $filterParamName)
                ]);
            } elseif ($op === 'icmp') {
                // регистронезависимое сравнение
                $value = $filterRow['value'];
                $comparison = new Comparison(
                    sprintf('lower(%s)', $name),
                    '=',
                    sprintf('lower(:%s)', $filterParamName)
                );
            } else {
                $value = $filterRow['value'];
                $comparison = new Comparison($name, $op, ':' . $filterParamName);
            }
            $qb->andWhere($comparison);
            $qb->setParameter($filterParamName, $value, ParameterTypeInferer::inferType($value));
        }
        return $qb;
    }
}
