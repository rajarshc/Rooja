<?php
class Magestore_Affiliateplusstatistic_Block_Report_Impressions_Grid extends Magestore_Affiliateplusstatistic_Block_Report_Grid_Abstract
{
	protected $_columnGroupBy = 'visit_at';
	protected $_period_column = 'visit_at';
	
	public function __construct(){
        if ($this->_getGroupByColumn() == '2') {
            $this->_columnGroupBy = 'account_email';
        } elseif ($this->_getGroupByColumn() == '3') {
            $this->_columnGroupBy = 'banner_id';
        }
		parent::__construct();
		$this->_resourceCollectionName = 'affiliateplusstatistic/report_impressions_collection';
		$this->setCountTotals(true);
	}
    
    protected function _getGroupByColumn() {
        if (!$this->hasData('group_by_column')) {
            $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('filter'));
            if (isset($requestData['filter_group_by'])) {
                $this->setData('group_by_column', $requestData['filter_group_by']);
            } else {
                $this->setData('group_by_column', '1');
            }
        }
        return $this->getData('group_by_column');
    }
	
	protected function _prepareColumns(){
        if ($this->_getGroupByColumn() == '2') {
            $this->addColumn('account_email',array(
                'header'	=> Mage::helper('affiliateplusstatistic')->__('Account Email'),
                'index'		=> 'account_email',
                'type'		=> 'string',
                'sortable'	=> false,
                'totals_label'	=> Mage::helper('adminhtml')->__('Total'),
                'html_decorators'	=> array('nobr'),
            ));
            
            $this->addColumn('visit_at',array(
                'header'	=> Mage::helper('affiliateplusstatistic')->__('Period'),
                'index'		=> 'visit_at',
                'width'		=> 100,
                'sortable'	=> false,
                'period_type'	=> $this->getPeriodType(),
                'renderer'	=> 'adminhtml/report_sales_grid_column_renderer_date',
            ));
            
            $this->addColumn('banner_id', array(
                'header'	=> Mage::helper('affiliateplusstatistic')->__('Banner'),
                'index'		=> 'banner_id',
                'renderer'	=> 'affiliateplusstatistic/report_renderer_banner',
                'totals_label'	=> '',
                'sortable'	=> false
            ));
        } elseif ($this->_getGroupByColumn() == '3') {
            $this->addColumn('banner_id', array(
                'header'	=> Mage::helper('affiliateplusstatistic')->__('Banner'),
                'index'		=> 'banner_id',
                'renderer'	=> 'affiliateplusstatistic/report_renderer_banner',
                'sortable'	=> false,
                'totals_label'	=> Mage::helper('adminhtml')->__('Total'),
                'html_decorators'	=> array('nobr'),
            ));
            
            $this->addColumn('visit_at',array(
                'header'	=> Mage::helper('affiliateplusstatistic')->__('Period'),
                'index'		=> 'visit_at',
                'width'		=> 100,
                'sortable'	=> false,
                'period_type'	=> $this->getPeriodType(),
                'renderer'	=> 'adminhtml/report_sales_grid_column_renderer_date',
            ));

            $this->addColumn('account_email',array(
                'header'	=> Mage::helper('affiliateplusstatistic')->__('Account Email'),
                'index'		=> 'account_email',
                'type'		=> 'string',
                'sortable'	=> false
            ));
        } else {
            $this->addColumn('visit_at',array(
                'header'	=> Mage::helper('affiliateplusstatistic')->__('Period'),
                'index'		=> 'visit_at',
                'width'		=> 100,
                'sortable'	=> false,
                'period_type'	=> $this->getPeriodType(),
                'renderer'	=> 'adminhtml/report_sales_grid_column_renderer_date',
                'totals_label'	=> Mage::helper('adminhtml')->__('Total'),
                'html_decorators'	=> array('nobr'),
            ));

            $this->addColumn('account_email',array(
                'header'	=> Mage::helper('affiliateplusstatistic')->__('Account Email'),
                'index'		=> 'account_email',
                'type'		=> 'string',
                'sortable'	=> false
            ));
            
            $this->addColumn('banner_id', array(
                'header'	=> Mage::helper('affiliateplusstatistic')->__('Banner'),
                'index'		=> 'banner_id',
                'renderer'	=> 'affiliateplusstatistic/report_renderer_banner',
                'totals_label'	=> '',
                'sortable'	=> false
            ));
        }
		
		$this->addColumn('referer',array(
			'header'	=> Mage::helper('affiliateplusstatistic')->__('Traffic Source'),
			'index'		=> 'referer',
			'renderer'	=> 'affiliateplusstatistic/report_renderer_trafficsource',
			'totals_label'	=> '',
			'sortable'	=> false
		));
		
		// $this->addColumn('url_path',array(
			// 'header'	=> Mage::helper('affiliateplusstatistic')->__('Landing Page'),
			// 'index'		=> 'url_path',
			// 'renderer'	=> 'affiliateplusstatistic/report_renderer_landingpage',
			// 'totals_label'	=> '',
			// 'sortable'	=> false
		// ));
		
		$this->addColumn('totals',array(
			'header'	=> Mage::helper('affiliateplusstatistic')->__('Impressions'),
			'index'		=> 'totals',
			'type'		=> 'number',
			'total'		=> 'sum',
			'sortable'	=> false
		));
		
		$this->addColumn('is_unique',array(
			'header'	=> Mage::helper('affiliateplusstatistic')->__('Unique Impressions'),
			// 'index_prefix'	=> 'DISTINCT ',
			'index'		=> 'is_unique',
			'type'		=> 'number',
			'total'		=> 'sum',
			// 'renderer'	=> 'affiliateplusstatistic/report_renderer_uniques',
			'sortable'	=> false
		));
        
        Mage::dispatchEvent('affiliateplusstatistic_add_column_impression_report_admin', array(
            'grid' => $this,
        ));
		
		$this->addExportType('*/*/exportImpressionsCsv', Mage::helper('adminhtml')->__('CSV'));
		$this->addExportType('*/*/exportImpressionsExcel', Mage::helper('adminhtml')->__('Excel XML'));
		
		return parent::_prepareColumns();
	}
    
    public function getCsvFile()
    {
        $this->_isExport = true;
        $this->_prepareGrid();
        $this->_columns['referer']->setData('renderer', 'affiliateplusstatistic/report_renderer_referer');
        // $this->_columns['url_path']->setData('renderer', 'affiliateplusstatistic/report_renderer_path');

        $io = new Varien_Io_File();

        $path = Mage::getBaseDir('var') . DS . 'export' . DS;
        $name = md5(microtime());
        $file = $path . DS . $name . '.csv';

        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $path));
        $io->streamOpen($file, 'w+');
        $io->streamLock(true);
        $io->streamWriteCsv($this->_getExportHeaders());

        $this->_exportIterateCollection('_exportCsvItem', array($io));

        if ($this->getCountTotals()) {
            $io->streamWriteCsv($this->_getExportTotals());
        }

        $io->streamUnlock();
        $io->streamClose();

        return array(
            'type'  => 'filename',
            'value' => $file,
            'rm'    => true // can delete file after use
        );
    }
    
    public function getCommissionTotal()
    {
        return 10;
    }


    public function getExcelFile($sheetName = '')
    {
        $this->_isExport = true;
        $this->_prepareGrid();
        $this->_columns['referer']->setData('renderer', 'affiliateplusstatistic/report_renderer_referer');
        // $this->_columns['url_path']->setData('renderer', 'affiliateplusstatistic/report_renderer_path');

        $parser = new Varien_Convert_Parser_Xml_Excel();
        $io     = new Varien_Io_File();

        $path = Mage::getBaseDir('var') . DS . 'export' . DS;
        $name = md5(microtime());
        $file = $path . DS . $name . '.xml';

        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $path));
        $io->streamOpen($file, 'w+');
        $io->streamLock(true);
        $io->streamWrite($parser->getHeaderXml($sheetName));
        $io->streamWrite($parser->getRowXml($this->_getExportHeaders()));

        $this->_exportIterateCollection('_exportExcelItem', array($io, $parser));

        if ($this->getCountTotals()) {
            $io->streamWrite($parser->getRowXml($this->_getExportTotals()));
        }

        $io->streamWrite($parser->getFooterXml());
        $io->streamUnlock();
        $io->streamClose();

        return array(
            'type'  => 'filename',
            'value' => $file,
            'rm'    => true // can delete file after use
        );
    }
}