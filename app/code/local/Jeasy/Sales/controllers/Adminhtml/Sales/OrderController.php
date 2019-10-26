<?php

require_once(Mage::getModuleDir('controllers','Mage_Adminhtml').DS.'Sales'.DS.'OrderController.php');

class Jeasy_Sales_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
{
    private $columnSortOrder = [
        'created_at',
        'increment_id',
        'sku',
        'name',
        'image',
        'size',
        'qty',
        'status',
        'grand_total',
        'customer_name',
        'customer_address_1',
        'customer_address_2',
        'city',
        'region',
        'country',
        'postcode',
        'telephone',
        'email',
        'customer_note',
        'cost',
        'shipping_amount',
    ];

    private $styles = [
        'created_at' => ['width' => 22],
        'increment_id' => ['width' => 10],
        'sku' => ['width' => 20],
        'name' => ['width' => 35],
        'image' => ['width' => 45],
        'size' => ['width' => 26],
        'qty' => ['width' => 10],
        'status' => ['width' => 10],
        'grand_total' => ['width' => 10],
        'customer_name' => ['width' => 18],
        'customer_address_1' => ['width' => 18],
        'customer_address_2' => ['width' => 18],
        'city' => ['width' => 18],
        'region' => ['width' => 18],
        'country' => ['width' => 18],
        'postcode' => ['width' => 18],
        'telephone' => ['width' => 18],
        'email' => ['width' => 25],
        'customer_note' => ['width' => 10],
        'cost' => ['width' => 10],
        'shipping_amount' => ['width' => 10],
    ];

    private $headers = [
        'created_at' => '订单时间',
        'increment_id' => '订单号',
        'status' => '订单状态',
        'grand_total' => '订单金额',
        'customer_name' => '收件人姓名',
        'customer_address_1' => '收件人地址1',
        'customer_address_2' => '收件人地址2',
        'city' => '收件人城市',
        'region' => '收件人州',
        'country' => '收件人国家',
        'postcode' => '收件人邮编',
        'telephone' => '收件人电话',
        'email' => '客户邮箱',
        'customer_note' => '客户留言',
        'cost' => '成本',
        'shipping_amount' => '运费',
    ];

    private $itemHeaders = [
        'sku' => '产品型号',
        'name' => '产品名称',
        'image' => '产品图片',
        'size' => '产品属性（尺码）',
        'qty' => '产品数量',
    ];

    private $current_row_num = 1;

    private $_product_image = [];
    private $_countries;

    /**
     * Export order grid to CSV format
     */
    public function exportXlsxAction()
    {
        /** @var Mage_Adminhtml_Block_Sales_Order_Grid $grid */
        $grid = $this->getLayout()->createBlock('adminhtml/sales_order_grid');

        $grid->getCsv();

        $collection = $grid->getCollection();

        require_once Mage::getBaseDir('lib') . '/PHPExcel/Classes/PHPExcel.php';

        $objPHPExcel = new PHPExcel();

        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
            ->setLastModifiedBy("Jeasy")
            ->setTitle("Export orders")
            ->setSubject("Export orders")
            ->setDescription("Export orders")
            ->setKeywords("Export orders")
            ->setCategory("Export orders");

        $objActSheet = $objPHPExcel->setActiveSheetIndex(0);

        $headers = array_merge($this->headers, $this->itemHeaders);
        $this->writeLine($objActSheet, $headers);

        /** @var Mage_Sales_Model_Order $order */
        foreach ($collection as $order) {
            $rows = $this->convert2array($order);
            foreach ($rows as $row) {
                $this->writeLine($objActSheet, $row);
            }
        }

        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle('Simple');
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"orders.xlsx\"");
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit();
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    private function convert2array($order)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load($order->getId());
        $rows = [];
        $data = [];
        foreach (array_keys($this->headers) as $header) {
            $shippingAddress = $order->getShippingAddress();
            switch ($header) {
                case 'created_at':
                    $data[$header] = $order->getCreatedAtFormated('medium');
                    break;
                case 'increment_id':
                    $data[$header] = $order->getIncrementId();
                    break;
                case 'status':
                    $data[$header] = $order->getStatus();
                    break;
                case 'grand_total':
                    $data[$header] = $order->getOrderCurrencyCode() . ' ' . $this->formatPrice($order->getGrandTotal());
                    break;
                case 'customer_name':
                    $data[$header] = $shippingAddress->getName();
                    break;
                case 'customer_address_1':
                    $data[$header] = (string)$shippingAddress->getStreet1();
                    break;
                case 'customer_address_2':
                    $data[$header] = (string)$shippingAddress->getStreet2();
                    break;
                case 'city':
                    $data[$header] = (string)$shippingAddress->getCity();
                    break;
                case 'region':
                    $data[$header] = (string)$shippingAddress->getRegion();
                    break;
                case 'country':
                    $data[$header] = (string)$this->getCountryById($shippingAddress->getCountryId());
                    break;
                case 'postcode':
                    $data[$header] = (string)$shippingAddress->getPostcode();
                    break;
                case 'telephone':
                    $data[$header] = (string)$shippingAddress->getTelephone();
                    break;
                case 'email':
                    $data[$header] = $order->getCustomerEmail();
                    break;
                case 'customer_note':
                    $data[$header] = (string)$order->getCustomerNote();
                    break;
                case 'cost':
                case 'shipping_amount':
                    $data[$header] = "";
                    break;
                default:
                    break;
            }
        }

        $counter = 1;
        /** @var Mage_Sales_Model_Order_Item $item */
        foreach ($order->getAllVisibleItems() as $item) {
            $_data = [];
            foreach (array_keys($this->itemHeaders) as $header) {
                switch ($header) {
                    case 'sku':
                        $_data[$header] = $item->getSku();
                        break;
                    case 'name':
                        $_data[$header] = $item->getName();
                        break;
                    case 'image':
                        $_data[$header] = $this->getProductImage($item->getProductId());
                        break;
                    case 'size':
                        $size = "";
                        $options = $item->getProductOptions();
                        if (isset($options['options'])) {
                            $options = $options['options'];
                            $useSecOption = false;
                            foreach ($options as $option) {
                                if ($this->isSizeOption($option) || $useSecOption) {
                                    $size = isset($option['value']) ? $option['value'] : "";
                                } else {
                                    $useSecOption = true;
                                }
                            }
                        }
                        $_data[$header] = $size;
                        break;
                    case 'qty':
                        $_data[$header] = $item->getQtyOrdered();
                        break;
                    default:
                        break;
                }
            }

            if ($counter > 1) {
                foreach ($data as $key => $value) {
                    if (in_array($key, ["status", "grand_total"])) {
                        continue;
                    }
                    $data[$key] = "";
                }
            }
            $row = array_merge($data, $_data);

            $rows[] = $row;
            $counter++;
        }
        return $rows;
    }

    private function isSizeOption($option)
    {
        $label = isset($option['label']) ? $option['label'] : "";
        $label = strtolower($label);
        return in_array($label, [
            'size',
            'choose size',
            'størrelse',
            'storlek',
            'kies maat',
            'サイズ',
            'taglia',
            'valitse kokosi',
            'rozmiar',
            'taille',
        ]);
    }

    /**
     * @param $sheet PHPExcel_Worksheet
     * @param $row
     */
    private function writeLine($sheet, $row)
    {
        $row = $this->_sortOrder($row);
        $x = 'A';
        $y = $this->current_row_num;
        foreach ($row as $key => $value) {
            $xy = $x . $y;
            if ($this->current_row_num == 1) {
                $sheet->getStyle($x)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $sheet->getColumnDimension($x)->setWidth($this->styles[$key]['width']);
            }

            if ($key == 'image' && $value && file_exists($value)) {
                $objDrawing = new \PHPExcel_Worksheet_Drawing();
                $objDrawing->setPath($value);
                $objDrawing->setHeight(300);
                $objDrawing->setWidth(300);
                $objDrawing->setCoordinates($xy);
                $objDrawing->setOffsetX(10);
                $objDrawing->setOffsetY(5);
                $objDrawing->setWorksheet($sheet);
            } else {
                $sheet->setCellValue($xy, $value);
            }
            $x++;
        }

        if ($this->current_row_num >= 2) {
            $sheet->getRowDimension($this->current_row_num)->setRowHeight(180);
        }

        $this->current_row_num++;
    }

    private function _sortOrder($row)
    {
        $_row = [];
        foreach ($this->columnSortOrder as $key) {
            if (isset($row[$key])) {
                $_row[$key] = $row[$key];
            } else {
                throw new \Exception("Invalid header key $key in columnSortOrder, row array = ". var_export($row, true));
            }
        }
        return $_row;
    }

    private function getProductImage($id)
    {
        if (!isset($this->_product_image[$id])) {
            /** @var Mage_Catalog_Model_Product $product */
            $product = Mage::getModel('catalog/product');
            $product->load($id);
            if ($product->getId()) {
                $images = $product->getMediaGalleryImages();
                foreach ($images as $key => $image) {
                    $this->_product_image[$id] = $image->getPath();
                    break;
                }
            } else {
                $this->_product_image[$id] = "";
            }
        }

        return $this->_product_image[$id];
    }

    private function getCountryById($id)
    {
        if ($this->_countries === null) {
            /** @var Mage_Directory_Model_Country $directory */
            $directory = Mage::getModel('directory/country');
            /** @var Mage_Directory_Model_Resource_Country_Collection $collection */
            $collection = $directory->getCollection();
            $countries = $collection->toOptionArray();
            $this->_countries = [];
            foreach ($countries as $item) {
                if ($item['value']) {
                    $this->_countries[$item['value']] = $item['label'];
                }
            }
        }
        return isset($this->_countries[$id]) ? $this->_countries[$id] : $id;
    }
    private function formatPrice($price)
    {
        return sprintf("%.2f", round($price, 2));
    }
}