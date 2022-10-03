<?php

declare(strict_types=1);

namespace Yourintellidata\Module\Abandcarts\Carts;

use Configuration;
use Db;
use Context;
use Cart;
use CartRule;
use Module;


class AbandCart
{
    private $module;
    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    /**
     * clears expired discounts for aband. carts.
     *
     */
    public function clearExpiredDiscounts()
    {
        $now = date('Y-m-d H:i:s');
        $sql = 'SELECT cr.id_cart_rule';
        $sql .= ' FROM ' . _DB_PREFIX_ . 'cart_rule cr';
        $sql .= ' INNER JOIN ' . _DB_PREFIX_ . 'abandcart_email ae ON cr.id_cart_rule = ae.id_cart_rule';
        $sql .= " WHERE cr.date_to < '" . $now . "'";
        $this->module->loggerdebug->debug('clearExpiredDiscounts', ['sql' => $sql]);
        $result = Db::getInstance()->executeS($sql);
        $this->module->logger->info('Expired cart rules found', ['count' => count($result), ['method' => __FUNCTION__]]);
        foreach ($result as $item) {
            $cartrule = new CartRule($item['id_cart_rule']);
            $r = $cartrule->delete();
            $this->module->logger->info('Delete expired discount', ['id' => $item['id_cart_rule'], 'result' => $r, ['method' => __FUNCTION__]]);
        }
    }

    /**
     * get Carts for reminder.
     *
     * @param int $remtype
     */
    public function getCartsReminder(int $remtype)
    {
        $now = date('Y-m-d H:i:s');
        $delay1 = (int)Configuration::get('ABANDCARTS_DELAY_1');
        $delay2 = (int)Configuration::get('ABANDCARTS_DELAY_2');
        $delay3 = ($delay1 + $delay2);
        $delay4 = (int)Configuration::get('ABANDCARTS_DELAY_4');
        $sql = 'SELECT c.id_cart, c.id_lang, cu.id_customer, c.id_shop, cu.firstname, cu.lastname, cu.email, c.date_upd, c.date_add';
        if ($remtype != null && $remtype != 0) {
            $sql .= ', abcart.id_cart_rule';     
        }
        $sql .= ' FROM ' . _DB_PREFIX_ . 'cart c';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'orders o ON (o.id_cart = c.id_cart)';
        $sql .= ' RIGHT JOIN ' . _DB_PREFIX_ . 'customer cu ON (cu.id_customer = c.id_customer)';
        $sql .= ' INNER JOIN ' . _DB_PREFIX_ . 'cart_product product ON c.id_cart = product.id_cart';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'abandcart_email abcart ON abcart.id_cart = c.id_cart';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'abandcart_customer abcartcust ON abcartcust.id_customer = c.id_customer';
        $sql .= ' WHERE (o.id_order IS NULL)';
        if ($remtype == null || $remtype == 0) {
            $sql .= " AND c.date_upd >= DATE_SUB('" . $now . "',INTERVAL " . ($delay1 + $delay2) . ' HOUR)';
            $sql .= " AND c.date_upd <= DATE_SUB('" . $now . "',INTERVAL " . $delay2 . ' HOUR)';
        }
        $sql .= ' AND c.id_customer NOT IN ';
        $sql .= '(SELECT oo.id_customer';
        $sql .= ' FROM ' . _DB_PREFIX_ . 'orders oo';
        $sql .= " WHERE oo.date_add >= DATE_SUB('" . $now . "',INTERVAL " . $delay3 . ' HOUR))';
        $sql .= ' AND abcartcust.id_customer IS NULL';
        if ($remtype != null && $remtype != 0) {
            $sql .= ' AND abcart.status =' . (int)$remtype;
            if ($remtype == 1) {
                $sql .= " AND abcart.date_sent <= DATE_SUB('" . $now . "',INTERVAL " . $delay4 . ' HOUR)';
            }
        } elseif ($remtype == null || $remtype == 0) {
            $sql .= ' AND abcart.id_cart IS NULL';
        }
        $sql .= ' GROUP BY cu.email';
        $sql .= ' ORDER BY c.date_upd DESC';
        $this->module->loggerdebug->debug('getCartsReminder',['sql' => $sql, 'remtype' => $remtype]);
        $carts = Db::getInstance()->executeS($sql);
        return $carts;
    }


    /**
     * get first time reminded carts.
     *
     */
    public function GetCartsReminded(int $reminder)
    {
        $sql = 'SELECT c.id_cart, c.id_lang, cu.id_customer, c.id_shop, cu.firstname, cu.lastname, cu.email, c.date_upd, c.date_add, abcart.date_sent';
        $sql .= ' FROM ' . _DB_PREFIX_ . 'cart c';
        $sql .= ' INNER JOIN ' . _DB_PREFIX_ . 'abandcart_email abcart ON abcart.id_cart = c.id_cart';
        $sql .= ' INNER JOIN ' . _DB_PREFIX_ . 'customer cu ON (cu.id_customer = c.id_customer)';
        $sql .= ' WHERE abcart.status = ' . $reminder;
        $this->module->loggerdebug->debug('GetCartsReminded', ['sq' => $sql]);
        $carts = Db::getInstance()->executeS($sql);
        return $carts;
    }

    /**
     * get link to restore cart.
     *
     * @param int $id_cart
     */
    public static function getCartlink($id_cart)
    {
        $cart = new Cart($id_cart);
        $link = Context::getContext()->link->getPageLink(
            'cart',
            true,
            (int) $cart->getAssociatedLanguage()->getId(),
            [
                'action' => 'show',
                'step' => 3,
                'recover_cart' => $cart->id,
                'token_cart' => md5(_COOKIE_KEY_ . 'recover_cart_' . (int) $cart->id),
            ]
        );
        return $link;
    }
}
