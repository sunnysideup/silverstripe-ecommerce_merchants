<?php

class Category extends DataObject
{

    public static $db = array(
        'Name' => 'Varchar',
        'Featured' => 'Boolean',
        'Sort' => "Int"
    );

    public static $has_one = array(
        'TinyImage' => 'Image',
        'SmallImage' => 'Image',
        'LargeImage' => 'Image'
    );

    public static $belongs_many_many = array(
        'Products' => 'MerchantProduct'
    );

    public static $indexes = array(
        'Sort' => true
    );

    public static $casting = array(
        'Code' => 'Varchar'
    );

    public static $field_labels = array(
        'Featured' => 'Is featured ?',
        'Sort' => 'Sort Number (lower numbers first)'
    );

    public static $default_sort = "\"Sort\" ASC";

    public static $singular_name = 'Category';
    public function i18n_singular_name()
    {
        return _t('Category.SINGULARNAME', self::$singular_name);
    }

    public static $plural_name = 'Categories';
    public function i18n_plural_name()
    {
        return _t('Category.PLURALNAME', self::$plural_name);
    }


    /**
     * keeps the categories for one city and one merchant
     * @var Array
     */
    private static $categories_for_city_and_merchant_page_cache = array();

    /**
     * Returns ALL the categories for one city and one merchant
     * @param Int | City $city
     * @param Int | MerchantPage $merchantPage
     * @return DataObjectSet | Null
     */
    public static function categories_for_city_and_merchant_page($city = 0, $merchantPage = 0)
    {
        $resultArray = array();
        if ($city instanceof City) {
            $cityID = $city->ID;
        }
        if (is_numeric($city)) {
            $cityID = $city;
        }
        if ($merchantPage instanceof MerchantPage) {
            $merchantPageID = $merchantPage->ID;
        }
        if (is_numeric($merchantPage)) {
            $merchantPageID = $merchantPage;
        }
        $key = $cityID."_".$merchantPageID;
        if (!isset(self::$categories_for_city_and_merchant_page_cache[$key])) {
            if (!$cityID && !$merchantPageID) {
                self::$categories_for_city_and_merchant_page_cache[$key] = DataObject::get("Category");
            } else {
                self::$categories_for_city_and_merchant_page_cache[$key] = null;
                //Q1. what merchant locations are in this city?
                $merchantPageWhere = "";
                if ($merchantPageID) {
                    $merchantPageWhere = "  AND (\"ParentID\" = ".$merchantPageID.")";
                }
                if (intval($cityID) == 0) {
                    $merchantLocations = DataObject::get("MerchantLocation", MerchantLocation::get_active_filter($checkMerchant = true)."  ".$merchantPageWhere."  ");
                } else {
                    $merchantLocations = DataObject::get("MerchantLocation", "CityID =".$cityID." AND ( ".MerchantLocation::get_active_filter($checkMerchant = true)." ) ".$merchantPageWhere." ");
                }
                if ($merchantLocations) {
                    foreach ($merchantLocations as $merchantLocation) {
                        //Q2. what categories are applicable for this merchant location?
                        $categories = $merchantLocation->Categories();
                        if ($categories) {
                            foreach ($categories as $category) {
                                $resultArray[$category->ID] = $category->ID;
                            }
                        }
                    }
                }
                if (is_array($resultArray) && count($resultArray)) {
                    self::$categories_for_city_and_merchant_page_cache[$key] = DataObject::get("Category", "\"Category\".\"ID\"  IN (".implode(",", $resultArray).")");
                }
            }
        }
        return self::$categories_for_city_and_merchant_page_cache[$key];
    }

    public function Link()
    {
        $page = DataObject::get_one('AllMerchantsPage');
        if ($page) {
            return $page->CategoryLink($this->ID, true);
        }
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldsToTab('Root.Images', array(
            new ImageField('LargeImage', _t('Category.LARGEIMAGE', 'Large icon'), null, null, null, 'Categories'),
            new ImageField('SmallImage', _t('Category.SMALLIMAGE', 'Small icon (label)'), null, null, null, 'Categories'),
            new ImageField('TinyImage', _t('Category.TINYIMAGE', 'Tiny icon (for use in forms)'), null, null, null, 'Categories')
        ));
        return $fields;
    }

    public function getFilterFormHTMLTitle()
    {
        $image = $this->SmallImage();
        if ($image && $image->exists()) {
            return '<img src="' . $image->Filename . '" alt="' . $this->Name . '" /> ' . $this->Title;
        } else {
            return $this->Title;
        }
    }

    public function getCode()
    {
        return preg_replace("/[^a-zA-Z0-9\s]/", "", $this->Name);
    }
}
