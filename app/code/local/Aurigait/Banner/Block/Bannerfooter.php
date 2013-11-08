<?php 
class Aurigait_Banner_Block_Bannerfooter extends Mage_Core_Block_Template
{
	public function getBanners()
    {
    	
		$gender=$_SESSION['selected_gender'];
		if($gender!='')
		{
			if($gender=='men')
			  $gendercode=array(1,2);
			elseif($gender=='women')
			  $gendercode=array(1,3);
			else 
			  $gendercode=array(1,2,3);
			
			if($gender=='both')
			{
				$sql="select bb.gender,block_title,view_more_text,view_more_url,block_position,bannerimage,image_text,link,position
        			from banner_blocks bb
         			inner join banner b on bb.id=b.block_id and b.banner_type=1
         			where bb.gender in (".implode(',',$gendercode).")
         			order by bb.block_position,b.position,b.banner_id";
			}
			else {
				$sql="select bb.gender,block_title,view_more_text,view_more_url,block_position,bannerimage,image_text,link,position
        			from banner_blocks bb
         			inner join banner b on bb.id=b.block_id and b.banner_type=1
         			where bb.gender in (".implode(',',$gendercode).")
         			order by bb.gender desc,bb.block_position,b.position,b.banner_id";
			}
			
			
					
		}
		else {
			$sql="select bb.gender,block_title,view_more_text,view_more_url,block_position,bannerimage,image_text,link,position
        			from banner_blocks bb
         			inner join banner b on bb.id=b.block_id and b.banner_type=1
         			order by bb.block_position,b.position,b.banner_id";
		}
    	$read=Mage::getSingleton('core/resource')->getConnection('core_read');
         
		 $results=$read->fetchAll($sql);
		 return $results;
    }
        
}