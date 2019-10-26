<?php

class Magegiant_Onestepcheckout_Model_Mysql4_Attribute extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('onestepcheckout/attribute', 'attribute_id');
    }

    public function updateAttribute($entity_type_id, $attribute_code, $fields = array())
    {
        $read      = $this->_getReadAdapter();
        $write     = $this->_getWriteAdapter();
        $selectSql = $read->select()->reset()
            ->from(array('t' => $this->getMainTable()), array('*'))
            ->where('entity_type_id = ?', $entity_type_id)
            ->where('attribute_code =?', $attribute_code);
        $field     = $read->fetchAll($selectSql);
        if (empty($field) || !is_array($field)) {
            if (array_key_exists('is_used_for_onestepcheckout', $fields)
                && $fields['is_used_for_onestepcheckout'] == 'req'
            ) {
                $write->insert($this->getMainTable(), array_merge(
                    array(
                        'entity_type_id' => $entity_type_id,
                        'attribute_code' => $attribute_code,
                        'is_billing'     => 1,
                        'position'       => 1000,
                        'colspan'        => 2,
                    ),
                    $fields
                ));
            } else {
                $write->insert($this->getMainTable(), array_merge(
                    array(
                        'entity_type_id' => $entity_type_id,
                        'attribute_code' => $attribute_code,
                    ),
                    $fields
                ));
            }
        } else {
            $write->update($this->getMainTable(), $fields, array(
                'entity_type_id = ?' => $entity_type_id,
                'attribute_code= ?'  => $attribute_code,
            ));
        }
    }

    public function deleteAttribute($entity_type_id, $attribute_code)
    {
        $write = $this->_getWriteAdapter();
        $write->delete(
            $this->getMainTable(), array(
                'entity_type_id=?' => $entity_type_id,
                'attribute_code=?' => $attribute_code,
            )
        );

        return $this;
    }

    public function updatePosition($entity_type_id, $attribute_code, $colspan, $position, $is_billing = true)
    {
        if ($is_billing) {
            $update_fields = array(
                'colspan'    => $colspan,
                'position'   => $position,
                'is_billing' => 1,
            );
        } else {
            $update_fields = array(
                'colspan'    => $colspan,
                'position'   => $position,
                'is_billing' => 0,
            );
        }
        $write = $this->_getWriteAdapter();
        $write->update($this->getMainTable(),
            $update_fields
            , array(
                'entity_type_id=?' => $entity_type_id,
                'attribute_code=?' => $attribute_code,
            ));
    }

    /**
     * init fields when sety onestepcheckout module
     */
    public function initFields()
    {
        $write = $this->_getWriteAdapter();
        $query = "
        insert into `" . $this->getMainTable() . "` (`attribute_id`, `attribute_code`, `entity_type_id`, `is_used_for_onestepcheckout`, `position`, `colspan`,`is_billing`) values('5','firstname','1','req','1','1','1');
        insert into `" . $this->getMainTable() . "` (`attribute_id`, `attribute_code`, `entity_type_id`, `is_used_for_onestepcheckout`, `position`, `colspan`,`is_billing`) values('7','lastname','1','req','2','1','1');
        insert into `" . $this->getMainTable() . "` (`attribute_id`, `attribute_code`, `entity_type_id`, `is_used_for_onestepcheckout`, `position`, `colspan`,`is_billing`) values('18','gender','1','opt','','1','0');
        insert into `" . $this->getMainTable() . "` (`attribute_id`, `attribute_code`, `entity_type_id`, `is_used_for_onestepcheckout`, `position`, `colspan`,`is_billing`) values('11','dob','1','opt','','1','0');
        insert into `" . $this->getMainTable() . "` (`attribute_id`, `attribute_code`, `entity_type_id`, `is_used_for_onestepcheckout`, `position`, `colspan`,`is_billing`) values('9','email','1','req','3','2','1');
        insert into `" . $this->getMainTable() . "` (`attribute_id`, `attribute_code`, `entity_type_id`, `is_used_for_onestepcheckout`, `position`, `colspan`,`is_billing`) values('24','company','2','opt','11','2','1');
        insert into `" . $this->getMainTable() . "` (`attribute_id`, `attribute_code`, `entity_type_id`, `is_used_for_onestepcheckout`, `position`, `colspan`,`is_billing`) values('25','street','2','req','4','2','1');
        insert into `" . $this->getMainTable() . "` (`attribute_id`, `attribute_code`, `entity_type_id`, `is_used_for_onestepcheckout`, `position`, `colspan`,`is_billing`) values('26','city','2','req','6','2','1');
        insert into `" . $this->getMainTable() . "` (`attribute_id`, `attribute_code`, `entity_type_id`, `is_used_for_onestepcheckout`, `position`, `colspan`,`is_billing`) values('27','country_id','2','req','5','2','1');
        insert into `" . $this->getMainTable() . "` (`attribute_id`, `attribute_code`, `entity_type_id`, `is_used_for_onestepcheckout`, `position`, `colspan`,`is_billing`) values('29','region_id','2','req','8','1','1');
        insert into `" . $this->getMainTable() . "` (`attribute_id`, `attribute_code`, `entity_type_id`, `is_used_for_onestepcheckout`, `position`, `colspan`,`is_billing`) values('30','postcode','2','req','7','1','1');
        insert into `" . $this->getMainTable() . "` (`attribute_id`, `attribute_code`, `entity_type_id`, `is_used_for_onestepcheckout`, `position`, `colspan`,`is_billing`) values('31','telephone','2','req','9','1','1');
        insert into `" . $this->getMainTable() . "` (`attribute_id`, `attribute_code`, `entity_type_id`, `is_used_for_onestepcheckout`, `position`, `colspan`,`is_billing`) values('32','fax','2','opt','10','1','1');
        ";
        $write->query($query);
    }
}