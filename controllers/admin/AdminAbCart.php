<?php

use Yourintellidata\Module\Abandcarts\Carts\AbandCart;

class AdminAbCartController extends ModuleAdminController
{
    public $php_self = 'aband-cart';
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    public function initContent()
    {
        $cart = new AbandCart($this->module);
        $carts = $cart->getCartsReminder(0);
        $cartsfirst = $cart->GetCartsReminded(1);
        $cartssecond = $cart->GetCartsReminded(2);
        $smarty = $this->context->smarty;
        $config['date'] = '%d.%m.%Y %H:%M:%S';
        $smarty->assign('config', $config);
        $smarty->assign(array(
            'carts' => $carts,
            'cartsfirst' => $cartsfirst,
            'cartssecond' => $cartssecond,
            'cartLink' => $this->context->link->getAdminLink('AdminCarts', true).'&viewcart',
        ));
        $content = $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/carts.tpl');
        $this->content = $content;
        parent::initContent();
    }

    public function initPageHeaderToolbar()
    {

        $short = $this->trans(
            'Module settings',
            array(),
            'Modules.Abandcarts.Admin'
        );
        $descr = $this->trans(
            'Module settings',
            array(),
            'Modules.Abandcarts.Admin'
        );
        if ($this->display != 'view') {
            $this->page_header_toolbar_btn['abandcartsmodulesettingsbtn'] = array(
                'short' => $short,
                'href' => $this->context->link->getAdminLink('AdminModules', true).'&configure=abandcarts',
                'desc' => $descr,
                'class' => 'venipak-icon icon-gear',
            );
        }

        parent::initPageHeaderToolbar();
        $this->context->smarty->clearAssign('help_link');
    }
}
