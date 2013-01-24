<?php
class Mycustom_Fileupload_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
		
   $this->loadLayout();     
   $this->renderLayout();
    }
	
	public function uploadAction()
    {  

	error_reporting(E_ALL);
	
	$this->_initLayoutMessages('core/session'); 
	$messages=Mage::getSingleton("customer/session")->getMessages();
	
	$post_data = Mage::app()->getRequest()->getPost(); 

	$uploadFiletype = $post_data['type'];
	$redirectUrl    = $post_data['admin_url'];
	
	$file_name = $_FILES["file"]["name"]; 
	$file_type = $_FILES["file"]["type"];  
	
	// File Type check
	if($file_type == 'application/vnd.ms-excel')
	{ 
		// Code for File upload 	
		if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != "") 
		{
									$uploader = new Varien_File_Uploader("file");
									$uploader->setAllowedExtensions(array('xls'));
									$uploader->setAllowRenameFiles(false);
									$uploader->setFilesDispersion(false);
									$path = Mage::getBaseDir("media") . DS . "upload" . DS;
									$uploader->save($path);
		 }
	 
	 	$excel_reader_path = Mage::getBaseDir("media") . DS . "Excel" . DS;
	 
	 	$file_sourece = $path.$_FILES['file']['name'];
	
	 	require_once $excel_reader_path.'excel_reader2.php';
	 
		 $excel_reader = new Spreadsheet_Excel_Reader();
		 $excel_reader->setUTFEncoder('iconv');
		 $excel_reader->setOutputEncoding('CP1251');
		 $file=$excel_reader->read($file_sourece,"UTF-16");
				
		 $file_row=3;
				
		 $column_count=$excel_reader->sheets[0]['numCols'];
		 $row_count=$excel_reader->sheets[0]['numRows'];		
				

		$row_check = 0 ;
				
		for($file_row;$file_row<=$excel_reader->sheets[0]['numRows'];$file_row++) 
		{   			
		 	@$data1=addslashes($excel_reader->sheets[0]['cells'][$file_row][1]); 
			@$data2=addslashes($excel_reader->sheets[0]['cells'][$file_row][2]); 
			@$data3=addslashes($excel_reader->sheets[0]['cells'][$file_row][3]); 
			@$data4=$excel_reader->sheets[0]['cells'][$file_row][4]; 
			@$data5=addslashes($excel_reader->sheets[0]['cells'][$file_row][5]);  
			@$data6=addslashes($excel_reader->sheets[0]['cells'][$file_row][6]);
			
			$pincode = $data4 ;
			
		$collection = Mage::getModel('fileupload/fileupload')->getCollection();
		$collection->addFieldToFilter('pincode',array('like'=>'%'.$pincode.'%'));
		$getDatas = $collection->getData();
		
	/*	echo "<pre>";
		print_r($getDatas);
		echo "Count ".count($getDatas);
		echo "</pre>";
		exit;*/
			
		$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
		$connection->beginTransaction();
		$sql = 'INSERT INTO fileupload (`location`, `branch_detail`, `served_by`, `pincode`, `area`, `file_type`, `state`) VALUES("'.$data1.'","'.$data2.'","'.$data3.'","'.$data4.'","'.$data5.'","'.$uploadFiletype.'", "'.$data6.'")';
    
		 $sql;
	
 	  	$checkData = $connection->query($sql);
		$connection->commit(); 
		$row_check++; 
		
		}
		$this->_redirect('fileupload/adminhtml_fileupload/index/key/'.$redirectUrl.'?fstatus=1&data_cnt='.$row_check);
	}
	else
	{
	$this->_redirect('fileupload/adminhtml_fileupload/index/key/'.$redirectUrl.'?fstatus=2');
	}
		
		} 
		
		
	public function checkAction()
	{
	
		$fileupload = Mage::getModel('fileupload/fileupload');
		$fileupload = $fileupload->setLocation('ManiTEstNEW');
		$fileupload->setBranchdetail('HELLO')
		->save();
		
		echo "Done";
		
	}
	
	
} 
?>