<?php

declare(strict_types=1);

namespace Yourintellidata\Module\Abandcarts\Controller\Admin;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\ORM\EntityManagerInterface;
use Yourintellidata\Module\Abandcarts\Entity\AbandcartEmail;
use DateTime;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PrestaShop\PrestaShop\Adapter\Shop\Context;
use Tools;

class TestController extends FrameworkBundleAdminController
{
    
    const TAB_CLASS_NAME = 'AdminAbandcartsControllerTest';
    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
     *
     * @return Response
     */
    public function testAction()
    {
        $logger = new Logger($this->name);
        $logger->pushHandler(new StreamHandler(_PS_MODULE_DIR_ . $this->name . '/error.log', Logger::INFO));
        $logger->info('Test', [__METHOD__]);
        // $abandCarts = new AbandCart();
        // /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->container->get('doctrine.orm.entity_manager');
        // $emailRepo = $entityManager->getRepository(AbandcartEmail::class);

        // $emails = $emailRepo->findBy(['cartId' => 1]);
        // if ($emails != null) {
        //     $logger->warning('Record already exists in datatable', [__METHOD__, $emails[0]]);
        // } else {
        //     $email = new AbandcartEmail();
        //     $email
        //         ->setCartId(1)
        //         ->setDateSent(new DateTime())
        //         ->setStatus(1);
        //     $entityManager->persist($email);
        //     $entityManager->flush();
        //     $logger->info('Record added', [__METHOD__, $email]);
        // }
        Tools::redirectAdmin(
            $this->context->link-> getAdminLink('AdminModules', true).'&configure=abandcarts'
        );
       
    }
}