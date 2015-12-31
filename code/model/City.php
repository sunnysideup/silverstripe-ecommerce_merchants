<?php

class City extends DataObject
{

    public static $db = array(
        'Name' => 'Varchar(255)',
        'Country' => 'Varchar(2)'
    );

    public static $singular_name = 'City';
    public function i18n_singular_name()
    {
        return _t('City.SINGULARNAME', self::$singular_name);
    }

    public static $plural_name = 'Cities';
    public function i18n_plural_name()
    {
        return _t('City.PLURALNAME', self::$plural_name);
    }

    public static $default_sort = "\"Name\" ASC";

    public function Link()
    {
        $page = DataObject::get_one('AllMerchantsPage');
        if ($page) {
            return $page->CityLink($this->ID, true);
        }
    }


    /**
     * keeps the Cities for one merchant and category
     * @var Array
     */
    private static $cities_for_category_and_merchant_page_cache = array();

    /**
     * Returns ALL the Cities for one city and one MerchantPage
     * @param Int | Category $category
     * @param Int | MerchantPage $city
     * @return DataObjectSet | Null
     */
    public static function cities_for_category_and_merchant_page($category = 0, $merchantPage = 0)
    {
        $resultArray = array();
        if ($category instanceof Category) {
            $categoryID = $category->ID;
        }
        if (is_numeric($category)) {
            $categoryID = $category;
        }
        if ($merchantPage instanceof MerchantPage) {
            $merchantPageID = $merchantPage->ID;
        }
        if (is_numeric($merchantPage)) {
            $merchantPageID = $merchantPage;
        }
        $key = $categoryID."_".$merchantPageID;
        if (!isset(self::$cities_for_category_and_merchant_page_cache[$key])) {
            self::$cities_for_category_and_merchant_page_cache[$key] = null;
            if (!$categoryID && !$merchantPageID) {
                self::$cities_for_category_and_merchant_page_cache[$key] = DataObject::get("City");
            } else {
                //Q1. what merchant locations are in this city?
                $merchantPageWhere = "";
                if ($merchantPageID) {
                    $merchantPageWhere = " AND ( \"ParentID\" = ".$merchantPageID." )";
                }
                $merchantLocations = DataObject::get("MerchantLocation", MerchantLocation::get_active_filter($checkMerchant = true).$merchantPageWhere);
                if ($merchantLocations) {
                    foreach ($merchantLocations as $merchantLocation) {
                        $city = $merchantLocation->City();
                        if ($city && $city->exists()) {
                            if ($categoryID) {
                                //Q2. what categories are applicable for this merchant location?
                                $categories = $merchantLocation->Categories();
                                if ($categories) {
                                    foreach ($categories as $category) {
                                        if ($category->ID == $categoryID) {
                                            $resultArray[$city->ID] = $city->ID;
                                            break;
                                        }
                                    }
                                }
                            } else {
                                $resultArray[$city->ID] = $city->ID;
                            }
                        }
                    }
                }
                if (is_array($resultArray) && count($resultArray)) {
                    self::$cities_for_category_and_merchant_page_cache[$key] = DataObject::get("City", "\"City\".\"ID\"  IN (".implode(",", $resultArray).")");
                }
            }
        }
        return self::$cities_for_category_and_merchant_page_cache[$key];
    }
}
