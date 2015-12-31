<?php

class MerchantPriceOption extends DataObject
{

    protected static $currency_symbol = "$";
    public static function set_currency_symbol($s)
    {
        self::$currency_symbol = $s;
    }
    public static function get_currency_symbol()
    {
        return self::$currency_symbol;
    }

    public static $db = array(
        'Price' => 'Currency',
        'ShowInFrom' => 'Boolean',
        'ShowInUpTo' => 'Boolean',
        'DefaultFrom' => 'Boolean',
        'DefaultUpTo' => 'Boolean'
    );

    public static $casting = array(
        'PriceNice' => 'HTMLText',
        'PriceInt' => 'Int'
    );

    public static $summary_fields = array(
        'Price' => 'Price',
        'ShowInFrom' => 'Boolean',
        'ShowInUpTo' => 'Boolean',
        'DefaultFrom' => 'Boolean',
        'DefaultUpTo' => 'Boolean'
    );

    public static $default_sort = "Price ASC";

    public static $singular_name = 'Price Option';
    public function i18n_singular_name()
    {
        return _t('MerchantPriceOption.SINGULARNAME', self::$singular_name);
    }

    public static $plural_name = 'Price Options';
    public function i18n_plural_name()
    {
        return _t('MerchantPriceOption.PLURALNAME', self::$plural_name);
    }

    public function getPriceNice()
    {
        return self::get_currency_symbol().round($this->Price, 2);
    }

    public function getPriceInt()
    {
        return round($this->Price);
    }

    public function Link()
    {
        $page = DataObject::get_one('AllMerchantsPage');
        if ($page) {
            $link = $page->Link()."?";
            $link .= $page->PriceFromLink($this->Price, false);
            $link .= $page->PriceUpToLink($this->Price, false);
            return $link;
        }
        return "/";
    }
}
