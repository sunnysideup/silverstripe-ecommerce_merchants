<?php

class MerchantProduct extends Product
{

    /****************************************
     * Model Setup
     ****************************************/

    public static $many_many = array(
        'Categories' => 'Category'
    );

    protected static $active_filter = 'ShowInSearch = 1 AND AllowPurchase = 1';
    public static function get_active_filter($cityID = 0, $categoryID = 0)
    {
        $filter = self::$active_filter;
        $merchantID = AllMerchantsPage_Controller::get_only_show_filter();
        ;
        if ($merchantID) {
            $filter .= " AND ParentID = $merchantID";
        }
        return $filter;
    }

    protected static $minimum_sort = 100000;

    public static $default_parent = 'MerchantPage';

    public static $allowed_children = 'none';

    public static $hide_ancestor = 'Product';

    public static $can_be_root = false;

    public static $singular_name = 'Product';
    public function i18n_singular_name()
    {
        return _t('MerchantProduct.SINGULARNAME', self::$singular_name);
    }

    public static $plural_name = 'Products';
    public function i18n_plural_name()
    {
        return _t('MerchantProduct.PLURALNAME', self::$plural_name);
    }

    public function canEdit($member = null)
    {
        return $this->canFrontEndEdit($member);
    }

    /****************************************
     * CRUD Forms
     ****************************************/

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('AlsoShowHere');
        $fields->replaceField('Content', new TextareaField('Content', _t('MerchantProduct.CONTENT', 'Content')));
        $categories = DataObject::get('Category');
        if ($categories) {
            $categories = $categories->map('ID', 'Name');
            $fields->addFieldToTab('Root.Content.Main', new CheckboxSetField('Categories', _t('MerchantProduct.CATEGORIES', 'Categories'), $categories));
        }
        if ($this->ID) {
            $parent = $this->Parent();
            $locations = $parent->Locations();
            if ($locations) {
                $fields->addFieldToTab('Root.Content.Main', new CheckboxSetField('ProductGroups', _t('MerchantProduct.PRODUCTGROUPS', 'Locations'), $locations->map("ID", "Title")));
            }
        }
        return $fields;
    }


    public function getFrontEndFields(MerchantPage $parent = null)
    {
        if (! $parent) {
            $parent = DataObject::get_by_id("MerchantPage", $this->ParentID);
        }
        $categories = DataObject::get('Category');
        $categories = $categories->map('ID', 'Name');
        $locations = $parent->Locations();
        if ($locations) {
            $locations = $locations->map('ID', 'Title');
        } else {
            $locations = array();
        }
        $allowPurchaseField = new CheckboxField('AllowPurchase', "<a href=\"".$this->Link()."\" taget=\"_blank\">"._t('MerchantProduct.ALLOW_PURCHASE', 'For sale')."</a>");
        $allowPurchaseField->escape = false;
        $fields = new FieldSet(
            new TextField('Title', _t('MerchantProduct.TITLE', 'Product name')),
            $allowPurchaseField,
            new TextareaField('Content', _t('MerchantProduct.CONTENT', 'Description')),
            new NumericField('Price', _t('MerchantProduct.PRICE', 'Price')),
            new TextField('InternalItemID', _t('MerchantProduct.CODE', 'Product Code')),
            new CheckboxSetField('Categories', _t('MerchantProduct.CATEGORIES', 'Categories'), $categories),
            new CheckboxSetField('ProductGroups', _t('MerchantProduct.PRODUCTGROUPS', 'Locations'), $locations),
            $imageField = new SimpleImageField('Image', _t('MerchantProduct.IMAGE', 'Image'), null, null, null, 'Products')
        );
        /*$validator = $imageField->getValidator();
        $validator->setAllowedExtensions(MerchantPage::get_image_extensions());
        $imageField->setValidator($validator);*/
        $requiredFields = new RequiredFields('Title', 'Content', 'Price', 'Categories');
        return array($fields, $requiredFields);
    }

    /****************************************
     * Controller related stuff
     ****************************************/

    public function Locations()
    {
        return $this->getManyManyComponents('ProductGroups', MerchantLocation::get_active_filter(false));
    }

    public function EditLink()
    {
        return $this->Link('edit');
    }

    public function canPurchase($member = null)
    {
        if (parent::canPurchase()) {
            $productGroups = $this->ProductGroups();
            if ($productGroups && $productGroups->count()) {
                return true;
            }
        }
        return false;
    }

    /**
     * includes CanPurchase + Is listed in Product Groups
     *
     */
    public function ForSale()
    {
        return $this->canPurchase(null);
    }

    /****************************************
     * reading and writing
     ****************************************/

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $this->MetaTitle = $this->Title;
        $this->MetaDescription = strip_tags($this->Content);
        if ($this->Sort < self::$minimum_sort) {
            $this->Sort = $this->Sort + self::$minimum_sort;
        }
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();
        $parent = DataObject::get_by_id("MerchantPage", $this->ParentID);
        $filter = '';
        if ($parent) {
            $locations = DataObject::get('MerchantLocation', "ParentID = $this->ParentID");
            ;
            if ($locations) {
                $locations = implode(',', $locations->map('ID', 'ID'));
                $filter = " AND \"ProductGroupID\" NOT IN ($locations)";
            }
        }
        DB::query("DELETE FROM \"Product_ProductGroups\" WHERE \"ProductID\" = $this->ID $filter");
    }
}

class MerchantProduct_Controller extends Product_Controller
{

    /****************************************
     * Actions
     ****************************************/

    public function edit()
    {
        if (! $this->canFrontEndEdit()) {
            return Director::redirect($this->Link());
        }
        return array();
    }


    /****************************************
     * Forms
     ****************************************/

    public function EditForm()
    {
        list($fields, $requiredFields) = $this->getFrontEndFields();
        $actions = new FieldSet(
            new FormAction('saveeditform', _t('MerchantAdminAccountPage_Controller.SAVE_DETAILS', 'Save Details')),
            new FormAction('removeProduct', _t('ModelAdmin.DELETE', 'Delete'))
        );
        $form = new Form($this, 'EditForm', $fields, $actions, $requiredFields);
        $form->loadDataFrom($this);
        return $form;
    }

    public function saveeditform($data, $form)
    {
        if ($this->canFrontEndEdit()) {
            try {
                $form->saveInto($this->dataRecord); // Call on dataRecord to fix SimpleImageField issue
                $this->dataRecord->MenuTitle = $this->dataRecord->Title; // Copy of the title on the menu title
                $this->dataRecord->URLSegment = null; // To reset the value of the URLSegment in the onBeforeWrite of SiteTree
                $this->dataRecord->writeToStage('Stage');
                $this->dataRecord->doPublish();
                $form->sessionMessage(_t('MerchantProduct_Controller.SAVE_PRODUCT_DETAILS_SUCCESS', 'Your product details have been saved successfully.'), 'good');
            } catch (ValidationException $e) {
                $form->sessionMessage(_t('MerchantProduct_Controller.SAVE_PRODUCT_DETAILS_ERROR', 'Your product details could not be saved.'), 'bad');
            }
        }
        return Director::redirect($this->EditLink()); // Not redirectBack because the URLSegment might have changed
    }

    public function removeproduct($data, $form)
    {
        if ($this->canFrontEndEdit()) {
            $this->dataRecord->AllowPurchase = false;
            $this->dataRecord->writeToStage('Stage');
            $this->dataRecord->doPublish();
        }
        return Director::redirect($this->Parent()->Link());
    }

    public function saveallproducts()
    {
        if (Permission::checkMember(Member::CurrentUserID(), array("ADMIN", "SITETREE_EDIT_ALL", "SHOPADMIN"))) {
            $merchantProducts = DataObject::get("MerchantProduct");
            if ($merchantProducts) {
                foreach ($merchantProducts as $merchantProduct) {
                    if ($merchantProduct->IsPublished()) {
                        $merchantProduct->writeToStage('Stage');
                        $merchantProduct->doPublish();
                        DB::alteration_message("publishing ".$merchantProduct->Title." - ".$merchantProduct->Title." - ".$merchantProduct->FullSiteTreeSort);
                    }
                }
            }
        } else {
            Security::permissionFailure($this, "Please login first");
        }
    }
}
