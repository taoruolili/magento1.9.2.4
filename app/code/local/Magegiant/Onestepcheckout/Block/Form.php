<?php


/**
 * Customer Dynamic attributes Form Block
 *
 * @category    Magegiant
 * @package     Magegiant_Onestepcheckout
 */
class Magegiant_Onestepcheckout_Block_Form extends Magegiant_Onestepcheckout_Block_Eav_Form
{
    /**
     * Name of the block in layout update xml file
     *
     * @var string
     */
    protected $_xmlBlockName = 'customer_form_template';

    /**
     * Class path of Form Model
     *
     * @var string
     */
    protected $_formModelPath = 'customer/form';

}
