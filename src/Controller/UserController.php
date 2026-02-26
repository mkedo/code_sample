<?php

namespace App\Controller;

use App\Api\ApiError;
use App\Api\ApiResponse;
use App\Crypto\SignatureCollection;
use App\Crypto\SignatureView\SignatureViewFactory;
use App\Crypto\ValidatorFactory;
use App\Security\AppUser;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
  /**
   * @Route("/contragentInfo")
   * @IsGranted("ROLE_ORGANIZER")
   * @return Response
   * @throws Exception
   */
    public function contragentInfo(): Response
    {
        $user = AppUser::fromUserInterface($this->getUser());
        $contragent = $user->getContragent();

        if (empty($contragent)) {
            return new ApiError("Пользователь не привязан к организации");
        }

        $legalAddress = $contragent->getLegalAddress();
        $postalAddress = $contragent->getPostalAddress();
        $info = [
          'id' => $contragent->getId(),
          'fullName' => $contragent->getFullName(),
          'inn' => $contragent->getInn(),
          'kpp' => $contragent->getKpp(),
          'ogrn' => $contragent->getOgrn(),
          'phone' => $contragent->getPhone(),
          'email' => $contragent->getEmail(),
          'legalAddress' => $legalAddress ?: '',
          'postalAddress' => $postalAddress ?: '',
        ];

        return new ApiResponse($info);
    }

  /**
   * @IsGranted("ROLE_ORGANIZER")
   * @Route("/checkEds")
   * @param Request $request
   * @param ValidatorFactory $validatorFactory
   * @param SignatureViewFactory $signatureViewFactory
   * @return Response
   * @throws Exception
   */
    public function checkEds(
        Request $request,
        ValidatorFactory $validatorFactory,
        SignatureViewFactory $signatureViewFactory
    ): Response
    {
        $signatures = SignatureCollection::fromJson($request->getContent());
        $validator = $validatorFactory->getPlain();

        try {
            $validator->validate($signatures->getAsString('check'));
            $view = $signatureViewFactory->getView($signatures->getAsString('check'));
            return new ApiResponse([
              'description' =>
                "Подпись прошла проверку\n\n"
                ."Реквизиты сертификата:\n"
                . $view->describeCertificate(),
            ]);
        } catch (\Exception $e) {
            return new ApiError($e->getMessage());
        }
    }

    /**
     * Получить реквизиты сертификата
     * @IsGranted("ROLE_ORGANIZER")
     * @Route("/getEds")
     * @return Response
     * @throws Exception
     */
    public function getEds(): Response
    {
        $user = AppUser::fromUserInterface($this->getUser());

        $certificate = $user->getCertificate();
        if (!$certificate) {
            throw new Exception("У пользователя не найден сертификат");
        }
        $identDescribe = $certificate->asCertificateIdent()->describeCertificate();

        try {
            return new ApiResponse([
                'description' => $identDescribe,
            ]);
        } catch (\Exception $e) {
            return new ApiError($e->getMessage());
        }
    }
}
