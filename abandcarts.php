<?php

declare(strict_types=1);

use Yourintellidata\Module\Abandcarts\Install\Installer;
use Yourintellidata\Module\Abandcarts\Controller\Admin\ConfigureController;
use Yourintellidata\Module\Abandcarts\Controller\Admin\TestController;
use Yourintellidata\Module\Abandcarts\Carts\AbandCart;
use Yourintellidata\Module\Abandcarts\Carts\AbandCustomer;
use Doctrine\ORM\EntityManagerInterface;
use Yourintellidata\Module\Abandcarts\Entity\AbandcartEmail;
// use DateTime;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PrestaShop\PrestaShop\Core\MailTemplate\Layout\Layout;
use PrestaShop\PrestaShop\Core\MailTemplate\ThemeCollectionInterface;
use PrestaShop\PrestaShop\Core\MailTemplate\ThemeInterface;


if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

class Abandcarts extends Module
{
    public $conf_keys = array();
    public $tabs = [];
    const ABANDCARTS_FROM_NAME = 'netprice.lv';
    const ABANDCARTS_LEGAL_GROUP = 5;

    const ABANDCARTS_CART_DISC_FROM_1 = 25.00;
    const ABANDCARTS_CART_DISC_FROM_2 = 40.00;
    const ABANDCARTS_CART_DISC_FROM_3 = 60.00;

    const ABANDCARTS_CART_DISC_AMOUNT_1 = 1.00;
    const ABANDCARTS_CART_DISC_AMOUNT_2 = 2.00;
    const ABANDCARTS_CART_DISC_AMOUNT_3 = 3.00;

    const ABANDCARTS_CART_DAYS_VALID = 5;


    /** @var Logger $logger */
    public $logger;

    /** @var Logger $loggerdebug */
    public $loggerdebug;

    public function __construct()
    {
        $this->name = 'abandcarts';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->author = 'Georgijs Karpovs';
        $this->need_instance = 0;
        $this->confirmUninstall = "Are you sure?";
        $this->bootstrap = true;
        parent::__construct();

        $this->loggerdebug = new Logger('debug');
        if (Configuration::get('ABANDCARTS_ENABLE_DEBUG_LOGS')) {
            $this->loggerdebug->pushHandler(new StreamHandler(_PS_MODULE_DIR_ . $this->name . '/debug.log', Logger::DEBUG));
        } else {
            $this->loggerdebug->pushHandler(new StreamHandler(_PS_MODULE_DIR_ . $this->name . '/debug.log', Logger::INFO));
        }

        $this->logger = new Logger($this->name);
        $this->logger->pushHandler(new StreamHandler(_PS_MODULE_DIR_ . $this->name . '/' . $this->name . '.log', Logger::INFO));

        $this->conf_keys = array(
            'ABANDCARTS_DELAY_1' => 24, // activity period before inactivity
            'ABANDCARTS_DELAY_2' => 24, // inactivity period
            'ABANDCARTS_DELAY_3' => 32, // Ignore customer with orders in last n hours
            'ABANDCARTS_DELAY_4' => 23, // Second reminder after n hours
            'ABANDCARTS_DELAY_5' => 10, // disable abandoned carts reminder for customer for n days
            'ABANDCARTS_ENABLE_1' => 0, // live mode
            'ABANDCARTS_EMAIL_FROM' => 'email@example.com',
            'ABANDCARTS_ENABLE_DEBUG_LOGS' => 0, // live mode
            'ABANDCARTS_HASH_TOKEN' => Tools::hash('abandcarts_token'),
        );
        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */


        $this->displayName = $this->trans('Abandoned Carts Notifier', array(), 'Modules.Abandcarts.Admin');
        $this->description = $this->trans('Creates and sends cart recovery emails', array(), 'Modules.Abandcarts.Admin');
    }

    public function isUsingNewTranslationSystem()
    {
        return true;
    }

    public function install()
    {

        if (!parent::install()) {
            return false;
        }
        $installer = new Installer($this, $this->loggerdebug);
        return $installer->install();
        // return true;
    }

    public function uninstall()
    {
        $installer = new Installer($this, $this->loggerdebug);

        return $installer->uninstall() && parent::uninstall();

        // return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {

        $html = '';
       

        /**
         * If values have been submitted in the form, process.
         */
        if (Tools::isSubmit('submitAband')) {
            $valid = true;
            if (!Validate::isEmail(Tools::getValue('ABANDCARTS_EMAIL_FROM'))) {
                $html .= $this->displayError($this->trans(
                    'Invalid email address',
                    array(),
                    'Modules.Abandcarts.Admin'
                ));
                $valid = false;
            }
            if ($valid) {
                $ok = true;
                foreach ($this->conf_keys as $key => $val) {
                    if (Tools::getValue($key) !== false) {
                        $ok &= Configuration::updateValue(
                            $key,
                            Tools::getValue($key)
                        );
                    }
                }
                if ($ok) {
                    $html .= $this->displayConfirmation($this->trans(
                        'Settings updated succesfully',
                        array(),
                        'Modules.Abandcarts.Admin'
                    ));
                } else {
                    $html .= $this->displayError($this->trans(
                        'Error occurred during settings update',
                        array(),
                        'Modules.Abandcarts.Admin'
                    ));
                }
            }
        }

        if (Tools::isSubmit('submitTest')) {
            $this->test();
        }

        return $html . $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        // $helper->submit_action = 'submitAband';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        // return $helper->generateForm(array($this->getConfigForm(), $this->getTestForm()));
        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        // $cron_link = AdminController::$currentIndex . '&configure=' . $this->name . '&cron' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules');
        $cron_link = $this->context->link->getModuleLink(
            $this->name,
            'CronJob',
            [
                'action' => 'processAbandCarts',
                'token' => Configuration::get('ABANDCARTS_HASH_TOKEN')
            ]
        );
        $fields_form_1 = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->trans(
                        'Abandoned carts',
                        array(),
                        'Modules.Abandcarts.Admin'
                    ),
                    'icon' => 'icon-cogs',
                ),
                'description' => $this->trans(
                    'For each cancelled cart (with no order), generate a discount and send it to the customer.',
                    array(),
                    'Modules.Abandcarts.Admin'
                ),
                'input' => array(
                    'class' => "",
                    array(
                        'type' => 'switch',
                        'is_bool' => true, //retro-compat
                        'label' => $this->trans(
                            'Live Mode',
                            array(),
                            'Admin.Actions'
                        ),
                        'name' => 'ABANDCARTS_ENABLE_1',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->trans(
                                    'Enabled',
                                    array(),
                                    'Admin.Global'
                                ),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->trans(
                                    'Disabled',
                                    array(),
                                    'Admin.Global'
                                ),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans(
                            'Carts activity period before inactive',
                            array(),
                            'Modules.Abandcarts.Admin'
                        ),
                        'name' => 'ABANDCARTS_DELAY_1',
                        'class' => 'col-sm-2',
                        'suffix' => $this->trans(
                            'hour(s)',
                            array(),
                            'Modules.Abandcarts.Admin'
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans(
                            'Carts inactivity period for first reminder',
                            array(),
                            'Modules.Abandcarts.Admin'
                        ),
                        'name' => 'ABANDCARTS_DELAY_2',
                        'class' => 'col-sm-2',
                        'suffix' => $this->trans(
                            'hour(s)',
                            array(),
                            'Modules.Abandcarts.Admin'
                        ),
                    ),
                    // array(
                    //     'type' => 'text',
                    //     'label' => $this->trans(
                    //         'Ignore customer with orders in last n hours',
                    //         array(),
                    //         'Modules.Abandcarts.Admin'
                    //     ),
                    //     'name' => 'ABANDCARTS_DELAY_3',
                    //     'class' => 'col-sm-2',
                    //     'suffix' => $this->trans(
                    //         'hour(s)',
                    //         array(),
                    //         'Modules.Abandcarts.Admin'
                    //     ),
                    // ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans(
                            'Second reminder after n hours',
                            array(),
                            'Modules.Abandcarts.Admin'
                        ),
                        'name' => 'ABANDCARTS_DELAY_4',
                        'class' => 'col-sm-2',
                        'suffix' => $this->trans(
                            'hour(s)',
                            array(),
                            'Modules.Abandcarts.Admin'
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans(
                            'Disable reminders for customer for n days after last reminder',
                            array(),
                            'Modules.Abandcarts.Admin'
                        ),
                        'name' => 'ABANDCARTS_DELAY_5',
                        'class' => 'col-sm-2',
                        'suffix' => $this->trans(
                            'day(s)',
                            array(),
                            'Modules.Abandcarts.Admin'
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'is_bool' => true, //retro-compat
                        'label' => $this->trans(
                            'Write debug logs',
                            array(),
                            'Admin.Actions'
                        ),
                        'name' => 'ABANDCARTS_ENABLE_DEBUG_LOGS',
                        'values' => array(
                            array(
                                'id' => 'debuglog_on',
                                'value' => 1,
                                'label' => $this->trans(
                                    'Enabled',
                                    array(),
                                    'Admin.Global'
                                ),
                            ),
                            array(
                                'id' => 'debuglog_off',
                                'value' => 0,
                                'label' => $this->trans(
                                    'Disabled',
                                    array(),
                                    'Admin.Global'
                                ),
                            ),
                        ),
                    ),
                    //ABANDCARTS_ENABLE_DEBUG_LOGS
                    array(
                        'type' => 'text',
                        'label' => $this->trans(
                            'From email address',
                            array(),
                            'Modules.Abandcarts.Admin'
                        ),
                        'name' => 'ABANDCARTS_EMAIL_FROM',
                        'required' => true,
                        'class' => 'col-sm-3',
                    ),
                    // array(
                    //     'type' => 'html',
                    //     'name' => 'cron_info',
                    //     'class' => 'col-sm-2',
                    //     'html_content' => '<a class="btn btn-default" href="' . $patch_link . '">' . $this->trans(
                    //         'Cron Link',
                    //         array(),
                    //         'Modules.Abandcarts.Admin'
                    //     ) . '</a>',
                    // ),
                    array(
                        'type' => 'html',
                        'name' => 'cron_info',
                        'html_content' => '<div class="alert alert-info">' . $cron_link . '</div>',
                        'class' => 'col-sm-1',
                    ),
                ),
                'submit' => array(
                    'title' => $this->trans(
                        'Save',
                        array(),
                        'Admin.Actions'
                    ),
                    'name' => 'submitAband',
                    'class' => 'btn btn-default pull-right',
                ),
            ),
        );
        return $fields_form_1;
    }

    //For testing
    protected function getTestForm()
    {
        $fields_form_1 = array(
            'form' => array(
                'legend' => array(
                    'title' => 'Test',
                    'icon' => 'icon-cogs',
                ),
                'submit' => array(
                    'title' => 'Test',
                    'name' => 'submitTest',
                    'class' => 'btn btn-default pull-right',
                ),
            ),
        );
        return $fields_form_1;
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'ABANDCARTS_DELAY_1' => Tools::getValue(
                'ABANDCARTS_DELAY_1',
                Configuration::get('ABANDCARTS_DELAY_1')
            ),
            'ABANDCARTS_DELAY_2' => Tools::getValue(
                'ABANDCARTS_DELAY_2',
                Configuration::get('ABANDCARTS_DELAY_2')
            ),
            'ABANDCARTS_DELAY_3' => Tools::getValue(
                'ABANDCARTS_DELAY_3',
                Configuration::get('ABANDCARTS_DELAY_3')
            ),
            'ABANDCARTS_DELAY_4' => Tools::getValue(
                'ABANDCARTS_DELAY_4',
                Configuration::get('ABANDCARTS_DELAY_4')
            ),
            'ABANDCARTS_DELAY_5' => Tools::getValue(
                'ABANDCARTS_DELAY_5',
                Configuration::get('ABANDCARTS_DELAY_5')
            ),
            'ABANDCARTS_ENABLE_1' => Tools::getValue(
                'ABANDCARTS_ENABLE_1',
                Configuration::get('ABANDCARTS_ENABLE_1')
            ),
            'ABANDCARTS_EMAIL_FROM' => Tools::getValue(
                'ABANDCARTS_EMAIL_FROM',
                Configuration::get('ABANDCARTS_EMAIL_FROM')
            ),
            'ABANDCARTS_ENABLE_DEBUG_LOGS' => Tools::getValue(
                'ABANDCARTS_ENABLE_DEBUG_LOGS',
                Configuration::get('ABANDCARTS_ENABLE_DEBUG_LOGS')
            ),
            //ABANDCARTS_ENABLE_DEBUG_LOGS
        );
    }

    /**
     * Save form data.
     */
    protected function test()
    {
        // $this->createDiscount(2, date('Y-m-d H:i:s'), self::ABANDCARTS_CART_DAYS_VALID, 2);
        // $abandCarts = new AbandCart($this);
        // $abandCarts->clearExpiredDiscounts();
        $this->processAbandCarts();
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

    /**
     * creates discount for aband. cart.
     *
     */
    private function createDiscount(
        $id_customer,
        $daysvalid,
        $amount,
        float $minamount,
        $prefix = 'N'
    ) {
        $cart_rule = new CartRule();
        $todate = date('Y-m-d H:i:s');
        $todate = strtotime($todate);
        $todate = strtotime("+" . $daysvalid . " day", $todate);
        $date_validity = date('Y-m-d H:i:s', $todate);
        // $cart_rule->reduction_percent = (float)$amount;
        $cart_rule->reduction_amount = $amount;
        $cart_rule->reduction_tax = true;
        // $cart_rule->id_customer = (int)$id_customer;
        $cart_rule->date_to = $date_validity;
        $cart_rule->date_from = date('Y-m-d H:i:s');
        $cart_rule->quantity = 1;
        $cart_rule->quantity_per_user = 1;
        $cart_rule->cart_rule_restriction = 1;
        $cart_rule->reduction_exclude_special = true;
        $cart_rule->minimum_amount = $minamount;
        $cart_rule->minimum_amount_tax = true;

        $languages = Language::getLanguages(true);
        foreach ($languages as $language) {
            $cart_rule->name[(int)$language['id_lang']] = $this->trans('Cart voucher', [], 'Modules.Abandcarts.Admin', $language['locale']);
        }

        $code = $prefix . Tools::strtoupper(
            Tools::passwdGen(5)
        );
        $cart_rule->code = $code;
        $cart_rule->active = 1;
        if (!$cart_rule->add()) {
            return false;
        }

        return $cart_rule;
    }

    public function hookActionGetAdminOrderButtons(array $params)
    {
        $order = new Order($params['id_order']);
        $cart = new Cart((int) $order->id_cart);
        $customer = new Customer($order->id_customer);

        /** @var \Symfony\Bundle\FrameworkBundle\Routing\Router $router */
        $router = $this->get('router');

        if ($customer->isGuest()) {
            /** @var \PrestaShopBundle\Controller\Admin\Sell\Order\ActionsBarButtonsCollection $bar */
            $bar = $params['actions_bar_buttons_collection'];
            $url_to_reorder = Context::getContext()->link->getPageLink('guest-tracking', true, null, 'order_reference=' . $order->reference . '&email=' . $customer->email);
            $bar->add(
                new \PrestaShopBundle\Controller\Admin\Sell\Order\ActionsBarButton(
                    'btn-info',
                    ['href' => $url_to_reorder, 'target' => '_blank'],
                    $this->trans(
                        'Reorder',
                        array(),
                        'Shop.Theme.Actions'
                    )
                )
            );
        }
    }

    /**

     * Process abandoned carts
     * @todo add logs
     */
    public function processAbandCarts()
    {
        $abandCarts = new AbandCart($this);
        $abandCustomer = new AbandCustomer($this);

        //delete expired discounts
        $abandCarts->clearExpiredDiscounts();

        // get abandoned carts which was not processed. first reminder
        $carts = $abandCarts->getCartsReminder(0);
        $this->logger->info('First reminder carts found', ['count' => count($carts), 'method' => __FUNCTION__]);
        foreach ($carts as $cart) {
            $this->createReminder($cart, 1);
        }

        // get abandoned carts for second reminder
        $carts = $abandCarts->getCartsReminder(1);
        $this->logger->info('Second reminder carts found', ['count' => count($carts), 'method' => __FUNCTION__]);
        foreach ($carts as $cart) {
            $this->createReminder($cart, 2);
        }
        $abandCustomer->ResetCustomers();
    }

    /**
     * creates reminder for aband. cart.
     *
     * @param array $cart
     * @param int $type
     */
    private function createReminder($cart, $type)
    {
        $abcart = new Cart($cart['id_cart']);
        $customer = new Customer($abcart->id_customer);
        $lang = new Language($abcart->id_lang);
        if (in_array($this::ABANDCARTS_LEGAL_GROUP, $customer->getGroups())) {
            $this->logger->info('Legal entity cart skipped', ['cart id' => $cart, 'customer id' => $abcart->id_customer, 'method' => __FUNCTION__]);
            return;
        }
        $cartamount = (float)$abcart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING);
        $discamount = 0.0;
        $minamount = 0;
        if ($cartamount >= self::ABANDCARTS_CART_DISC_FROM_1 && $cartamount < self::ABANDCARTS_CART_DISC_FROM_2) {
            $discamount = self::ABANDCARTS_CART_DISC_AMOUNT_1;
            $minamount = self::ABANDCARTS_CART_DISC_FROM_1;
        } else if ($cartamount >= self::ABANDCARTS_CART_DISC_FROM_2 && $cartamount < self::ABANDCARTS_CART_DISC_FROM_3) {
            $discamount = self::ABANDCARTS_CART_DISC_AMOUNT_2;
            $minamount = self::ABANDCARTS_CART_DISC_FROM_2;
        } else if ($cartamount >= self::ABANDCARTS_CART_DISC_FROM_3) {
            $discamount = self::ABANDCARTS_CART_DISC_AMOUNT_3;
            $minamount = self::ABANDCARTS_CART_DISC_FROM_3;
        }

        $template = 'reminder';
        if ($discamount === 0.0) {
            $template = 'reminder_nodisc';
        }
        $subject = $this->trans('Cart Reminder', [], 'Modules.Abandcarts.Email', $lang->locale);
        $cartlink = AbandCart::getCartlink($abcart->id);

        if ($discamount > 0) {
            $discfromat = Context::getContext()->getCurrentLocale()->formatPrice($discamount, Currency::getIsoCodeById((int) $abcart->id_currency), false);
            if ($type == 1) {
                $cartrule = $this->createDiscount($customer->id, self::ABANDCARTS_CART_DAYS_VALID, $discamount, $minamount);
                $daysvalid = self::ABANDCARTS_CART_DAYS_VALID;
            } else if ($type == 2) {
                $cartrule = new CartRule($cart['id_cart_rule']);
                if ($cartrule->id == null) {
                    $cartrule = $this->createDiscount($customer->id, self::ABANDCARTS_CART_DAYS_VALID, $discamount, $minamount);
                }
                $daysvalid = self::ABANDCARTS_CART_DAYS_VALID - 1;
            }
        }

        $templatevars = array(
            '{lastname}' =>  $customer->lastname,
            '{firstname}' => $customer->firstname,
            '{amount}' => $discfromat,
            '{days}' => $daysvalid,
            '{voucher_num}' => $cartrule->code,
            '{cart_link}' => $cartlink,
            '{minamount}' => Context::getContext()->getCurrentLocale()->formatPrice($minamount, Currency::getIsoCodeById((int) $abcart->id_currency), false)
        );
        if (Configuration::get('ABANDCARTS_ENABLE_1')) {
            // live mode
            $sendresult = $this->sendEmail(
                $template,
                $subject,
                $templatevars,
                $customer->email,
                $abcart->id_lang,
                null,
                'georgijs.karpovs@gmail.com'
            );
            $this->logger->info(
                'Email sent',
                [
                    'reminder type' => $type,
                    'email address' =>  $customer->email,
                    'customer Id' =>  $customer->id,
                    'cart Id' => $abcart->id,
                    'cart amount' => $cartamount,
                    'cart rule id' => $cartrule->id,
                    'discount amount' => $discfromat,
                    'send result' => $sendresult,
                    'template' => $template,
                    'method' => __FUNCTION__,
                ]
            );
        } else {
            // test mode
            $this->logger->info(
                'Test reminder',
                [
                    'reminder type' => $type,
                    'email address' =>  $customer->email,
                    'customer Id' =>  $customer->id,
                    'cart Id' => $abcart->id,
                    'cart amount' => $cartamount,
                    'cart rule id' => $cartrule->id,
                    'discount amount' => $discfromat,
                    'template' => $template,
                    'cart link' => $cartlink,
                    'method' => __FUNCTION__,
                ]
            );
            // $sendresult = $this->sendEmail(
            //     $template,
            //     $subject,
            //     $templatevars,
            //     'georgijs.karpovs@gmail.com',
            //     $abcart->id_lang
            // );
        }

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $emailRepo = $entityManager->getRepository(AbandcartEmail::class);

        if ($type == 1) {
            $emails = $emailRepo->findBy(['cartId' => $abcart->id]);
            if ($emails != null) {
                $this->logger->warning('CartId already exists in datatable', ['method' => __FUNCTION__, 'line' => __LINE__]);
            } else {
                $email = new AbandcartEmail();
                $email
                    ->setCartId($abcart->id)
                    ->setDateSent(new DateTime())
                    ->setStatus(1)
                    ->setCustomerId($abcart->id_customer)
                    ->setCartRuleId($cartrule->id);
                $entityManager->persist($email);
            }
        } else if ($type == 2) {
            $emails = $emailRepo->findBy(['cartId' => $abcart->id]);
            if ($emails != null) {
                $emails[0]->setStatus(2)
                    ->setDateSent(new DateTime());
                $entityManager->persist($emails[0]);
            } else {
                $this->logger->warning('cartId not found in datatable', ['method' => __FUNCTION__, 'line' => __LINE__]);
            }
            $abandCustomer = new AbandCustomer($this);
            $abandCustomer->AddCustomer((int)$abcart->id_customer);
        }
        $entityManager->flush();
    }

    private function sendEmail($template, $subject, $templateVars, $to, $lang, $toname = null, $bcc = null)
    {
        $result =  Mail::Send(
            $lang,
            $template,
            $subject,
            $templateVars,
            $to,
            $toname,
            Configuration::get('ABANDCARTS_EMAIL_FROM'),
            $this::ABANDCARTS_FROM_NAME,
            null,
            null,
            $this->local_path . 'mails/',
            false,
            null,
            $bcc
        );
        return $result;
    }

    /**
     * @param array $hookParams
     */
    public function hookActionListMailThemes(array $hookParams)
    {
        if (!isset($hookParams['mailThemes'])) {
            return;
        }

        /** @var ThemeCollectionInterface $themes */
        $themes = $hookParams['mailThemes'];

        /** @var ThemeInterface $theme */
        foreach ($themes as $theme) {
            if (!in_array($theme->getName(), ['modern', 'netprice'])) {
                continue;
            }

            // Add a layout to each theme (don't forget to specify the module name)
            $theme->getLayouts()->add(new Layout(
                'reminder',
                $this->local_path . '/mails/layouts/reminder.html.twig',
                '',
                $this->name
            ));
            $theme->getLayouts()->add(new Layout(
                'reminder_nodisc',
                $this->local_path . '/mails/layouts/remindernodisc.html.twig',
                '',
                $this->name
            ));
        }
    }

    public function hookActionObjectCartRuleAddBefore(array $hookParams)
    {
        /** @var CartRule $cartrule */
        $cartrule = $hookParams['object'];
        $cartrule->cart_rule_restriction = 1;
        $cartrule->update();
    }
}
