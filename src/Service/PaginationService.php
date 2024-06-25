<?php
namespace App\Service;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

class PaginationService
{
    public function paginate(QueryBuilder $queryBuilder, int $page, int $limit): array
    {
        $query = $queryBuilder
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();

        $paginator = new Paginator($query);
        $totalItems = count($paginator);
        $totalPages = (int) ceil($totalItems / $limit);

        return [
            'data' => $paginator->getIterator(),
            'meta' => [
                'page' => $page,
                'pageSize' => $limit,
                'totalPages' => $totalPages,
                'totalItems' => $totalItems,
            ],
        ];
    }
}