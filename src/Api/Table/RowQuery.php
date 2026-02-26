<?php

namespace App\Api\Table;

use App\Query\Filter;
use App\Query\QuickSearch;
use App\Query\Sort;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Берет на себя формирование запроса с
 * сортировкой,
 * пагинацией.
 */
class RowQuery
{
    /**
     * @var array
     */
    private $query;

    /**
     * @var QuickSearch
     */
    private $quickSearch;

    /**
     * RowQuery constructor.
     * @param string $json json-строка в формате
     * [
     *  'filter' => см. App\Query\Filter::apply()
     *  'sort' => [...]  см. App\Query\Sort::apply()
     *  'range' => ['pageSize' =>,  'page' => ]
     * ]
     * @throws \Exception
     */
    public function __construct(string $json)
    {
        if (empty($json)) {
            $this->query = [];
        } else {
            $this->query = \json_decode($json, true);
            if ($this->query === null) {
                throw new \Exception("Не удалось декодировать запрос");
            }
        }
    }

    /**
     * @param QuickSearch $quickSearch
     * @return RowQuery
     */
    public function setQuickSearch(QuickSearch $quickSearch): self
    {
        $this->quickSearch = $quickSearch;
        return $this;
    }

    /**
     * @param QueryBuilder $qb
     * @return Paginator
     */
    public function getPaginator(QueryBuilder $qb): Paginator
    {
        $qb = clone $qb;
        $query = $this->query;
        if (isset($query['sort'])) {
            (new Sort())->apply($qb, $query['sort']);
        }
        if (isset($query['filter'])) {
            (new Filter())
                ->apply($qb, $query['filter']);
        }
        $perPage = 25;
        $currentPage = 1;
        if (isset($query['range']['pageSize'])) {
            // -1 если выбрано "Все" записи
            if ($query['range']['pageSize'] > 0) {
                $perPage = $query['range']['pageSize'];
            }
        }
        if (isset($query['range']['page'])) {
            $currentPage = $query['range']['page'];
        }
        if (!empty($query['search']) && isset($this->quickSearch)) {
            $this->quickSearch->apply($qb, $query['search']);
        }

        $paginator = new Paginator($qb);

        $paginator
            ->getQuery()
            ->setFirstResult($perPage * ($currentPage - 1))
            ->setMaxResults($perPage);

        return $paginator;
    }
}
