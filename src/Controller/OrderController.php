<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\OrderInput;
use App\Entity\User;
use App\Form\OrderFormType;
use App\Service\OrderManager;
use App\Service\ProductCatalog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class OrderController extends AbstractController
{
    #[Route(path: '/', name: 'app_home', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function index(
        Request $request,
        ProductCatalog $catalog,
        OrderManager $orderManager,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $input = new OrderInput();
        $form = $this->createForm(OrderFormType::class, $input);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $orderManager->createFromInput($input, $user);
            $this->addFlash('success', 'Заказ успешно оформлен.');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('home/index.html.twig', [
            'form' => $form->createView(),
            'bags' => $catalog->byCategory(ProductCatalog::CATEGORY_BAGS),
            'clothing' => $catalog->byCategory(ProductCatalog::CATEGORY_CLOTHING),
            'orders' => $orderManager->listVisibleTo($user),
        ]);
    }
}
