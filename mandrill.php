<?php
/**
 * 2017 thirty bees
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@thirtybees.com so we can send you a copy immediately.
 *
 * @author    thirty bees <modules@thirtybees.com>
 * @copyright 2017 thirty bees
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_TB_VERSION_')) {
    exit;
}

require_once __DIR__.'/vendor/autoload.php';

/**
 * Class Mandrill
 *
 * @since 1.0.0
 */
class Mandrill extends Module
{
    const API_KEY = 'MANDRILL_API_KEY';

    /**
     * Mandrill constructor.
     */
    public function __construct()
    {
        $this->name = 'mandrill';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'thirty bees';
        $this->need_instance = 0;

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Mandrill');
        $this->description = $this->l('Mandrill module for the store');
    }

    /**
     * @return string
     */
    public function getContent()
    {
        $this->postProcess();

        return $this->generateCredentialsForm();
    }

    protected function postProcess()
    {
        if (Tools::getValue('submitCredentials')) {
            Configuration::updateValue(static::API_KEY, Tools::getValue(static::API_KEY));
        }
    }

    /**
     * @return string
     *
     * @since 1.0.0
     */
    protected function generateCredentialsForm()
    {
        $fields = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Mandrill Credentials'),
                    'icon'  => 'icon-key',
                ],
                'input'  => [
                    [
                        'type'  => 'text',
                        'label' => $this->l('API key'),
                        'name'  => static::API_KEY,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = '';
        $helper->submit_action = 'submitCredentials';
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
        ];

        return $helper->generateForm([$fields]);
    }

    /**
     * @return array
     */
    protected function getConfigFieldsValues()
    {
        return [
            static::API_KEY => Configuration::get(static::API_KEY),
        ];
    }
}
