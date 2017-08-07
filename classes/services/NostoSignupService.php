<?php

/**
 * 2013-2017 Nosto Solutions Ltd
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@nosto.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Nosto Solutions Ltd <contact@nosto.com>
 * @copyright 2013-2017 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

use Nosto\Operation\AccountSignup as NostoSDKAccountSignupOperation;

class NostoSignupService
{
    /**
     * Creates a new Nosto account for given shop language.
     *
     * @param int $id_lang the language ID for which to create the account.
     * @param string $email the account owner email address.
     * @param stdClass|string $account_details the details for the account.
     * @return bool true if account was created, false otherwise.
     */
    public function createAccount($id_lang, $email, $account_details = "")
    {
        $signupParams = NostoAccountSignup::loadData(Context::getContext(), $id_lang);
        if ($signupParams->getOwner()->getEmail() !== $email) {
            $accountOwner = new NostoAccountOwner();
            $accountOwner->setEmail($email);
            $signupParams->setOwner($accountOwner);
        }
        $signupParams->setDetails($account_details);

        $operation = new NostoSDKAccountSignupOperation($signupParams);
        $account = $operation->create();
        $id_shop = null;
        $id_shop_group = null;
        if (Context::getContext()->shop instanceof Shop) {
            $id_shop = Context::getContext()->shop->id;
            $id_shop_group = Context::getContext()->shop->id_shop_group;
        }

        return NostoHelperAccount::save($account, $id_lang, $id_shop_group, $id_shop);
    }
}
