<?php

namespace App\Query;

use Doctrine\ORM\QueryBuilder;

/**
 * Сортировка.
 */
class Sort
{
    /**
     * @param QueryBuilder $qb
     * @param array $sorts [['column'=> , 'order' => 'ASC'|'DESC' }
     * @return QueryBuilder
     */
    public function apply(QueryBuilder $qb, array $sorts): QueryBuilder
    {
        foreach ($sorts as $sort) {
            $order = $sort['order'] ?? 'ASC';
            $qb->addOrderBy($sort['column'], $order);
        }
        return $qb;
    }
}
