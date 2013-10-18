<?xml version="1.0"?>
<layout version="0.1.0">
	<default>
		<reference name="head">
    		<action method="addCss"><styleSheet>css/magestore/affiliateplusprogram.css</styleSheet></action>
    	</reference>
	</default>
	
    <affiliateplus_default>
    	<reference name="account_navigator">
			<action method="addLink" translate="label" module="affiliateplusprogram">
				<name>program</name><path>affiliateplusprogram/index/index</path><label>My Programs</label><disabled helper="affiliateplus/account/accountNotLogin" /><order>15</order>
			</action>
    	</reference>
    </affiliateplus_default>
    
    <affiliateplusprogram_index_index>
		<reference name="head">
			<action method="addJs"><script>tinybox/tinybox.js</script></action>
			<action method="addCss"><stylesheet>css/tinybox/style.css</stylesheet></action>
		</reference>
    	<update handle="affiliateplus_default" />
        <reference name="content">
            <block type="affiliateplusprogram/program" name="affiliateplusprogram" template="affiliateplusprogram/program.phtml" />
        </reference>
    </affiliateplusprogram_index_index>
    
    <affiliateplusprogram_index_detail>
    	<update handle="affiliateplus_default" />
        <reference name="content">
            <block type="affiliateplusprogram/detail" name="affiliateplusprogram_detail" template="affiliateplusprogram/detail.phtml" />
        </reference>
    </affiliateplusprogram_index_detail>
    
    <affiliateplusprogram_index_all>
		<reference name="head">
			<action method="addJs"><script>tinybox/tinybox.js</script></action>
			<action method="addCss"><stylesheet>css/tinybox/style.css</stylesheet></action>
		</reference>
    	<update handle="affiliateplus_default" />
        <reference name="content">
            <block type="affiliateplusprogram/all" name="affiliateplusprogram_all" template="affiliateplusprogram/all.phtml" />
        </reference>
    </affiliateplusprogram_index_all>
    
</layout>