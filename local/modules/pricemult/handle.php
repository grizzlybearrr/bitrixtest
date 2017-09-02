<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

IncludeModuleLangFile(__FILE__);

if(!$USER->IsAdmin()) $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$APPLICATION->SetTitle(GetMessage("PRICEMULT_PAGE_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");


spl_autoload_register(function($classname){
	$bitrixFile = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pricemult/lib/'.$classname.'.php';
	$localFile = $_SERVER['DOCUMENT_ROOT'].'/local/modules/pricemult/lib/'.$classname.'.php';

	if(file_exists( $localFile )) {
		require( $localFile );
	} elseif (file_exists( $bitrixFile )) {
		require( $bitrixFile );
	}
});

$handler = new PricemultManager();
$handler->processPostAction();
?>


<?if($handler->isReady()):?>
	<?//вывод управления в административной части?>
	<div class="adm-detail-content-wrap">

		<div class="adm-detail-content">
			<div class="adm-detail-title">
				<?=GetMessage('PRICEMULT_HANDLE_TITLE')?>
			</div>

			<div class="adm-detail-content-item-block">

				<form method="post" action="">
					<table class="adm-detail-content-table edit-table">
						<tbody>

							<tr>
								<td width="40%" class="adm-detail-valign-top">
									<?=GetMessage('PRICEMULT_PERCENT')?>
								</td>
								<td width="60%">

									<?$handler->showPercentInput()?>
									<input class="adm-btn-save"
										name="execute"
										value="<?=GetMessage('PRICEMULT_RUN_BTN')?>"
										type="submit" />
								</td>
							</tr>
				

							<tr>
								<td width="40%" class="adm-detail-valign-top">
									<?=GetMessage('PRICEMULT_SECTIONS')?>
								</td>
								<td width="60%">
									<?$handler->showSectionSelector()?>
								</td>

							</tr>

						</tbody>
					</table>
				</form>

			</div>
		</div>

	</div>


	<!-- История операций -->
	<?
		$title = GetMessage('PRICEMULT_RESULT_TITLE');
		$handler->showHistoryTable( $title );
	?>
	
	<?//end handler ready?>
<?else:?>
	<?//попытка использовать модуль не настроив инфоблок ?>
	<a href="/bitrix/admin/settings.php?lang=ru&mid=pricemult&mid_menu=1">
		<?=GetMessage('PRICEMULT_IBLOCK_NOT_CONFIG');?>
	</a>
<?endif;?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>