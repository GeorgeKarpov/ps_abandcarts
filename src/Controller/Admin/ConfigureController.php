<?php

declare(strict_types=1);

namespace Yourintellidata\Module\Abandcarts\Controller\Admin;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\ORM\EntityManagerInterface;
use Yourintellidata\Module\Abandcarts\Entity\AbandcartEmail;
use DateTime;

class ConfigureController extends FrameworkBundleAdminController
{
    const TAB_CLASS_NAME = 'AdminAbandcartsControllerTabsConfigure';

    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
     *
     * @return Response
     */
    public function indexAction()
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->container->get('doctrine.orm.entity_manager');
        $emailRepo = $entityManager->getRepository(AbandcartEmail::class);

        $email = $emailRepo->findBy(['cartId' => 2]);
        $test = '';
        if ($email != null) {
            $this->addFlash('error', 'Already exists');
            $test = 'Already exists';
        } else {
            $email = new AbandcartEmail();
            $email->setCartId(2)->setDateSent(new DateTime());
            $entityManager->persist($email);

            //This call validates all previous modification (modified/persisted entities)
            //This is when the database queries are performed
            $entityManager->flush();
            $test = $email->getDateSent()->format('d.m.Y H:i:s');
        }


        return $this->render('@Modules/abandcarts/views/templates/admin/configure.html.twig',  [
            'enableSidebar' => true,
            'help_link' => false,
            'test' => $test
        ]);
    }
}
