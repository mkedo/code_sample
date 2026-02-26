<?php

namespace App\Controller;

// ...
use App\Api\Table\RowQuery;
use App\Entity\Config;
use App\Entity\Contragent;
use App\Entity\Notification;
use App\Entity\User;
use App\Query\QuickSearch;
use App\Repository\ContragentRepository;


/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    /**
     * Список и поиск контрагентов.
     * @Route("/contragents")
     * @IsGranted("ROLE_ADMIN")
     * @return Response
     */
    public function contragents(
        ContragentRepository $contragentRepository,
        Request $request
    ): Response
    {
        $contragents = (new RowQuery($request->get('query', '')))
            ->setQuickSearch(
                (new QuickSearch())
                    ->fullTextBy('c.fullName')
                    ->exactBy('c.inn')
                    ->exactBy('c.kpp')
                    ->exactBy('c.ogrn')
                    ->fullTextBy('c.phone')
                    ->fullTextBy('c.email')
            )
            ->getPaginator($contragentRepository->getAll());

        $entries = array_map(function (Contragent $c) {
            return [
                'c' => [
                    'id' => $c->getId(),
                    'fullName' => $c->getFullName(),
                    'inn' => $c->getInn(),
                    'kpp' => $c->getKpp(),
                    'ogrn' => $c->getOgrn(),
                    'phone' => $c->getPhone(),
                    'email' => $c->getEmail(),
                ]
            ];
        }, (array)$contragents->getIterator());

        return new ApiResponse([
            'entries' => $entries,
            'total' => count($contragents),
        ]);
    }

    /* ... */
}