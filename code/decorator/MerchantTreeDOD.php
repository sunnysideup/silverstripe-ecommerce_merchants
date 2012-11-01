<?

/**
 * Class implementing the canEdit function but for the frontend instead as canEdit is for the CMS.
 */
class MerchantTreeDOD extends DataObjectDecorator {

	static $allowed_classes = array('MerchantPage', 'MerchantProduct', 'MerchantLocation');

	public function canFrontEndEdit($member = null) {

		//we do this afterwards as we want to make sure that admins can edit all pages.
		if(! in_array($this->owner->class, self::$allowed_classes)) {
			return parent::canEdit($member);
		}

		if($member instanceof Member) $memberID = $member->ID;
		else if(is_numeric($member)) $memberID = $member;
		else $memberID = Member::currentUserID();

		if($memberID && Permission::checkMember($memberID, array("ADMIN", "SITETREE_EDIT_ALL", "SHOPADMIN"))) return true;

		if($this->owner->ID) {
			if($this->owner->CanEditType == 'LoggedInUsers') {
				return $memberID;
			}
			else if($this->owner->CanEditType == 'OnlyTheseUsers') {
				$groups = DB::query("SELECT GroupID FROM SiteTree_EditorGroups WHERE SiteTreeID = {$this->owner->ID}")->column();
				if($memberID && count($groups) > 0) {
					$groups = implode(',', $groups);
					return DB::query("SELECT COUNT(*) FROM Group_Members WHERE MemberID = $memberID AND GroupID IN ($groups)")->value();
				}
			}
			// Inherit
			elseif($this->owner->ParentID) {
				$parent = $this->owner->Parent();
				if($parent->exists()) {
					return $parent->canFrontEndEdit($memberID);
				}
			}
		}
	}
}
