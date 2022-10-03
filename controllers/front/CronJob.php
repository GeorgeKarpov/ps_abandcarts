<?php

class AbandcartsCronJobModuleFrontController extends ModuleFrontController
{
    public $auth = false;
    public function __construct() {
        $this->module = new Abandcarts();
        parent::__construct();
        // exit();
    }
    
    public function postProcess()
    {
        $token = Tools::getValue('token');
        if ($token !== Configuration::get('ABANDCARTS_HASH_TOKEN')) {
            $this->ajaxRender([
                'success' => false,
                'message' => 'wrong token'
            ]);
            exit();
        }

        $action = Tools::getValue('action');
        if ($action == 'processAbandCarts') {
            $this->module->logger->info('Cron job started', ['method' => __FUNCTION__]);
            $this->module->processAbandCarts();
            $this->module->logger->info('Cron job completed', ['method' => __FUNCTION__]);
        }
    }

    public function display()
    {
        $this->ajax = 1;

        $this->ajaxRender("Cron controller\n");
    }
}
