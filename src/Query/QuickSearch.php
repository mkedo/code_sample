<?php

namespace App\Query;

use Doctrine\ORM\QueryBuilder;

/**
 * Быстрый поиск.
 */
class QuickSearch
{
    /**
     * @var array
     */
    private $fields = [];

    /**
     * Точное сравнение по текстовой колонке.
     * @param string $columnName
     * @return $this
     */
    public function exactBy(string $columnName): self
    {
        $this->fields[$columnName] = 'exact';
        return $this;
    }

    /**
     * Полнотекстовый поиск по текстовой колонке.
     * @param string $columnName
     * @return $this
     */
    public function fullTextBy(string $columnName): self
    {
        $this->fields[$columnName] = 'fts';
        return $this;
    }

    /**
     * Поиск по целочисленной числовой колонке.
     * @param string $columnName
     * @return $this
     */
    public function byInteger(string $columnName): self
    {
        $this->fields[$columnName] = 'integer';
        return $this;
    }

    /**
     * @param QueryBuilder $qb
     * @param string $query
     * @return QueryBuilder
     * @throws \Exception
     */
    public function apply(QueryBuilder $qb, string $query): QueryBuilder
    {
        if (!empty($this->fields)) {
            $ors = [];
            $hasEquery = false;
            $hasFts = false;
            foreach ($this->fields as $columnName => $type) {
                if ($type === 'exact') {
                    $ors [] = "{$columnName} = :qs_equery";
                    $hasEquery = true;
                } elseif ($type === 'fts') {
                    $ors [] = "ts_match_op(to_tsvector('russian',{$columnName}), 
                               to_tsquery('russian', :qs_ftquery)) = true";
                    // чтоб находил при поиске по словам которые в стоп-листе
                    $ors [] = "lower({$columnName}) LIKE lower(:qs_lquery)";
                    $hasFts = true;
                } elseif ($type === 'integer') {
                    if (filter_var($query, FILTER_VALIDATE_INT) !== false) {
                        $hasEquery = true;
                        $ors [] = "{$columnName} = :qs_equery";
                    }
                } else {
                    throw new \Exception("Неизвестный тип поиска {$type}");
                }
            }
            $qb->andWhere($qb->expr()->orX(...$ors));

            if ($hasEquery) {
                $qb->setParameter('qs_equery', $query);
            }
            if ($hasFts) {
                $qb->setParameter('qs_lquery', '%' . $query . '%');
                $qb->setParameter('qs_ftquery', $this->prepareFTSKeywords($query));
            }
        }
        return $qb;
    }
    // ...
}
