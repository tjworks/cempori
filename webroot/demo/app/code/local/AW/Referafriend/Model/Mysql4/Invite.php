<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Referafriend
 * @copyright  Copyright (c) 2010-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */
class AW_Referafriend_Model_Mysql4_Invite extends AW_Referafriend_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('referafriend/invite', 'invite_id');
    }

    public function getTotalInvites($signup = true, $referrerId = null,$forDiscount = false,$ruleId = null)
    {
        if($forDiscount){
        $select = $this->_getReadAdapter()->select()
            ->from(array('main_table'=>$this->getMainTable()),array(' IFNULL((COUNT(*) - used_signups.used_signups),COUNT(*)) as signups'))
            ->where('main_table.referral_status = ?',1)
            ->group('main_table.referrer_id')
            ->joinLeft( array('used_signups' => $this->getTable('referafriend/usedsignups') ), 'rule_id = '.$ruleId.' AND used_signups.referrer_id = '.$referrerId, array())
            ->where('main_table.referrer_id = ?', $referrerId)
            ;
        }
        else{
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), 'COUNT(*)');

            if ($referrerId){
                $select->where('referrer_id = ?', $referrerId);
            }

        }
        if($ruleId){
            $select
                ->joinLeft( array('rule' => $this->getTable('referafriend/rule') ), 'rule.rule_id = '.$ruleId, array())
                ->where('main_table.created_at > rule.updated')
                ;
        }
        if ($signup){
            $select->where('referral_id > 0');
        }
        return $this->_getReadAdapter()->fetchOne($select);
    }

    public function getReferralIds($referrerId = null)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), 'referral_id');
        if ($referrerId){
            $select->where('referrer_id = ?', $referrerId);
        }
        return $this->_getReadAdapter()->fetchCol($select);
    }

    public function getInviteIds()
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), $this->getIdFieldName());
        $select->where('referral_id > 0');
        return $this->_getReadAdapter()->fetchCol($select);
    }
}