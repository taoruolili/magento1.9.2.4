<?php

class Magegiant_Onestepcheckout_Model_Cron
{
    /**
     * Enable debug mode for extended cron events logging
     */
    const DEBUG_MODE = false;

    /**
     * Session timeout after which abandoned cart event may trigger
     */
    const SESSION_TIMEOUT = 0; // 1 hour
    /**
     * Count of items from orders history collection that will be processed at one pass
     * Also used in check new customers function
     * Miximum value of this constant depends on RAM capacity in your server.
     */
    const ITEMS_PER_ONCE = 50;

    /**
     * ID of cache record with FUE lock
     */
    const CACHE_LOCK_ID = 'giant_fue_lock';

    /*
     * Cron run interval (in seconds)
     */
    const LOCK_EXPIRE_INTERVAL = 1800; // 30 minutes

    const MYSQL_DATETIME_FORMAT = 'Y-m-d H:i:s';
    /*
     * @var int Last execution time
     */
    protected $_lastExecTime = false;

    /*
     * @var string Last execution time string representation in MySQL datetime format
     */
    protected $_lastExecTimeMySQL = false;

    /*
     * @var int Time of job start
     */
    protected $_now = false;

    /*
     * @var string Time of job started in MySQL datetime format
     */
    protected $_nowMySQL = false;


    /*
     * Constructor
     */
    public function __construct()
    {
        clearstatcache();
    }

    /**
     * Checks if one FUE is already running
     *
     * @return
     */
    public static function checkLock()
    {
        if (($time = Mage::app()->loadCache(self::CACHE_LOCK_ID))) {
            if ((time() - $time) > self::LOCK_EXPIRE_INTERVAL) {
                // Old expired lock
            } else {
                return false;
            }
        }
        Mage::app()->saveCache(time(), self::CACHE_LOCK_ID, array(), self::LOCK_EXPIRE_INTERVAL);

        return true;
    }

    /*
     * Runs cron job
     */
    public function cronJobs()
    {
        if (!Mage::helper('onestepcheckout/config')->isEnabledAbandonedCart()) {
            return;
        }
        $this->_now          = time();
        $this->_lastExecTime = time();
        if (!self::checkLock()) {
            Mage::log('FUE is already running');
            if (!self::DEBUG_MODE) {
                return;
            }
        }
        $this->_nowMySQL          = date(self::MYSQL_DATETIME_FORMAT, $this->_now);
        $this->_lastExecTimeMySQL = date(
            self::MYSQL_DATETIME_FORMAT, $this->_lastExecTime
        );
        try {
            $this->_checkAbandonedCarts();
        } catch (Exception $e) {
            Mage::logException($e);
        }
        Mage::app()->removeCache(self::CACHE_LOCK_ID);
    }

    /*
     * Checks for new abandoned carts appeared
     */
    protected function _checkAbandonedCarts()
    {
        if (self::DEBUG_MODE) {
            Mage::log("Checking abandoned carts");
        }
        $resource    = Mage::getSingleton('core/resource');
        $read        = $resource->getConnection('core_read');
        $active_from = Mage::helper('onestepcheckout/config')->getAbandonedActiveFrom();
        $active_to   = Mage::helper('onestepcheckout/config')->getAbandonedActiveTo();
        $select      = $read->select()
            ->from(
                array('q' => $resource->getTableName('sales/quote')), array(
                    'store_id'    => 'q.store_id',
                    'quote_id'    => 'q.entity_id',
                    'customer_id' => 'q.customer_id',
                    'updated_at'  => 'q.updated_at')
            )
            ->joinLeft(
                array('a' => $resource->getTableName('sales/quote_address')),
                'q.entity_id=a.quote_id AND a.address_type="billing"',
                array(
                    'customer_email'      => new Zend_Db_Expr('IFNULL(q.customer_email, a.email)'),
                    'customer_firstname'  => new Zend_Db_Expr('IFNULL(q.customer_firstname, a.firstname)'),
                    'customer_middlename' => new Zend_Db_Expr('IFNULL(q.customer_middlename, a.middlename)'),
                    'customer_lastname'   => new Zend_Db_Expr('IFNULL(q.customer_lastname, a.lastname)'),
                )
            )
            ->joinInner(
                array('i' => $resource->getTableName('sales/quote_item')),
                'q.entity_id=i.quote_id',
                array(
                    'product_ids' => new Zend_Db_Expr('GROUP_CONCAT(i.product_id)'),
                    'item_ids'    => new Zend_Db_Expr('GROUP_CONCAT(i.item_id)')
                )
            )
            ->where('q.is_active=1')
            ->where(
                'q.updated_at < ?', date(
                    self::MYSQL_DATETIME_FORMAT,
                    $this->_now - self::SESSION_TIMEOUT
                )
            )
            ->where('q.items_count>0')
            ->where('q.customer_email IS NOT NULL OR a.email IS NOT NULL')
            ->where('i.parent_item_id IS NULL')
            ->group('q.entity_id')
            ->order('updated_at');
        if ($active_from) {
            $select = $select->where(
                'q.updated_at >= ?', date(self::MYSQL_DATETIME_FORMAT,strtotime($active_from))
            );
        }
        if ($active_to) {
            $select = $select->where(
                'q.updated_at <= ?', date(self::MYSQL_DATETIME_FORMAT,strtotime($active_to))
            );
        }
        $carts = $read->fetchAll($select);
        if (!count($carts)) {
            return;
        }
        $product = Mage::getModel('catalog/product');
        $select  = $read->select()
            ->distinct()
            ->from($resource->getTableName('sales/quote_item_option'), 'value')
            ->where('code="product_type"');

        foreach ($carts as $cart) {
            $categoryIds    = '';
            $productTypeIds = array();
            $sku            = array();
            $productIds     = explode(',', $cart['product_ids']);
            $extraInfo      = $read->fetchCol($select->where('item_id IN (' . $cart['item_ids'] . ')'));

            foreach ($extraInfo as $productTypeId) {
                $productTypeIds[$productTypeId] = true;
            }

            foreach ($productIds as $productId) {
                $product->unsetData()->load($productId);
                if (is_array($product->getCategoryIds())) {
                    $categoryIds .= ',' . implode(',', $product->getCategoryIds());
                } else {
                    $categoryIds .= ',' . $product->getCategoryIds();
                }
                $productTypeIds[$product->getTypeId()] = true;
                $sku[]                                 = $product->getSku();
            }
            $params                       = array();
            $params['store_id']           = $cart['store_id'];
            $params['customer_id']        = $cart['customer_id'];
            $params['customer_email']     = $cart['customer_email'];
            $params['customer_firstname'] = $cart['customer_firstname'];
            $params['category_ids']       = Mage::helper('onestepcheckout')->noEmptyValues(
                array_unique(explode(',', $categoryIds))
            );
            $params['product_type_ids']   = array_keys($productTypeIds);
            $params['sku']                = $sku;
            $params['product_ids']        = $productIds;
            $params['quote_id']           = $cart['quote_id'];
            $params['updated_at']         = $cart['updated_at'];
            $model                        = Mage::getModel('onestepcheckout/abandonedcart_customer')->process($params);
        }
    }


}
