<?php

declare(strict_types=1);

namespace Yourintellidata\Module\Abandcarts\Carts;

use Yourintellidata\Module\Abandcarts\Entity\AbandcartCustomer;
use Configuration;
use Db;
use Module;
use DateTime;

class AbandCustomer
{
    /** @var Module $module */
    private $module;
    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    public function ResetCustomers()
    {
        $now = date('Y-m-d H:i:s');
        $delay1 = (int)Configuration::get('ABANDCARTS_DELAY_1');
        $delay2 = (int)Configuration::get('ABANDCARTS_DELAY_2');
        $delay3 = ($delay1 + $delay2);
        
        $sql = 'SELECT ac.id_customer FROM ' . _DB_PREFIX_ . 'abandcart_email ac';
        $sql .= ' INNER JOIN ' . _DB_PREFIX_ . 'orders o ON o.id_customer = ac.id_customer';
        $sql .= " WHERE o.date_add >= DATE_SUB('" . $now . "',INTERVAL " . $delay3 . ' HOUR)';
        $this->module->loggerdebug->debug('ResetCustomers',['sql' => $sql]);
        $ordered = Db::getInstance()->executeS($sql);
        $this->module->logger->info('Ordered carts found', ['count' => count($ordered), ['method' => __FUNCTION__]]);
        if (count($ordered) > 0) {
            $sql = 'DELETE ac FROM ' . _DB_PREFIX_ . 'abandcart_email ac';
            $sql .= ' INNER JOIN ' . _DB_PREFIX_ . 'orders o ON o.id_customer = ac.id_customer';
            $sql .= " WHERE o.date_add >= DATE_SUB('" . $now . "',INTERVAL " . $delay3 . ' HOUR)';
            $deleted = Db::getInstance()->execute($sql);
            $this->module->logger->info('Reset ordered emails', ['result' => $deleted, 'method' => __FUNCTION__]);
        }

        $sql = 'SELECT ' . _DB_PREFIX_ . 'abandcart_email.id_customer FROM ' . _DB_PREFIX_ . 'abandcart_email';
        $sql .= ' INNER JOIN ' . _DB_PREFIX_ . 'abandcart_customer ON ' . _DB_PREFIX_ . 'abandcart_customer.id_customer = ' . _DB_PREFIX_ . 'abandcart_email.id_customer';
        $sql .= ' WHERE ' . _DB_PREFIX_ . 'abandcart_customer.date_added <= DATE_SUB(NOW(),INTERVAL ' . (int)Configuration::get('ABANDCARTS_DELAY_5') . ' DAY)';
        $cartsreset = Db::getInstance()->executeS($sql);
        $this->module->logger->info('Carts for reset found', ['count' => count($cartsreset), 'method' => __FUNCTION__]);
        if (count($cartsreset) > 0) {
            $sql = 'DELETE ' . _DB_PREFIX_ . 'abandcart_email FROM ' . _DB_PREFIX_ . 'abandcart_email';
            $sql .= ' INNER JOIN ' . _DB_PREFIX_ . 'abandcart_customer ON ' . _DB_PREFIX_ . 'abandcart_customer.id_customer = ' . _DB_PREFIX_ . 'abandcart_email.id_customer';
            $sql .= ' WHERE ' . _DB_PREFIX_ . 'abandcart_customer.date_added <= DATE_SUB(NOW(),INTERVAL ' . (int)Configuration::get('ABANDCARTS_DELAY_5') . ' DAY)';
            $deleted = Db::getInstance()->execute($sql);
            $this->module->logger->info('Reset customers carts', ['result' => $deleted, 'method' => __FUNCTION__]);
        }

        $sql = 'SELECT id_customer FROM ' . _DB_PREFIX_ . 'abandcart_customer';
        $sql .= ' WHERE date_added <= DATE_SUB(NOW(),INTERVAL ' . (int)Configuration::get('ABANDCARTS_DELAY_5') . ' DAY)';
        $custreset = Db::getInstance()->executeS($sql);
        $this->module->logger->info('Customers for reset found', ['count' => count($custreset), 'method' => __FUNCTION__]);
        if (count($custreset) > 0) {
            $sql = 'DELETE FROM ' . _DB_PREFIX_ . 'abandcart_customer';
            $sql .= ' WHERE date_added <= DATE_SUB(NOW(),INTERVAL ' . (int)Configuration::get('ABANDCARTS_DELAY_5') . ' DAY)';
            $deleted = Db::getInstance()->execute($sql);
            $this->module->logger->info('Reset customers', ['result' => $deleted, 'method' => __FUNCTION__]);
        }   
    }

    public function AddCustomer(int $id)
    {
        $entityManager = $this->module->get('doctrine.orm.entity_manager');
        $customerRepo = $entityManager->getRepository(AbandcartCustomer::class);
        $customers = $customerRepo->findBy(['customerId' => $id]);
        if ($customers != null) {
            $this->module->logger->warning('CustomerId already exists in datatable', ['method' => __FUNCTION__, 'line' => __LINE__]);
        } else {
            $customer = new AbandcartCustomer();
            $customer
                ->setCustomerId($id)
                ->setDateAdded(new DateTime());
            $entityManager->persist($customer);
            $entityManager->flush();
        }
    }
}
