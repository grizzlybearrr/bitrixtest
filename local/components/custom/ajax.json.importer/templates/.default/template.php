<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();


//$APPLICATION->AddHeadScript($templateFolder."/bootstrap/js/bootstrap.min.js");
$APPLICATION->SetAdditionalCss($templateFolder."/bootstrap/css/bootstrap.min.css");

?>


<div class="js-uploader">
	<form class="js-uploader__form" name="upload" enctype='multipart/form-data' action="" method="post" id="file_form"> 
	  <div class="input-group">
	    <input class="form-control js-uploader__file" type=file name="datafile" size=27> 

	    <div class="input-group-btn">
			<button type="submit" class="btn btn-primary">Отправить</button> 
	    </div>
	  </div>
	</form>


	<div class="wrapper js-uploader__info"></div>

</div>
<!-- end component -->