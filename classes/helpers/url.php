<?php
/**
 * 2013-2016 Nosto Solutions Ltd
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
 * @copyright 2013-2016 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

use Nosto\Request\Http\HttpRequest as NostoSDKHttpRequest;

/**
 * Helper class for managing urls.
 */
class NostoHelperUrl
{
    /**
     * Returns a preview url to a product page.
     *
     * @param int|null $idLang optional language ID if a specific language is needed.
     * @return string the url.
     */
    public static function getPreviewUrlProduct($idLang = null)
    {
        try {
            if (is_null($idLang)) {
                $idLang = (int)Context::getContext()->language->id;
            }

            $row = Product::getProducts($idLang, 0, 1, "id_product", "ASC", false, true);
            $id_product = isset($row['id_product']) ? (int)$row['id_product'] : 0;

            $product = new Product($idLang, $id_product);
            if (!Validate::isLoadedObject($product)) {
                return '';
            }

            $params = array('nostodebug' => 'true');
            return self::getProductUrl($product, $idLang, null, $params);
        } catch (Exception $e) {
            NostoHelperLogger::error($e, "Unable to build the product preview URL");
            return '';
        }
    }

    /**
     * Returns a preview url to a category page.
     *
     * @param int|null $idLang optional language ID if a specific language is needed.
     * @return string the url.
     */
    public static function getPreviewUrlCategory($idLang = null)
    {
        try {
            if (is_null($idLang)) {
                $idLang = (int)Context::getContext()->language->id;
            }

            $row = Category::getHomeCategories($idLang, true)[0];
            $id_category = isset($row['id_category']) ? (int)$row['id_category'] : 0;

            $category = new Category($id_category, $idLang);
            if (!Validate::isLoadedObject($category)) {
                return '';
            }

            $params = array('nostodebug' => 'true');
            return self::getCategoryUrl($category, $idLang, null, $params);
        } catch (Exception $e) {
            NostoHelperLogger::error($e, "Unable to build the category preview URL");
            return '';
        }
    }

    /**
     * Returns a preview url to the search page.
     *
     * @param int|null $idLang optional language ID if a specific language is needed.
     * @return string the url.
     */
    public static function getPreviewUrlSearch($idLang = null)
    {
        try {
            $params = array(
                'controller' => 'search',
                'search_query' => 'nosto',
                'nostodebug' => 'true',
            );
            return self::getPageUrl('NostoSearch.php', $idLang, null, $params);
        } catch (Exception $e) {
            // Return empty on failure
            return '';
        }
    }

    /**
     * Returns a preview url to cart page.
     *
     * @param int|null $idLang optional language ID if a specific language is needed.
     * @return string the url.
     */
    public static function getPreviewUrlCart($idLang = null)
    {
        try {
            $params = array('nostodebug' => 'true');
            return self::getPageUrl('NostoOrderTagging.php', $idLang, null, $params);
        } catch (Exception $e) {
            // Return empty on failure
            return '';
        }
    }

    /**
     * Returns a preview url to the home page.
     *
     * @param int|null $idLang optional language ID if a specific language is needed.
     * @return string the url.
     */
    public static function getPreviewUrlHome($idLang = null)
    {
        try {
            $params = array('nostodebug' => 'true');
            return self::getPageUrl('index.php', $idLang, null, $params);
        } catch (Exception $e) {
            // Return empty on failure
            return '';
        }
    }

    /**
     * Builds a product page url for the language and shop.
     *
     * We created our own method due to the existing one in `Link` behaving differently across
     * PS versions.
     *
     * @param Product $product
     * @param int|null $idLang the language ID (falls back on current context if not set).
     * @param int|null $idShop the shop ID (falls back on current context if not set).
     * @param array $params additional params to add to the url.
     * @return string the product page url.
     */
    public static function getProductUrl($product, $idLang = null, $idShop = null, array $params = array())
    {
        if (is_null($idLang)) {
            $idLang = (int)Context::getContext()->language->id;
        }
        if (is_null($idShop)) {
            $idShop = (int)Context::getContext()->shop->id;
        }

        $url = NostoHelperLink::getLink()->getProductLink($product, null, null, null, $idLang, $idShop);
        if ((int)Configuration::get('PS_REWRITING_SETTINGS') === 0) {
            $params['id_lang'] = $idLang;
        }

        return NostoSDKHttpRequest::replaceQueryParamsInUrl($params, $url);
    }

    /**
     * Builds a category page url for the language and shop.
     *
     * We created our own method due to the existing one in `Link` behaving differently across
     * PS versions.
     *
     * @param Category|CategoryCore $category the category model.
     * @param int|null $idLang the language ID (falls back on current context if not set).
     * @param int|null $idShop the shop ID (falls back on current context if not set).
     * @param array $params additional params to add to the url.
     * @return string the category page url.
     */
    public static function getCategoryUrl($category, $idLang = null, $idShop = null, array $params = array())
    {
        if (is_null($idLang)) {
            $idLang = (int)Context::getContext()->language->id;
        }
        if (is_null($idShop)) {
            $idShop = (int)Context::getContext()->shop->id;
        }

        $url = NostoHelperLink::getLink()->getCategoryLink($category, null, $idLang, null, $idShop);
        if ((int)Configuration::get('PS_REWRITING_SETTINGS') === 0) {
            $params['id_lang'] = $idLang;
        }

        return NostoSDKHttpRequest::replaceQueryParamsInUrl($params, $url);
    }

    /**
     * Builds a page url for the language and shop.
     *
     * We created our own method due to the existing one in `Link` behaving differently across
     * PS versions.
     *
     * @param string $controller the controller name.
     * @param int|null $idLang the language ID (falls back on current context if not set).
     * @param int|null $idShop the shop ID (falls back on current context if not set).
     * @param array $params additional params to add to the url.
     * @return string the page url.
     */
    public static function getPageUrl($controller, $idLang = null, $idShop = null, array $params = array())
    {
        if (is_null($idLang)) {
            $idLang = (int)Context::getContext()->language->id;
        }
        if (is_null($idShop)) {
            $idShop = (int)Context::getContext()->shop->id;
        }

        $url = NostoHelperLink::getLink()->getPageLink($controller, true, $idLang, null, false, $idShop);

        if ((int)Configuration::get('PS_REWRITING_SETTINGS') === 0) {
            $params['id_lang'] = $idLang;
        }

        return NostoSDKHttpRequest::replaceQueryParamsInUrl($params, $url);
    }

    /**
     * Builds a module controller url for the language and shop.
     *
     * We created our own method due to the existing one in `Link` behaving differently across
     * PS versions.
     *
     * @param string $name the name of the module to create an url for.
     * @param string $controller the name of the controller.
     * @param int|null $idLang the language ID (falls back on current context if not set).
     * @param int|null $idShop the shop ID (falls back on current context if not set).
     * @param array $params additional params to add to the url.
     * @return string the url.
     */
    public static function getModuleUrl($name, $controller, $idLang = null, $idShop = null, array $params = array())
    {
        if (is_null($idLang)) {
            $idLang = (int)Context::getContext()->language->id;
        }
        if (is_null($idShop)) {
            $idShop = (int)Context::getContext()->shop->id;
        }

        $params['module'] = $name;
        $params['controller'] = $controller;

        if (version_compare(_PS_VERSION_, '1.5.5.0') === -1) {
            // For PS versions 1.5.0.0 - 1.5.4.1 we always hard-code the urls to be in non-friendly format and fetch
            // the shops base url ourselves. This is a workaround to all the bugs related to url building in these
            // PS versions.
            $params['fc'] = 'module';
            $params['module'] = $name;
            $params['controller'] = $controller;
            $params['id_lang'] = $idLang;
            return self::getBaseUrl($idShop) . 'index.php?' . http_build_query($params);
        } else {
            $link = NostoHelperLink::getLink();
            return $link->getModuleLink($name, $controller, $params, null, $idLang, $idShop);
        }
    }

    /**
     * Get the url for the controller
     *
     * @param string $controllerClassName controller class name prefix, without the 'Controller' part
     * @param string $employeeId current logge in employee id
     * @return string controller url
     */
    public static function getControllerUrl($controllerClassName, $employeeId)
    {
        /** @noinspection PhpDeprecationInspection */
        $tabId = (int)Tab::getIdFromClassName($controllerClassName);
        $token = Tools::getAdminToken($controllerClassName . $tabId . $employeeId);

        return 'index.php?controller=' . $controllerClassName . '&token=' . $token;
    }

    /**
     * Returns the base url for given shop.
     *
     * @param null $id_shop the shop ID (falls back on current context if not set).
     * @return string the base url.
     */
    private static function getBaseUrl($id_shop = null)
    {
        $ssl = Configuration::get('PS_SSL_ENABLED');

        if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') && !is_null($id_shop)) {
            $shop = new Shop($id_shop);
        } else {
            $shop = Context::getContext()->shop;
        }

        $base = ($ssl ? 'https://' . $shop->domain_ssl : 'http://' . $shop->domain);
        return $base . $shop->getBaseURI();
    }
}
