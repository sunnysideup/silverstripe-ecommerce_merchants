<?php

class MerchantAdminDOD extends DataObjectDecorator
{
    public function extraStatics()
    {
        return array('db' => array(
            'Merchant' => 'Varchar(255)', //only used for sign up
            'Website' => 'Varchar(255)' //only used for sign up
        ));
    }

    public function isMerchantAdmin()
    {
        return $this->isNewMerchantAdmin() || $this->isApprovedMerchantAdmin();
    }

    public function isNewMerchantAdmin()
    {
        $group = MerchantGroupDOD::get_main_group();
        return ! $this->isApprovedMerchantAdmin() && $this->owner->inGroup($group);
    }

    public function isApprovedMerchantAdmin()
    {
        $groups = MerchantGroupDOD::get_approved_groups();
        return $this->owner->inGroups($groups);
    }

    public function AdminHomePage()
    {
        if ($this->owner->IsAdmin() || $this->owner->IsShopAdmin()) {
            //do nothing
        } else {
            if ($this->isApprovedMerchantAdmin()) {
                $pages = DataObject::get('MerchantPage');
                if ($pages) {
                    foreach ($pages as $page) {
                        if ($page->canFrontEndEdit($this->owner)) {
                            return $page;
                        }
                    }
                }
            }
        }
    }

    public static function get_registration_fields()
    {
        return self::get_edit_fields(
            array(
                'Merchant' => _t('MerchantAdminDOD.MERCHANT', 'Merchant'),
                'Website' => _t('MerchantAdminDOD.WEBSITE', 'Website')
            )
        );
    }

    public static function get_edit_fields($extraFields = null)
    {
        $fields = new FieldSet(
            new TextField('FirstName', _t('Member.FIRSTNAME', 'First Name')),
            new TextField('Surname', _t('Member.SURNAME', 'Surname')),
            new EmailField('Email', _t('Member.EMAIL', 'Email')),
            new ConfirmedPasswordField('Password', _t('Member.db_Password', 'Password') . ' *')
        );
        $requiredFields = new RequiredFields('FirstName', 'Surname', 'Email', 'Password');
        if ($extraFields) {
            foreach ($extraFields as $name => $title) {
                $fields->push(new TextField($name, $title));
                $requiredFields->addRequiredField($name);
            }
        }
        return array($fields, $requiredFields);
    }
}
