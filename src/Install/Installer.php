<?php

declare(strict_types=1);

namespace Yourintellidata\Module\Abandcarts\Install;

use Db;
use Module;
use Tab;
use Exception;
use Language;
use Configuration;
use Tools;
use PrestaShop\PrestaShop\Core\MailTemplate\ThemeCatalogInterface;
use PrestaShopBundle\Entity\Repository\TabRepository;

class Installer
{
    const FILE_NAME = 'Installer';
    const DB_ACTION_INSTALL = 'install';
    const DB_ACTION_UNINSTALL = 'uninstall';
    protected $module;
    /**
     * @var array
     */
    private $errors = [];
    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    /**
     * Module's installation entry point.
     *
     * @param Module $module
     *
     * @return bool
     */
    public function install(): bool
    {
        foreach ($this->module->conf_keys as $key => $val) {
            $updated = Configuration::updateValue($key, $val);
            // $this->logger->debug('Config updated:' . $updated, ['field' => $key, 'value' => $val]);
        }

        if (!$this->registerHooks()) {
            return false;
        }

        if (!$this->processDatabase(self::DB_ACTION_INSTALL)) {
            $parameters['legacy'] = 'htmlspecialchars';
            $this->errors[] = $this->module->getTranslator()->trans('Failed to install database tables', $parameters, 'Modules.Abandcarts.Admin');
            return false;
        }

        if (!$this->installTab()) {
            return false;
        }


        return true;
    }

    /**
     * Module's uninstallation entry point.
     *
     * @return bool
     */
    public function uninstall(): bool
    {
        foreach ($this->module->conf_keys as $key => $val) {
            $deleted = Configuration::deleteByName($key);
            // $this->logger->debug('Config deleted:' . $deleted, ['field' => $key]);
        }
        if (!$this->processDatabase(self::DB_ACTION_UNINSTALL)) {
            $parameters['legacy'] = 'htmlspecialchars';
            $this->errors[] = $this->module->getTranslator()->trans('Failed to uninstall database tables', $parameters, 'Modules.Abandcarts.Admin');
            return false;
        }
        return $this->uninstallTab();
        // return $this->uninstallTab();
        // return true;
    }

    /**
     * Register hooks for the module.
     *
     * @param Module $module
     *
     * @return bool
     */
    private function registerHooks(): bool
    {
        $hooks = [
            'displayBackOfficeHeader',
            'header',
            'actionGetAdminOrderButtons',
            'backOfficeHeader',
            'actionObjectCartRuleAddBefore',
            ThemeCatalogInterface::LIST_MAIL_THEMES_HOOK
        ];

        return (bool) $this->module->registerHook($hooks);
    }

    /**
     * Process database install/uninstall
     *
     * @param string $action Can be "install" or "uninstall"
     *
     * @return bool
     */
    private function processDatabase($action)
    {
        $pathToSql = sprintf('sql/%s/*.sql', $action);
        $sqlFiles = glob($this->module->getLocalPath() . $pathToSql);

        foreach ($sqlFiles as $sqlFile) {
            $sqlStatements = $this->getSqlStatements($sqlFile);

            if (!$this->execute($sqlStatements)) {
                if (self::DB_ACTION_INSTALL === $action) {
                    $this->errors[] = sprintf(
                        $this->module->getTranslator()->trans('Failed to uninstall database tables', ['legacy' => 'htmlspecialchars'], 'Modules.Abandcarts.Admin'),
                        $sqlFile
                    );
                }
                return false;
            }
        }

        return true;
    }

    /**
     * Execute SQL statements
     *
     * @param $sqlStatements
     *
     * @return bool
     */
    private function execute($sqlStatements)
    {
        try {
            $result = Db::getInstance()->execute($sqlStatements);
        } catch (Exception $e) {
            return false;
        }

        return (bool)$result;
    }

    /**
     * Format and get sql statements from file
     *
     * @param string $fileName
     *
     * @return string
     */
    private function getSqlStatements($fileName)
    {
        $sqlStatements = Tools::file_get_contents($fileName);
        $sqlStatements = str_replace('PREFIX_', _DB_PREFIX_, $sqlStatements);
        $sqlStatements = str_replace('ENGINE_TYPE', _MYSQL_ENGINE_, $sqlStatements);
        $sqlStatements = str_replace('DB_NAME', _DB_NAME_, $sqlStatements);
        $sqlStatements = str_replace('DOMAIN_', 'Modules' . ucfirst($this->module->name), $sqlStatements);

        return $sqlStatements;
    }

    private function installTab()
    {
        $languages = Language::getLanguages();

        $tab = new Tab();
        $tab->class_name = 'AdminAbCart';
        $tab->module = $this->module->name;
        $tab->id_parent = Tab::getIdFromClassName('AdminParentOrders') ? Tab::getIdFromClassName('AdminParentOrders') : Tab::getIdFromClassName('Orders');
        $parameters['legacy'] = 'htmlspecialchars';
        $tab->name[1] = 'Abandoned Carts';
        $tab->name[2] = 'Pamestie grozi';
        $tab->name[4] = 'Забытые корзины';
        // foreach ($languages as $lang) {
        //     $tab->name[$lang['id_lang']] = $this->module->getTranslator()->trans('Abandoned Carts', $parameters, 'Modules.Abandcarts.Admin', $lang['locale']);
        // }

        try {
            $tab->save();
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }

        return true;
    }

    /** Uninstall module tab */
    private function uninstallTab()
    {
        $tab = (int)Tab::getIdFromClassName('AdminAbCart');

        if ($tab) {
            $mainTab = new Tab($tab);

            try {
                $mainTab->delete();
            } catch (Exception $e) {
                echo $e->getMessage();

                return false;
            }
        }

        return true;
    }
}
